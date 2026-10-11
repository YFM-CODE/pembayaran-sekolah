<?php

namespace App\Http\Controllers;

use App\Models\TransaksiSpp;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CetakKuitansiController extends Controller
{
    /**
     * Kuitansi Dokumen Formal (Print Browser atau Download PDF Asli)
     */
    public function kuitansi(Request $request, TransaksiSpp $transaksiSpp)
    {
        $transaksi = $transaksiSpp->load([
            'tagihan.siswa.kelas',
            'petugas',
        ]);

        // Aksi Download Langsung File PDF
        if ($request->query('action') === 'download') {
            $pdf = Pdf::loadView('cetak.kuitansi-pdf', [
                'transaksi' => $transaksi,
                'isPdfDownload' => true,
            ])->setPaper('a5', 'landscape');

            return $pdf->download('Kuitansi-' . $transaksi->nomor_kuitansi . '.pdf');
        }

        // Tampilan Cetak Layar Browser
        return view('cetak.kuitansi-pdf', [
            'transaksi' => $transaksi,
            'isPdfDownload' => false,
        ]);
    }

    /**
     * Struk Thermal Kasir
     */
    public function thermal(TransaksiSpp $transaksiSpp)
    {
        $transaksi = $transaksiSpp->load([
            'tagihan.siswa.kelas',
            'petugas',
        ]);

        return view('cetak.thermal', compact('transaksi'));
    }
}