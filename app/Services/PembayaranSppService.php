<?php

namespace App\Services;

use App\Models\TagihanSpp;
use App\Models\TransaksiSpp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PembayaranSppService
{
    public function bayar(array $data, User $petugas): TransaksiSpp
    {
        return DB::transaction(function () use ($data, $petugas) {
            /** @var TagihanSpp $tagihan */
            $tagihan = TagihanSpp::where('id', $data['tagihan_spp_id'])
                ->lockForUpdate()
                ->first();

            if (! $tagihan || $tagihan->status === 'lunas') {
                throw ValidationException::withMessages([
                    'tagihan_spp_id' => 'Tagihan ini sudah lunas atau tidak ditemukan.',
                ]);
            }

            if ((float) $data['jumlah_dibayar'] > (float) $tagihan->sisa_tagihan) {
                throw ValidationException::withMessages([
                    'jumlah_dibayar' => 'Jumlah bayar tidak boleh melebihi sisa tagihan (Rp ' . number_format($tagihan->sisa_tagihan, 0, ',', '.') . ').',
                ]);
            }

            // Generate nomor kuitansi otomatis: KW-SPP-YYYYMM-XXXX
            $nomorKuitansi = $this->generateNomorKuitansi();

            $transaksi = TransaksiSpp::create([
                'nomor_kuitansi' => $nomorKuitansi,
                'tagihan_spp_id' => $tagihan->id,
                'petugas_id' => $petugas->id,
                'jumlah_dibayar' => $data['jumlah_dibayar'],
                'waktu_transaksi' => Carbon::now(),
                'metode_pembayaran' => $data['metode_pembayaran'],
                'nomor_referensi_bank' => $data['nomor_referensi_bank'] ?? null,
                'catatan' => $data['catatan'] ?? null,
            ]);

            $totalTerbayarBaru = (float) $tagihan->total_terbayar + (float) $data['jumlah_dibayar'];
            $sisaBaru = (float) $tagihan->nominal_tagihan - $totalTerbayarBaru;

            $tagihan->update([
                'total_terbayar' => $totalTerbayarBaru,
                'sisa_tagihan' => max(0, $sisaBaru),
                'status' => $sisaBaru <= 0 ? 'lunas' : 'sebagian',
            ]);

            return $transaksi->load(['tagihan.siswa.kelas', 'petugas']);
        });
    }

    private function generateNomorKuitansi(): string
    {
        $prefix = 'KW-SPP-' . date('Ym') . '-';
        $terakhir = TransaksiSpp::where('nomor_kuitansi', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('nomor_kuitansi');

        $urutan = $terakhir ? ((int) substr($terakhir, -4)) + 1 : 1;

        return $prefix . str_pad((string) $urutan, 4, '0', STR_PAD_LEFT);
    }
}