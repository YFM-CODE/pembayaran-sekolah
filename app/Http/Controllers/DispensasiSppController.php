<?php

namespace App\Http\Controllers;

use App\Enums\PeranPengguna;
use App\Filters\GlobalFilter;
use App\Http\Requests\SimpanPengajuanDispensasiRequest;
use App\Interfaces\DispensasiSppInterface;
use App\Models\DispensasiSpp;
use App\Models\TagihanSpp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class DispensasiSppController extends Controller
{
    public function __construct(
        protected DispensasiSppInterface $dispensasiRepo
    ) {}

    public function index(Request $request, GlobalFilter $filter): Response
    {
        $user = $request->user();
        $roleValue = is_object($user?->role) ? $user->role->value : (string) $user?->role;

        $dispensasiData = ($roleValue === PeranPengguna::SISWA->value)
            ? DispensasiSpp::with(['tagihan.siswa.kelas', 'pemohon', 'kepalaSekolah'])
                ->whereHas('tagihan.siswa', fn ($q) => $q->where('user_id', $user->id))
                ->latest()
                ->paginate(15)
                ->withQueryString()
            : $this->dispensasiRepo->paginate($filter);

        $tagihanQuery = TagihanSpp::with(['siswa.kelas'])
            ->where('sisa_tagihan', '>', 0)
            ->orderBy('tahun')
            ->orderBy('bulan');

        if ($roleValue === PeranPengguna::SISWA->value) {
            $tagihanQuery->whereHas('siswa', fn ($q) => $q->where('user_id', $user->id));
        }

        $tagihanBelumLunas = $tagihanQuery->get();

        $daftarSiswaTagihan = $tagihanBelumLunas->groupBy('siswa_id')->map(function ($items) {
            $siswa = $items->first()->siswa;

            return [
                'id' => $siswa?->id,
                'nis' => $siswa?->nis,
                'nama_lengkap' => $siswa?->nama_lengkap,
                'kelas' => $siswa?->kelas?->nama_kelas ?? '-',
                'tagihan' => $items->map(function ($t) {
                    return [
                        'id' => $t->id,
                        'periode' => "Bulan {$t->bulan} / {$t->tahun}",
                        'nominal' => (float) $t->nominal_tagihan,
                        'sisa' => (float) $t->sisa_tagihan,
                        'jatuh_tempo_asal' => $t->jatuh_tempo,
                    ];
                })->values(),
            ];
        })->values();

        return Inertia::render('DispensasiSpp/Index', [
            'dispensasi' => $dispensasiData,
            'daftarSiswaTagihan' => $daftarSiswaTagihan,
            'filters' => request()->all(['search']),
        ]);
    }

    public function store(SimpanPengajuanDispensasiRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $pathDokumen = null;
            if ($request->hasFile('dokumen_pendukung')) {
                $pathDokumen = $request->file('dokumen_pendukung')->store('dokumen_dispensasi', 'public');
            }

            $this->dispensasiRepo->simpan([
                'tagihan_spp_id' => $validated['tagihan_spp_id'],
                'pemohon_id' => $request->user()->id,
                'tanggal_pengajuan' => now()->toDateString(),
                'batas_waktu_baru' => $validated['batas_waktu_baru'],
                'alasan_penundaan' => $validated['alasan_penundaan'],
                'dokumen_pendukung' => $pathDokumen,
                'status' => 'menunggu_persetujuan',
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Pengajuan dispensasi berhasil dikirim.');
        } catch (Throwable $th) {
            DB::rollBack();

            return redirect()->back()->withErrors(['error' => 'Gagal mengajukan dispensasi: '.$th->getMessage()]);
        }
    }

    public function persetujuan(Request $request, int $dispensasiSpp): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:disetujui,ditolak'],
            'catatan_pimpinan' => ['nullable', 'string', 'max:500'],
        ]);

        $dispensasi = $this->dispensasiRepo->cariBerdasarkanId($dispensasiSpp);

        if (! $dispensasi) {
            return redirect()->back()->withErrors(['dispensasi' => 'Data permohonan dispensasi tidak ditemukan.']);
        }

        DB::beginTransaction();
        try {
            $this->dispensasiRepo->perbarui($dispensasiSpp, [
                'disetujui_oleh' => $request->user()->id,
                'status' => $validated['status'],
                'catatan_pimpinan' => $validated['catatan_pimpinan'] ?? null,
                'tanggal_diproses' => now(),
            ]);

            if ($validated['status'] === 'disetujui') {
                $tagihan = $dispensasi->tagihan ?? TagihanSpp::find($dispensasi->tagihan_spp_id);
                if ($tagihan) {
                    $tagihan->update([
                        'jatuh_tempo' => $dispensasi->batas_waktu_baru,
                    ]);
                }
            }

            DB::commit();

            return redirect()->back()->with('success', 'Keputusan dispensasi berhasil disimpan.');
        } catch (Throwable $th) {
            DB::rollBack();

            return redirect()->back()->withErrors(['error' => 'Gagal memproses persetujuan dispensasi: '.$th->getMessage()]);
        }
    }
}
