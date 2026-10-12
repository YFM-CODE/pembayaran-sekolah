<?php

namespace App\Http\Controllers;

use App\Filters\GlobalFilter;
use App\Http\Requests\SimpanPembayaranSppRequest;
use App\Interfaces\TagihanSppInterface;
use App\Interfaces\TransaksiSppInterface;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TagihanSpp;
use App\Models\TransaksiSpp;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class PembayaranSppController extends Controller
{
    public function __construct(
        protected TransaksiSppInterface $transaksiRepo,
        protected TagihanSppInterface $tagihanRepo
    ) {}

    public function index(GlobalFilter $filter): Response
    {
        return Inertia::render('PembayaranSpp/Index', [
            'transaksi' => $this->transaksiRepo->paginate($filter),
            'daftarKelas' => Kelas::orderBy('nama_kelas')->get(['id', 'nama_kelas']),
            'filters' => request()->all(['search']),
        ]);
    }

    public function cariSiswa(Request $request): JsonResponse
    {
        $keyword = trim((string) $request->query('q'));
        $kelasId = $request->query('kelas_id');

        // 1. Ambil Siswa dengan relasi kelas yang pasti
        $query = Siswa::with('kelas');

        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }

        if ($keyword !== '') {
            $query->where(function ($sub) use ($keyword) {
                $sub->where('nis', 'like', "%{$keyword}%")
                    ->orWhere('nama_lengkap', 'like', "%{$keyword}%");
            });
        }

        $daftarSiswa = $query->limit(20)->get();

        if ($daftarSiswa->isEmpty()) {
            return response()->json([]);
        }

        // 2. Ambil tagihan aktif langsung dari database agar aman dari perbedaan nama relasi Model
        $siswaIds = $daftarSiswa->pluck('id');
        $tagihanGroup = DB::table('tagihan_spp')
            ->whereIn('siswa_id', $siswaIds)
            ->whereIn('status', ['belum_bayar', 'sebagian'])
            ->orderBy('tahun', 'asc')
            ->orderBy('bulan', 'asc')
            ->get()
            ->groupBy('siswa_id');

        // 3. Pasangkan tagihan ke properti tagihan_spp pada masing-masing siswa
        $daftarSiswa->each(function ($siswa) use ($tagihanGroup) {
            $siswa->tagihan_spp = $tagihanGroup->get($siswa->id, collect());
        });

        return response()->json($daftarSiswa);
    }

    public function store(SimpanPembayaranSppRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::beginTransaction();
        try {
            /** @var TagihanSpp $tagihan */
            $tagihan = TagihanSpp::where('id', $data['tagihan_spp_id'])
                ->lockForUpdate()
                ->first();

            if (! $tagihan || $tagihan->status === 'lunas') {
                throw ValidationException::withMessages([
                    'tagihan_spp_id' => 'Tagihan ini sudah lunas atau tidak ditemukan.',
                ]);
            }

            if ((float) $data['jumlah_dibayar'] > (float) $tagihan->sisa_tagihan) {
                throw ValidationException::withMessages([
                    'jumlah_dibayar' => 'Jumlah bayar tidak boleh melebihi sisa tagihan (Rp '.number_format($tagihan->sisa_tagihan, 0, ',', '.').').',
                ]);
            }

            // Nomor kuitansi unik: KW-SPP-YYYYMM-XXXX
            $prefix = 'KW-SPP-'.date('Ym').'-';
            $terakhir = TransaksiSpp::where('nomor_kuitansi', 'like', "{$prefix}%")
                ->orderByDesc('id')
                ->value('nomor_kuitansi');

            $urutan = $terakhir ? ((int) substr($terakhir, -4)) + 1 : 1;
            $nomorKuitansi = $prefix.str_pad((string) $urutan, 4, '0', STR_PAD_LEFT);

            // Simpan transaksi kasir
            $trx = TransaksiSpp::create([
                'nomor_kuitansi' => $nomorKuitansi,
                'tagihan_spp_id' => $tagihan->id,
                'petugas_id' => $request->user()->id,
                'jumlah_dibayar' => $data['jumlah_dibayar'],
                'waktu_transaksi' => Carbon::now(),
                'metode_pembayaran' => $data['metode_pembayaran'],
                'nomor_referensi_bank' => $data['nomor_referensi_bank'] ?? null,
                'catatan' => $data['catatan'] ?? null,
            ]);

            // Update nominal & status tagihan
            $totalTerbayarBaru = (float) $tagihan->total_terbayar + (float) $data['jumlah_dibayar'];
            $sisaBaru = (float) $tagihan->nominal_tagihan - $totalTerbayarBaru;

            $tagihan->update([
                'total_terbayar' => $totalTerbayarBaru,
                'sisa_tagihan' => max(0, $sisaBaru),
                'status' => $sisaBaru <= 0 ? 'lunas' : 'sebagian',
            ]);

            DB::commit();

            // Ambil relasi siswa & petugas untuk respon kuitansi
            $siswa = Siswa::with('kelas')->find($tagihan->siswa_id);

            return redirect()->back()->with([
                'success' => 'Pembayaran berhasil disimpan.',
                'kuitansi' => [
                    'id' => $trx->id,
                    'nomor_kuitansi' => $trx->nomor_kuitansi,
                    'jumlah_dibayar' => $trx->jumlah_dibayar,
                    'metode_pembayaran' => $trx->metode_pembayaran,
                    'waktu_transaksi' => Carbon::parse($trx->waktu_transaksi)->translatedFormat('d F Y H:i'),
                    'nama_siswa' => $siswa?->nama_lengkap ?? '-',
                    'nis' => $siswa?->nis ?? '-',
                    'kelas' => $siswa?->kelas?->nama_kelas ?? '-',
                    'bulan' => $tagihan->bulan,
                    'tahun' => $tagihan->tahun,
                    'sisa_tagihan' => max(0, $sisaBaru),
                    'status_tagihan' => $sisaBaru <= 0 ? 'lunas' : 'sebagian',
                    'petugas' => $request->user()->name,
                ],
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (Throwable $th) {
            DB::rollBack();

            return redirect()->back()->withErrors(['error' => 'Gagal memproses pembayaran: '.$th->getMessage()]);
        }
    }
}
