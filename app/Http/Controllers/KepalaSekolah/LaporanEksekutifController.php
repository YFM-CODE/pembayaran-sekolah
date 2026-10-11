<?php

namespace App\Http\Controllers\KepalaSekolah;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\SetoranKasir;
use App\Models\TransaksiSpp;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LaporanEksekutifController extends Controller
{
    public function index(Request $request): Response
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->endOfMonth()->toDateString());

        // Transaksi dalam rentang tanggal
        $queryTransaksi = TransaksiSpp::with(['tagihan.siswa.kelas', 'petugas'])
            ->whereBetween('waktu_transaksi', ["{$startDate} 00:00:00", "{$endDate} 23:59:59"]);

        $totalPenerimaan = (float) $queryTransaksi->sum('jumlah_dibayar');
        $transaksi = $queryTransaksi->orderByDesc('waktu_transaksi')->paginate(15)->withQueryString();

        // Rekap Setoran Kasir Harian
        $setoranKasir = SetoranKasir::with(['stafTu', 'bendahara'])
            ->whereBetween('tanggal_setoran', [$startDate, $endDate])
            ->orderByDesc('tanggal_setoran')
            ->get();

        return Inertia::render('KepalaSekolah/Laporan', [
            'transaksi' => $transaksi,
            'setoranKasir' => $setoranKasir,
            'totalPenerimaan' => $totalPenerimaan,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    public function cetak(Request $request)
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->endOfMonth()->toDateString());

        $transaksi = TransaksiSpp::with(['tagihan.siswa.kelas', 'petugas'])
            ->whereBetween('waktu_transaksi', ["{$startDate} 00:00:00", "{$endDate} 23:59:59"])
            ->orderBy('waktu_transaksi')
            ->get();

        $totalTunai = $transaksi->where('metode_pembayaran', 'tunai')->sum('jumlah_dibayar');
        $totalTransfer = $transaksi->where('metode_pembayaran', 'transfer')->sum('jumlah_dibayar');
        $totalSemua = $transaksi->sum('jumlah_dibayar');

        return view('cetak.laporan-eksekutif', [
            'transaksi' => $transaksi,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalTunai' => $totalTunai,
            'totalTransfer' => $totalTransfer,
            'totalSemua' => $totalSemua,
        ]);
    }
}