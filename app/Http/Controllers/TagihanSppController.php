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
            $siswa = Siswa::where('user_id', $user->id)->firstOrFail();
            $tagihan = $this->tagihanRepo->daftarTagihanSiswa($siswa->id);

            return Inertia::render('Siswa/TagihanSaya', [
                'tagihan' => $tagihan,
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

        // 1. Ambil tarif yang sudah DISETUJUI oleh Komite
        $tarifDisetujui = TarifSpp::where('tahun_ajaran', $validated['tahun_ajaran'])
            ->whereHas('persetujuanKomite', function ($q) {
                $q->where('status_persetujuan', 'disetujui');
            })
            ->get();

        if ($tarifDisetujui->isEmpty()) {
            return redirect()->back()->withErrors([
                'error' => 'Tidak dapat membuat tagihan: Belum ada tarif SPP pada tahun ajaran '.$validated['tahun_ajaran'].' yang disetujui oleh Komite Sekolah.',
            ]);
        }

        DB::beginTransaction();
        try {
            // Gunakan status_siswa = 'aktif' sesuai skema migrasi tabel siswa
            $daftarSiswa = Siswa::with(['kelas', 'keringanan.keringanan'])
                ->where('status_siswa', 'aktif')
                ->get();

            $totalDibuat = 0;
            $totalDilewati = 0;

            foreach ($daftarSiswa as $siswa) {
                $tingkatRaw = $siswa->kelas?->tingkat;

                if (! $tingkatRaw) {
                    $totalDilewati++;

                    continue;
                }

                // Normalisasi tingkat agar cocok antara 'X' <-> '10', 'XI' <-> '11', 'XII' <-> '12'
                $tarif = $tarifDisetujui->first(function ($t) use ($tingkatRaw) {
                    $tTingkat = trim(str_ireplace('kelas', '', (string) $t->tingkat));
                    $sTingkat = trim(str_ireplace('kelas', '', (string) $tingkatRaw));

                    if ($tTingkat === $sTingkat) {
                        return true;
                    }
                    if (in_array($tTingkat, ['10', 'X']) && in_array($sTingkat, ['10', 'X'])) {
                        return true;
                    }
                    if (in_array($tTingkat, ['11', 'XI']) && in_array($sTingkat, ['11', 'XI'])) {
                        return true;
                    }
                    if (in_array($tTingkat, ['12', 'XII']) && in_array($sTingkat, ['12', 'XII'])) {
                        return true;
                    }

                    return false;
                });

                // Lewati jika tarif untuk tingkat siswa belum disetujui Komite
                if (! $tarif) {
                    $totalDilewati++;

                    continue;
                }

                // Cek apakah tagihan bulan & tahun tersebut sudah ada
                if ($this->tagihanRepo->cekTagihanAda($siswa->id, $validated['bulan'], $validated['tahun'])) {
                    continue;
                }

                $nominalAsli = $tarif->nominal_standar;
                $potongan = 0;
                $keringananSppId = null;

                // Ambil data penetapan SK keringanan siswa pada tahun ajaran terkait
                $penetapanKeringanan = $siswa->keringanan
                    ->where('tahun_ajaran', $validated['tahun_ajaran'])
                    ->where('aktif', true)
                    ->first();

                if ($penetapanKeringanan && $penetapanKeringanan->keringanan && $penetapanKeringanan->keringanan->aktif) {
                    $program = $penetapanKeringanan->keringanan;
                    $keringananSppId = $program->id;

                    $tipe = is_object($program->tipe_potongan) ? $program->tipe_potongan->value : $program->tipe_potongan;

                    if ($tipe === 'persentase') {
                        $potongan = $nominalAsli * ($program->nilai_potongan / 100);
                    } else {
                        $potongan = $program->nilai_potongan;
                    }
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

            if ($totalDibuat === 0) {
                return redirect()->back()->withErrors([
                    'error' => 'Tidak ada tagihan baru yang dibuat. Kemungkinan tagihan untuk periode ini sudah pernah digenerate sebelumnya atau kelas siswa belum disahkan Komite.',
                ]);
            }

            $pesan = "Berhasil membuat {$totalDibuat} tagihan SPP.";
            if ($totalDilewati > 0) {
                $pesan .= " Sebanyak {$totalDilewati} siswa dilewati karena tarif kelasnya belum disetujui Komite.";
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
