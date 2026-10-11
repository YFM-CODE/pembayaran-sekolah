<?php

namespace App\Http\Controllers;

use App\Enums\PeranPengguna;
use App\Interfaces\TransaksiSppInterface;
use Illuminate\Http\Request;

class CetakKuitansiController extends Controller
{
    public function __construct(
        protected TransaksiSppInterface $transaksiRepo
    ) {}

    public function kuitansi(Request $request, int $transaksiSpp)
    {
        $transaksi = $this->transaksiRepo->cariBerdasarkanId($transaksiSpp);

        if (! $transaksi) {
            abort(404, 'Data transaksi SPP tidak ditemukan.');
        }

        $user = $request->user();
        $roleValue = is_object($user->role) ? $user->role->value : (string) $user->role;

        // Otorisasi siswa: hanya dapat melihat kuitansi miliknya sendiri
        if ($roleValue === PeranPengguna::SISWA->value) {
            if ($transaksi->tagihan?->siswa?->user_id !== $user->id) {
                abort(403, 'Anda tidak memiliki hak akses untuk melihat kuitansi ini.');
            }
        }

        $tagihan = $transaksi->tagihan;
        $siswa = $tagihan?->siswa;

        $data = [
            'transaksi' => $transaksi,
            'nomor_kuitansi' => $transaksi->nomor_kuitansi ?? ('#TRX-'.$transaksi->id),
            'nomor_transaksi' => $transaksi->nomor_kuitansi ?? ('#TRX-'.$transaksi->id),
            'nama_siswa' => $siswa?->nama_lengkap ?? 'Siswa',
            'nis' => $siswa?->nis ?? '-',
            'nisn' => $siswa?->nisn ?? '-',
            'kelas' => $siswa?->kelas?->nama_kelas ?? '-',
            'periode' => 'Bulan '.($tagihan?->bulan ?? '-').' / Tahun '.($tagihan?->tahun ?? '-'),
            'jumlah_dibayar' => (float) $transaksi->jumlah_dibayar,
            'metode' => $transaksi->metode_pembayaran,
            'petugas' => $transaksi->petugas?->name ?? 'Kasir Loket TU',
            'tanggal_bayar' => $transaksi->waktu_transaksi?->translatedFormat('d F Y H:i') ?? $transaksi->created_at?->translatedFormat('d F Y H:i'),
            'sisa_tagihan' => max(0, (float) ($tagihan?->sisa_tagihan ?? 0)),
            'catatan' => $transaksi->catatan,
        ];

        return view('exports.pdf.kuitansi-spp', $data);
    }

    public function thermal(Request $request, int $transaksiSpp)
    {
        $transaksi = $this->transaksiRepo->cariBerdasarkanId($transaksiSpp);

        if (! $transaksi) {
            abort(404, 'Data transaksi SPP tidak ditemukan.');
        }

        $user = $request->user();
        $roleValue = is_object($user->role) ? $user->role->value : (string) $user->role;

        // Otorisasi siswa: hanya dapat melihat kuitansi miliknya sendiri
        if ($roleValue === PeranPengguna::SISWA->value) {
            if ($transaksi->tagihan?->siswa?->user_id !== $user->id) {
                abort(403, 'Anda tidak memiliki hak akses untuk melihat kuitansi ini.');
            }
        }

        $tagihan = $transaksi->tagihan;
        $siswa = $tagihan?->siswa;

        $data = [
            'transaksi' => $transaksi,
            'nomor_kuitansi' => $transaksi->nomor_kuitansi ?? ('#TRX-'.$transaksi->id),
            'nomor_transaksi' => $transaksi->nomor_kuitansi ?? ('#TRX-'.$transaksi->id),
            'nama_siswa' => $siswa?->nama_lengkap ?? 'Siswa',
            'nis' => $siswa?->nis ?? '-',
            'kelas' => $siswa?->kelas?->nama_kelas ?? '-',
            'periode' => 'Bulan '.($tagihan?->bulan ?? '-').' / Tahun '.($tagihan?->tahun ?? '-'),
            'jumlah_dibayar' => (float) $transaksi->jumlah_dibayar,
            'metode' => $transaksi->metode_pembayaran,
            'petugas' => $transaksi->petugas?->name ?? 'Kasir Loket TU',
            'tanggal_bayar' => $transaksi->waktu_transaksi?->translatedFormat('d F Y H:i') ?? $transaksi->created_at?->translatedFormat('d F Y H:i'),
            'sisa_tagihan' => max(0, (float) ($tagihan?->sisa_tagihan ?? 0)),
        ];

        return view('exports.thermal.struk-kasir', $data);
    }
}
