<?php

namespace App\Http\Controllers;

use App\Enums\PeranPengguna;
use App\Filters\GlobalFilter;
use App\Interfaces\TagihanSppInterface;
use App\Models\Siswa;
use App\Models\TagihanSpp;
use App\Models\TarifSpp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class TagihanSppController extends Controller
{
    public function __construct(
        protected TagihanSppInterface $tagihanRepo
    ) {}

    public function index(Request $request, GlobalFilter $filter): Response
    {
        $user = $request->user();
        $roleValue = is_object($user->role) ? $user->role->value : $user->role;

        if ($roleValue === PeranPengguna::SISWA->value) {
            $siswa = Siswa::with('kelas')->where('user_id', $user->id)->first();
            $tagihan = $siswa ? $this->tagihanRepo->daftarTagihanSiswa($siswa->id) : collect([]);

            return Inertia::render('Siswa/TagihanSaya', [
                'tagihan' => $tagihan,
                'siswa' => $siswa ? [
                    'id' => $siswa->id,
                    'nama_lengkap' => $siswa->nama_lengkap,
                    'nis' => $siswa->nis,
                    'nisn' => $siswa->nisn,
                    'kelas' => $siswa->kelas?->nama_kelas ?? '-',
                    'status_siswa' => $siswa->status_siswa,
                ] : null,
            ]);
        }

        return Inertia::render('TagihanSpp/Index', [
            'tagihan' => $this->tagihanRepo->paginate($filter),
            'filters' => request()->all(['search', 'bulan', 'tahun', 'status', 'siswa__kelas_id']),
        ]);
    }

    public function generateBulanan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tahun_ajaran' => ['required', 'string'],
            'bulan' => ['required', 'integer', 'between:1,12'],
            'tahun' => ['required', 'integer', 'min:2020'],
            'jatuh_tempo' => ['required', 'date'],
        ]);

        // Ambil tarif tahun ajaran terkait yang SUDAH DISETUJUI oleh Komite
        $tarifDisetujui = TarifSpp::where('tahun_ajaran', $validated['tahun_ajaran'])
            ->whereHas('persetujuanKomite', function ($q) {
                $q->where('status_persetujuan', 'disetujui');
            })
            ->get()
            ->keyBy('tingkat');

        if ($tarifDisetujui->isEmpty()) {
            return redirect()->back()->withErrors([
                'error' => 'Tidak dapat membuat tagihan: Belum ada tarif SPP pada tahun ajaran '.$validated['tahun_ajaran'].' yang disetujui oleh Komite Sekolah.',
            ]);
        }

        DB::beginTransaction();
        try {
            $daftarSiswa = Siswa::with(['kelas', 'keringanan'])->where('status_aktif', true)->get();

            $totalDibuat = 0;
            $totalDilewati = 0;

            foreach ($daftarSiswa as $siswa) {
                $tingkat = $siswa->kelas?->tingkat;

                // Lewati siswa jika tarif tingkatnya belum disetujui komite
                if (! $tingkat || ! $tarifDisetujui->has($tingkat)) {
                    $totalDilewati++;

                    continue;
                }

                // Cek apakah tagihan bulan & tahun tersebut sudah pernah digenerate
                if ($this->tagihanRepo->cekTagihanAda($siswa->id, $validated['bulan'], $validated['tahun'])) {
                    continue;
                }

                $tarif = $tarifDisetujui->get($tingkat);
                $nominalAsli = $tarif->nominal_standar;
                $potongan = 0;
                $keringananSppId = null;

                if ($siswa->keringanan && $siswa->keringanan->aktif) {
                    $keringananSppId = $siswa->keringanan->id;
                    $potongan = $siswa->keringanan->tipe_potongan === 'persen'
                        ? ($nominalAsli * ($siswa->keringanan->nilai_potongan / 100))
                        : $siswa->keringanan->nilai_potongan;
                }

                $nominalTagihan = max(0, $nominalAsli - $potongan);

                TagihanSpp::create([
                    'siswa_id' => $siswa->id,
                    'tarif_spp_id' => $tarif->id,
                    'keringanan_spp_id' => $keringananSppId,
                    'bulan' => $validated['bulan'],
                    'tahun' => $validated['tahun'],
                    'nominal_asli' => $nominalAsli,
                    'nominal_potongan' => $potongan,
                    'nominal_tagihan' => $nominalTagihan,
                    'total_terbayar' => 0,
                    'sisa_tagihan' => $nominalTagihan,
                    'status' => 'belum_bayar',
                    'jatuh_tempo' => $validated['jatuh_tempo'],
                ]);

                $totalDibuat++;
            }

            DB::commit();

            $pesan = "Berhasil membuat {$totalDibuat} tagihan SPP.";
            if ($totalDilewati > 0) {
                $pesan .= " Sebanyak {$totalDilewati} siswa dilewati karena tarif tingkat kelasnya belum disetujui Komite.";
            }

            return redirect()->back()->with('success', $pesan);
        } catch (Throwable $th) {
            DB::rollBack();

            return redirect()->back()->withErrors([
                'error' => 'Gagal mengenerate tagihan: '.$th->getMessage(),
            ]);
        }
    }
}
