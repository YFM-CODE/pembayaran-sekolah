<?php

namespace App\Http\Controllers;

use App\Filters\GlobalFilter;
use App\Http\Requests\VerifikasiSetoranKasirRequest;
use App\Interfaces\SetoranKasirInterface;
use App\Interfaces\TransaksiSppInterface;
use App\Models\SetoranKasir;
use App\Models\TransaksiSpp;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class SetoranKasirController extends Controller
{
    public function __construct(
        protected SetoranKasirInterface $setoranRepo,
        protected TransaksiSppInterface $transaksiRepo
    ) {}

    public function index(GlobalFilter $filter, Request $request): Response
    {
        $petugasId = $request->user()->id;

        // Ambil semua transaksi milik kasir ini yang belum pernah disetor
        $transaksiGantung = TransaksiSpp::where('petugas_id', $petugasId)
            ->whereNull('setoran_kasir_id')
            ->get();

        $totalTunai = 0;
        $totalTransfer = 0;

        foreach ($transaksiGantung as $trx) {
            $metode = is_object($trx->metode_pembayaran) && property_exists($trx->metode_pembayaran, 'value')
                ? $trx->metode_pembayaran->value
                : (string) $trx->metode_pembayaran;

            if ($metode === 'tunai') {
                $totalTunai += (float) $trx->jumlah_dibayar;
            } elseif ($metode === 'transfer') {
                $totalTransfer += (float) $trx->jumlah_dibayar;
            }
        }

        $ringkasanKasir = [
            'total_tunai' => $totalTunai,
            'total_transfer' => $totalTransfer,
            'total_semua' => (float) $transaksiGantung->sum('jumlah_dibayar'),
            'jumlah_transaksi' => $transaksiGantung->count(),
        ];

        $userRole = is_object($request->user()->role) && property_exists($request->user()->role, 'value')
            ? $request->user()->role->value
            : (string) $request->user()->role;

        return Inertia::render('SetoranKasir/Index', [
            'setoran' => SetoranKasir::with(['stafTu', 'bendahara'])
                ->when($userRole === 'staf_tu', function ($q) use ($petugasId) {
                    $q->where('staf_tu_id', $petugasId);
                })
                ->orderByDesc('id')
                ->paginate(10)
                ->withQueryString(),
            'ringkasanKasir' => $ringkasanKasir,
            'filters' => request()->all(['search']),
        ]);
    }

    public function tutupKasir(Request $request): RedirectResponse
    {
        $petugasId = $request->user()->id;

        // Ambil seluruh transaksi yang belum dibundel ke setoran
        $transaksiBelumSetor = TransaksiSpp::where('petugas_id', $petugasId)
            ->whereNull('setoran_kasir_id')
            ->get();

        if ($transaksiBelumSetor->isEmpty()) {
            return redirect()->back()->withErrors([
                'error' => 'Tidak ada transaksi yang belum disetor saat ini.',
            ]);
        }

        DB::beginTransaction();
        try {
            // Hitung uang tunai fisik yang harus diserahkan ke bendahara
            $totalTunaiFisik = 0;
            foreach ($transaksiBelumSetor as $trx) {
                $metode = is_object($trx->metode_pembayaran) && property_exists($trx->metode_pembayaran, 'value')
                    ? $trx->metode_pembayaran->value
                    : (string) $trx->metode_pembayaran;

                if ($metode === 'tunai') {
                    $totalTunaiFisik += (float) $trx->jumlah_dibayar;
                }
            }

            // Generate nomor setoran unik: STR-YYYYMMDD-XXXX
            $prefix = 'STR-' . date('Ymd') . '-';
            $terakhir = SetoranKasir::where('nomor_setoran', 'like', "{$prefix}%")
                ->orderByDesc('id')
                ->value('nomor_setoran');

            $urutan = $terakhir ? ((int) substr($terakhir, -4)) + 1 : 1;
            $nomorSetoran = $prefix . str_pad((string) $urutan, 4, '0', STR_PAD_LEFT);

            // Simpan catatan setoran kasir baru
            $setoran = SetoranKasir::create([
                'nomor_setoran' => $nomorSetoran,
                'staf_tu_id' => $petugasId,
                'tanggal_setoran' => Carbon::today(),
                'total_sistem' => $totalTunaiFisik, // Uang fisik tunai yang tercatat di sistem
                'total_fisik' => null,
                'selisih' => 0,
                'status' => 'menunggu_verifikasi',
            ]);

            // Kunci semua id transaksi ke setoran ini
            TransaksiSpp::whereIn('id', $transaksiBelumSetor->pluck('id'))->update([
                'setoran_kasir_id' => $setoran->id,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Kasir berhasil ditutup! Silakan serahkan uang fisik ke Bendahara.');
        } catch (Throwable $th) {
            DB::rollBack();

            return redirect()->back()->withErrors(['error' => 'Gagal menutup kasir: ' . $th->getMessage()]);
        }
    }

    public function verifikasi(VerifikasiSetoranKasirRequest $request, int $setoranKasir): RedirectResponse
    {
        $setoran = SetoranKasir::find($setoranKasir);

        $statusSetoran = is_object($setoran?->status) && property_exists($setoran->status, 'value')
            ? $setoran->status->value
            : (string) $setoran?->status;

        if (! $setoran || $statusSetoran === 'diterima') {
            return redirect()->back()->withErrors([
                'error' => 'Setoran kasir ini sudah diverifikasi sebelumnya atau tidak ditemukan.',
            ]);
        }

        $validated = $request->validated();
        $totalFisik = (float) $validated['total_fisik'];
        $selisih = $totalFisik - (float) $setoran->total_sistem;

        DB::beginTransaction();
        try {
            $setoran->update([
                'bendahara_id' => $request->user()->id,
                'total_fisik' => $totalFisik,
                'selisih' => $selisih,
                'status' => $validated['status'],
                'catatan_bendahara' => $validated['catatan_bendahara'] ?? null,
                'waktu_verifikasi' => Carbon::now(),
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Verifikasi setoran kasir berhasil diperbarui.');
        } catch (Throwable $th) {
            DB::rollBack();

            return redirect()->back()->withErrors(['error' => 'Gagal memverifikasi setoran: ' . $th->getMessage()]);
        }
    }
}