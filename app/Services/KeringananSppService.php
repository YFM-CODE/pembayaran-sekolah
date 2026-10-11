<?php

namespace App\Services;

use App\Models\KeringananSpp;
use App\Models\SiswaKeringananSpp;
use Illuminate\Support\Facades\DB;

class KeringananSppService
{
    /**
     * Simpan program keringanan baru.
     */
    public function simpanProgram(array $data): KeringananSpp
    {
        return KeringananSpp::create([
            'nama_program' => $data['nama_program'],
            'tipe_potongan' => $data['tipe_potongan'],
            'nilai_potongan' => $data['nilai_potongan'],
            'syarat_ketentuan' => $data['syarat_ketentuan'] ?? null,
            'aktif' => $data['aktif'] ?? true,
        ]);
    }

    /**
     * Perbarui data program keringanan.
     */
    public function perbaruiProgram(KeringananSpp $program, array $data): KeringananSpp
    {
        $program->update([
            'nama_program' => $data['nama_program'],
            'tipe_potongan' => $data['tipe_potongan'],
            'nilai_potongan' => $data['nilai_potongan'],
            'syarat_ketentuan' => $data['syarat_ketentuan'] ?? null,
            'aktif' => $data['aktif'] ?? true,
        ]);

        return $program;
    }

    /**
     * Hapus program keringanan (hanya jika belum memiliki relasi penerima).
     */
    public function hapusProgram(KeringananSpp $program): void
    {
        if ($program->penerima()->exists()) {
            throw new \Exception('Program tidak dapat dihapus karena masih terhubung dengan data penetapan siswa.');
        }

        $program->delete();
    }

    /**
     * Menetapkan atau mencabut SK Keringanan ke siswa.
     */
    public function tetapkanSiswa(array $data): ?SiswaKeringananSpp
    {
        return DB::transaction(function () use ($data) {
            // Jika ID keringanan dikosongkan, artinya mencabut SK keringanan siswa pada tahun ajaran tsb
            if (empty($data['keringanan_spp_id'])) {
                SiswaKeringananSpp::where('siswa_id', $data['siswa_id'])
                    ->where('tahun_ajaran', $data['tahun_ajaran'])
                    ->delete();

                return null;
            }

            return SiswaKeringananSpp::updateOrCreate(
                [
                    'siswa_id' => $data['siswa_id'],
                    'tahun_ajaran' => $data['tahun_ajaran'],
                ],
                [
                    'keringanan_spp_id' => $data['keringanan_spp_id'],
                    'nomor_sk' => $data['nomor_sk'] ?? null,
                    'aktif' => $data['aktif'] ?? true,
                ]
            );
        });
    }
}
