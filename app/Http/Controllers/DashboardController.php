<?php

namespace App\Http\Controllers;

use App\Enums\MetodePembayaran;
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

                $ruteTagihan = route()->has('admin.tagihan-siswa.index')
                    ? route('admin.tagihan-siswa.index')
                    : (route()->has('admin.tagihan-spp.index') ? route('admin.tagihan-spp.index') : null);

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

                $transaksiTerbaru = TransaksiSpp::with(['tagihan.siswa', 'petugas'])
                    ->latest('waktu_transaksi')
                    ->limit(5)
                    ->get()
                    ->map(fn ($t) => [
                        'id' => $t->id,
                        'nomor_kuitansi' => $t->nomor_kuitansi,
                        'siswa' => $t->tagihan?->siswa?->nama_lengkap ?? 'Siswa',
                        'nis' => $t->tagihan?->siswa?->nis ?? '-',
                        'jumlah' => (float) $t->jumlah_dibayar,
                        'waktu' => $t->waktu_transaksi?->toISOString() ?? $t->created_at?->toISOString(),
                        'metode' => $t->metode_pembayaran instanceof MetodePembayaran ? $t->metode_pembayaran->value : (string) $t->metode_pembayaran,
                        'petugas' => $t->petugas?->name ?? 'Petugas TU',
                    ])
                    ->all();

                return Inertia::render('Admin/Dashboard', [
                    'ringkasan' => $ringkasan,
                    'grafik' => $grafik,
                    'transaksiTerbaru' => $transaksiTerbaru,
                ]);
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
                $siswa = Siswa::with(['kelas'])->where('user_id', $user->id)->first();
                $siswaId = $siswa?->id;

                $totalTagihanNominal = $siswaId ? (float) TagihanSpp::where('siswa_id', $siswaId)->sum('nominal_tagihan') : 0;
                $totalDibayarNominal = $siswaId ? (float) TagihanSpp::where('siswa_id', $siswaId)->sum('total_terbayar') : 0;
                $totalTunggakanNominal = $siswaId ? (float) TagihanSpp::where('siswa_id', $siswaId)->where('status', '!=', 'lunas')->sum('sisa_tagihan') : 0;

                $totalTagihanCount = $siswaId ? TagihanSpp::where('siswa_id', $siswaId)->count() : 0;
                $lunasCount = $siswaId ? TagihanSpp::where('siswa_id', $siswaId)->where('status', 'lunas')->count() : 0;
                $sebagianCount = $siswaId ? TagihanSpp::where('siswa_id', $siswaId)->where('status', 'sebagian')->count() : 0;
                $belumLunasCount = $siswaId ? TagihanSpp::where('siswa_id', $siswaId)->where('status', '!=', 'lunas')->count() : 0;

                $statusKeseluruhan = match (true) {
                    $totalTagihanCount === 0 => 'belum_ada_tagihan',
                    $belumLunasCount === 0 => 'lunas',
                    $totalDibayarNominal > 0 => 'sebagian',
                    default => 'belum_bayar',
                };

                $tagihanBelumLunas = $siswaId
                    ? TagihanSpp::where('siswa_id', $siswaId)
                        ->where('status', '!=', 'lunas')
                        ->with(['tarif', 'keringanan'])
                        ->orderBy('tahun', 'asc')
                        ->orderBy('bulan', 'asc')
                        ->get()
                        ->map(fn ($t) => [
                            'id' => $t->id,
                            'bulan' => $t->bulan,
                            'tahun' => $t->tahun,
                            'nominal_asli' => (float) $t->nominal_asli,
                            'nominal_potongan' => (float) $t->nominal_potongan,
                            'nominal_tagihan' => (float) $t->nominal_tagihan,
                            'total_terbayar' => (float) $t->total_terbayar,
                            'sisa_tagihan' => (float) $t->sisa_tagihan,
                            'status' => is_object($t->status) ? $t->status->value : (string) $t->status,
                            'jatuh_tempo' => $t->jatuh_tempo ? $t->jatuh_tempo->format('Y-m-d') : null,
                            'keterangan_tarif' => $t->tarif?->keterangan ?? ('Tarif SPP '.$t->tahun),
                            'nama_keringanan' => $t->keringanan?->nama_program,
                        ])
                        ->all()
                    : [];

                $transaksiTerbaru = $siswaId
                    ? TransaksiSpp::whereHas('tagihan', fn ($q) => $q->where('siswa_id', $siswaId))
                        ->with(['tagihan', 'petugas'])
                        ->latest('waktu_transaksi')
                        ->limit(5)
                        ->get()
                        ->map(fn ($trx) => [
                            'id' => $trx->id,
                            'nomor_kuitansi' => $trx->nomor_kuitansi ?? ('#TRX-'.$trx->id),
                            'bulan' => $trx->tagihan?->bulan,
                            'tahun' => $trx->tagihan?->tahun,
                            'jumlah_dibayar' => (float) $trx->jumlah_dibayar,
                            'waktu_transaksi' => $trx->waktu_transaksi?->toISOString() ?? $trx->created_at?->toISOString(),
                            'metode' => is_object($trx->metode_pembayaran) ? $trx->metode_pembayaran->value : (string) $trx->metode_pembayaran,
                            'petugas' => $trx->petugas?->name ?? 'Kasir Loket TU',
                        ])
                        ->all()
                    : [];

                $dispensasiAktif = $siswaId
                    ? DispensasiSpp::whereHas('tagihan', fn ($q) => $q->where('siswa_id', $siswaId))
                        ->with('tagihan')
                        ->latest()
                        ->first()
                    : null;

                return Inertia::render('Siswa/Dashboard', [
                    'siswa' => $siswa ? [
                        'id' => $siswa->id,
                        'nama_lengkap' => $siswa->nama_lengkap,
                        'nis' => $siswa->nis,
                        'nisn' => $siswa->nisn,
                        'kelas' => $siswa->kelas?->nama_kelas ?? '-',
                        'status_siswa' => $siswa->status_siswa,
                    ] : null,
                    'ringkasan' => [
                        'total_tagihan' => $totalTagihanNominal,
                        'total_dibayar' => $totalDibayarNominal,
                        'sisa_kewajiban' => $totalTunggakanNominal,
                        'count_tagihan' => $totalTagihanCount,
                        'count_lunas' => $lunasCount,
                        'count_sebagian' => $sebagianCount,
                        'count_belum_lunas' => $belumLunasCount,
                        'status_keseluruhan' => $statusKeseluruhan,
                    ],
                    'tagihanBelumLunas' => $tagihanBelumLunas,
                    'transaksiTerbaru' => $transaksiTerbaru,
                    'dispensasiAktif' => $dispensasiAktif ? [
                        'id' => $dispensasiAktif->id,
                        'periode' => 'Bulan '.($dispensasiAktif->tagihan?->bulan ?? '-').' / '.($dispensasiAktif->tagihan?->tahun ?? '-'),
                        'status' => $dispensasiAktif->status,
                        'batas_waktu_baru' => $dispensasiAktif->batas_waktu_baru,
                        'alasan' => $dispensasiAktif->alasan_penundaan,
                        'catatan_pimpinan' => $dispensasiAktif->catatan_pimpinan,
                    ] : null,
                    'totalKewajiban' => $totalTunggakanNominal,
                    'tagihanList' => $tagihanBelumLunas,
                ]);
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
