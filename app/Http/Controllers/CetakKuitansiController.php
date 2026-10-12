<?php

namespace App\Http\Controllers;

use App\Enums\PeranPengguna;
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

        $user = $request->user();
        $roleValue = is_object($user->role) ? $user->role->value : (string) $user->role;

        // Otorisasi siswa: hanya dapat melihat kuitansi miliknya sendiri
        if ($roleValue === PeranPengguna::SISWA->value) {
            if ($transaksi->tagihan?->siswa?->user_id !== $user->id) {
                abort(403, 'Anda tidak memiliki hak akses untuk melihat kuitansi ini.');
            }
        }

        // Aksi Download Langsung File PDF
        if ($request->query('action') === 'download') {
            $pdf = Pdf::loadView('cetak.kuitansi-pdf', [
                'transaksi' => $transaksi,
                'isPdfDownload' => true,
            ])->setPaper('a5', 'landscape');

            return $pdf->download('Kuitansi-'.($transaksi->nomor_kuitansi ?? $transaksi->id).'.pdf');
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
    public function thermal(Request $request, TransaksiSpp $transaksiSpp)
    {
        $transaksi = $transaksiSpp->load([
            'tagihan.siswa.kelas',
            'petugas',
        ]);

        $user = $request->user();
        $roleValue = is_object($user->role) ? $user->role->value : (string) $user->role;

        // Otorisasi siswa: hanya dapat melihat kuitansi miliknya sendiri
        if ($roleValue === PeranPengguna::SISWA->value) {
            if ($transaksi->tagihan?->siswa?->user_id !== $user->id) {
                abort(403, 'Anda tidak memiliki hak akses untuk melihat kuitansi ini.');
            }
        }

        return view('cetak.thermal', compact('transaksi'));
    }
}
