<?php

namespace App\Http\Controllers;

use App\Enums\PeranPengguna;
use App\Models\DispensasiSpp;
use App\Models\Kelas;
use App\Models\KeringananSpp;
use App\Models\SetoranKasir;
use App\Models\Siswa;
use App\Models\TagihanSpp;
use App\Models\TarifSpp;
use App\Models\TransaksiSpp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $roleValue = $user->role instanceof PeranPengguna ? $user->role->value : (string) $user->role;

        $ringkasan = [];
        $grafik = null;
        $tagihan = [];

        try {
            if ($roleValue === PeranPengguna::ADMIN->value) {
                $totalSiswa = Siswa::count();
                $siswaAktif = Siswa::where('status_siswa', 'aktif')->count();
                $siswaLulus = Siswa::where('status_siswa', 'lulus')->count();
                $siswaKeluar = Siswa::whereIn('status_siswa', ['pindah', 'keluar'])->count();

                $tagihanLunasCount = TagihanSpp::where('status', 'lunas')->count();
                $tagihanBelumLunasCount = TagihanSpp::where('status', '!=', 'lunas')->count();
                $totalTagihanCount = TagihanSpp::count();
                $totalTunggakanNominal = TagihanSpp::where('status', '!=', 'lunas')->sum('sisa_tagihan');

                $ruteTagihan = Route::has('admin.tagihan-siswa.index')
                    ? route('admin.tagihan-siswa.index')
                    : (Route::has('admin.tagihan-spp.index') ? route('admin.tagihan-spp.index') : null);

                $ringkasan = [
                    ['label' => 'Total Siswa Terdaftar', 'nilai' => $totalSiswa, 'sub' => "{$siswaAktif} Aktif, {$siswaLulus} Lulus", 'url' => route('admin.siswa.index')],
                    ['label' => 'Master Rombel / Kelas', 'nilai' => Kelas::count(), 'sub' => 'Tingkat X, XI, XII', 'url' => route('admin.kelas.index')],
                    ['label' => 'Pengguna Sistem', 'nilai' => User::where('is_active', true)->count(), 'sub' => 'Akun status aktif', 'url' => route('admin.users.index')],
                    ['label' => 'Skema Tarif SPP', 'nilai' => TarifSpp::count(), 'sub' => 'Tahun ajaran aktif', 'url' => route('admin.tarif-spp.index')],
                    ['label' => 'Program Keringanan', 'nilai' => KeringananSpp::count(), 'sub' => 'Kategori bantuan SPP', 'url' => route('admin.keringanan-spp.index')],
                    ['label' => 'Tagihan Belum Lunas', 'nilai' => $tagihanBelumLunasCount, 'sub' => 'Perlu ditagihkan', 'url' => $ruteTagihan],
                    ['label' => 'Total Tunggakan SPP', 'nilai' => $totalTunggakanNominal, 'tipe' => 'rupiah', 'sub' => 'Akumulasi sisa kewajiban', 'url' => $ruteTagihan],
                ];

                // Data untuk grafik rasio pelunasan
                $persenLunas = $totalTagihanCount > 0 ? round(($tagihanLunasCount / $totalTagihanCount) * 100) : 0;
                $grafik = [
                    'total_tagihan' => $totalTagihanCount,
                    'lunas' => $tagihanLunasCount,
                    'belum_lunas' => $tagihanBelumLunasCount,
                    'persentase_lunas' => $persenLunas,
                    'siswa_aktif' => $siswaAktif,
                    'siswa_lulus' => $siswaLulus,
                    'siswa_keluar' => $siswaKeluar,
                ];
            } elseif ($roleValue === PeranPengguna::STAF_TU->value) {
                $ringkasan = [
                    ['label' => 'Transaksi hari ini', 'nilai' => TransaksiSpp::where('petugas_id', $user->id)->whereDate('created_at', today())->count()],
                    ['label' => 'Diterima hari ini', 'nilai' => TransaksiSpp::where('petugas_id', $user->id)->whereDate('created_at', today())->sum('jumlah_dibayar'), 'tipe' => 'rupiah'],
                ];
            } elseif ($roleValue === PeranPengguna::BENDAHARA->value) {
                $ringkasan = [
                    ['label' => 'Setoran menunggu verifikasi', 'nilai' => SetoranKasir::where('status', 'menunggu_verifikasi')->count()],
                ];
            } elseif ($roleValue === PeranPengguna::KEPALA_SEKOLAH->value) {
                $ringkasan = [
                    ['label' => 'Dispensasi menunggu keputusan', 'nilai' => DispensasiSpp::where('status_pengajuan', 'diajukan')->count()],
                ];
            } elseif ($roleValue === PeranPengguna::KOMITE_SEKOLAH->value) {
                $ringkasan = [
                    ['label' => 'Tarif menunggu persetujuan', 'nilai' => TarifSpp::where('status_tarif', 'draft')->count()],
                ];
            } elseif ($roleValue === PeranPengguna::SISWA->value) {
                $ringkasan = [
                    ['label' => 'Total sisa kewajiban', 'nilai' => TagihanSpp::whereHas('siswa', fn ($q) => $q->where('user_id', $user->id))->sum('sisa_tagihan'), 'tipe' => 'rupiah'],
                ];

                $tagihan = TagihanSpp::whereHas('siswa', fn ($q) => $q->where('user_id', $user->id))
                    ->where('sisa_tagihan', '>', 0)
                    ->with('siswa:id,nama_lengkap')
                    ->orderBy('jatuh_tempo')
                    ->limit(5)
                    ->get()
                    ->map(fn ($t) => [
                        'id' => $t->id,
                        'siswa' => $t->siswa?->nama_lengkap ?? 'Siswa',
                        'bulan' => $t->bulan,
                        'tahun' => $t->tahun,
                        'sisa_tagihan' => $t->sisa_tagihan,
                        'jatuh_tempo' => $t->jatuh_tempo,
                    ])
                    ->all();
            }
        } catch (Throwable $e) {
            report($e);
        }

        return Inertia::render('RoleDashboard', [
            'role' => $roleValue,
            'ringkasan' => $ringkasan,
            'grafik' => $grafik,
            'tagihan' => $tagihan,
        ]);
    }
}
