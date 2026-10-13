<?php

namespace App\Http\Controllers\KepalaSekolah;

use App\Http\Controllers\Controller;
use App\Models\DispensasiSpp;
use App\Models\Siswa;
use App\Models\TagihanSpp;
use App\Models\TransaksiSpp;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class DashboardKepsekController extends Controller
{
    public function index(): Response
    {
        $bulanSekarang = Carbon::now()->month;
        $tahunSekarang = Carbon::now()->year;

        // 1. Penerimaan SPP Bulan Ini
        $penerimaanBulanIni = (float) TransaksiSpp::whereMonth('waktu_transaksi', $bulanSekarang)
            ->whereYear('waktu_transaksi', $tahunSekarang)
            ->sum('jumlah_dibayar');

        // 2. Penerimaan Tahun Berjalan
        $totalPenerimaanTahunIni = (float) TransaksiSpp::whereYear('waktu_transaksi', $tahunSekarang)
            ->sum('jumlah_dibayar');

        // 3. Akumulasi Sisa Tunggakan
        $totalTunggakan = (float) TagihanSpp::where('sisa_tagihan', '>', 0)->sum('sisa_tagihan');

        // 4. Rasio Kepatuhan Siswa
        $totalSiswa = Siswa::count();
        $idSiswaMenunggak = TagihanSpp::where('sisa_tagihan', '>', 0)
            ->distinct()
            ->pluck('siswa_id');
        $jumlahSiswaMenunggak = $idSiswaMenunggak->count();
        $siswaLunas = max(0, $totalSiswa - $jumlahSiswaMenunggak);
        $rasioKepatuhan = $totalSiswa > 0 ? round(($siswaLunas / $totalSiswa) * 100, 1) : 0;

        // 5. Permohonan Dispensasi Menunggu Persetujuan
        // Mengecek nama kolom secara adaptif (status_pengajuan vs status)
        $namaKolomStatus = Schema::hasColumn('dispensasi_spp', 'status_pengajuan') ? 'status_pengajuan' : 'status';
        $nilaiMenunggu = $namaKolomStatus === 'status_pengajuan' ? ['menunggu', 'menunggu_persetujuan'] : ['menunggu_persetujuan', 'menunggu'];

        $dispensasiMenunggu = DispensasiSpp::whereIn($namaKolomStatus, $nilaiMenunggu)->count();

        // 6. Transaksi Pembayaran Terkini
        $transaksiTerkini = TransaksiSpp::with(['tagihan.siswa.kelas', 'petugas'])
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        // 7. Siswa dengan Tunggakan Tertinggi
        $topTunggakan = TagihanSpp::with('siswa.kelas')
            ->where('sisa_tagihan', '>', 0)
            ->selectRaw('siswa_id, sum(sisa_tagihan) as total_sisa_tunggakan')
            ->groupBy('siswa_id')
            ->orderByDesc('total_sisa_tunggakan')
            ->limit(5)
            ->get()
            ->map(function ($t) {
                return [
                    'id' => $t->siswa_id,
                    'nis' => $t->siswa?->nis ?? '-',
                    'nama_lengkap' => $t->siswa?->nama_lengkap ?? 'Tanpa Nama',
                    'kelas' => $t->siswa?->kelas,
                    'total_sisa_tunggakan' => (float) $t->total_sisa_tunggakan,
                ];
            });

        return Inertia::render('KepalaSekolah/Dashboard', [
            'ringkasan' => [
                'penerimaan_bulan_ini' => $penerimaanBulanIni,
                'penerimaan_tahun_ini' => $totalPenerimaanTahunIni,
                'total_tunggakan' => $totalTunggakan,
                'total_siswa' => $totalSiswa,
                'siswa_lunas' => $siswaLunas,
                'siswa_menunggak' => $jumlahSiswaMenunggak,
                'rasio_kepatuhan' => $rasioKepatuhan,
                'dispensasi_menunggu' => $dispensasiMenunggu,
            ],
            'transaksiTerkini' => $transaksiTerkini,
            'topTunggakan' => $topTunggakan,
        ]);
    }
}
