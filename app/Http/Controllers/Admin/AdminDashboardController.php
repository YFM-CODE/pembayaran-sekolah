<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MetodePembayaran;
use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\KeringananSpp;
use App\Models\Siswa;
use App\Models\TagihanSpp;
use App\Models\TarifSpp;
use App\Models\TransaksiSpp;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $totalSiswa = Siswa::count();
        $siswaAktif = Siswa::where('status_siswa', 'aktif')->count();
        $siswaLulus = Siswa::where('status_siswa', 'lulus')->count();
        $siswaKeluar = Siswa::whereIn('status_siswa', ['pindah', 'keluar'])->count();

        $tagihanLunas = TagihanSpp::where('status', 'lunas')->count();
        $tagihanBelumLunas = TagihanSpp::where('status', '!=', 'lunas')->count();
        $totalTagihan = TagihanSpp::count();
        $totalTunggakanNominal = TagihanSpp::where('status', '!=', 'lunas')->sum('sisa_tagihan');

        $persenLunas = $totalTagihan > 0 ? round(($tagihanLunas / $totalTagihan) * 100) : 0;

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
            'ringkasan' => [
                ['label' => 'Total Siswa Terdaftar', 'nilai' => $totalSiswa, 'sub' => "{$siswaAktif} Aktif, {$siswaLulus} Lulus", 'url' => route('admin.siswa.index')],
                ['label' => 'Master Rombel / Kelas', 'nilai' => Kelas::count(), 'sub' => 'Tingkat X, XI, XII', 'url' => route('admin.kelas.index')],
                ['label' => 'Pengguna Sistem', 'nilai' => User::where('is_active', true)->count(), 'sub' => 'Akun aktif', 'url' => route('admin.users.index')],
                ['label' => 'Skema Tarif SPP', 'nilai' => TarifSpp::count(), 'sub' => 'Tahun ajaran aktif', 'url' => route('admin.tarif-spp.index')],
                ['label' => 'Program Keringanan', 'nilai' => KeringananSpp::count(), 'sub' => 'Kategori bantuan SPP', 'url' => route('admin.keringanan-spp.index')],
                ['label' => 'Tagihan Belum Lunas', 'nilai' => $tagihanBelumLunas, 'sub' => 'Perlu penagihan', 'url' => route('admin.tagihan-spp.index')],
                ['label' => 'Total Tunggakan SPP', 'nilai' => $totalTunggakanNominal, 'tipe' => 'rupiah', 'sub' => 'Akumulasi sisa kewajiban', 'url' => route('admin.tagihan-spp.index')],
            ],
            'grafik' => [
                'total_tagihan' => $totalTagihan,
                'lunas' => $tagihanLunas,
                'belum_lunas' => $tagihanBelumLunas,
                'persentase_lunas' => $persenLunas,
                'siswa_aktif' => $siswaAktif,
                'siswa_lulus' => $siswaLulus,
                'siswa_keluar' => $siswaKeluar,
            ],
            'transaksiTerbaru' => $transaksiTerbaru,
        ]);
    }
}
