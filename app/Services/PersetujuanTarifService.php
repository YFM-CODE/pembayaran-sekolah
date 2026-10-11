<?php

namespace App\Services;

use App\Models\PersetujuanTarifKomite;
use App\Models\TarifSpp;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PersetujuanTarifService
{
    /**
     * Memproses persetujuan atau penolakan tarif beserta dokumen Berita Acara.
     */
    public function handle(
        TarifSpp $tarifSpp,
        User $ketuaKomite,
        array $data,
        ?UploadedFile $fileBa = null
    ): PersetujuanTarifKomite {
        return DB::transaction(function () use ($tarifSpp, $ketuaKomite, $data, $fileBa) {
            // Cari data persetujuan yang sudah ada untuk tarif ini
            $persetujuan = PersetujuanTarifKomite::where('tarif_spp_id', $tarifSpp->id)->first();

            $pathDokumen = $persetujuan?->dokumen_ba_pdf;

            // Jika ada file PDF baru yang diunggah
            if ($fileBa) {
                // Hapus berkas lama jika sebelumnya sudah ada
                if ($pathDokumen && Storage::disk('public')->exists($pathDokumen)) {
                    Storage::disk('public')->delete($pathDokumen);
                }

                $filename = 'BA_'.time().'_'.preg_replace('/[^A-Za-z0-9_\-]/', '_', $tarifSpp->tahun_ajaran).'_KLS_'.$tarifSpp->tingkat.'.pdf';
                $pathDokumen = $fileBa->storeAs('berita-acara-komite', $filename, 'public');
            }

            return PersetujuanTarifKomite::updateOrCreate(
                ['tarif_spp_id' => $tarifSpp->id],
                [
                    'ketua_komite_id' => $ketuaKomite->id,
                    'nomor_berita_acara' => $data['nomor_berita_acara'],
                    'tanggal_kesepakatan' => $data['tanggal_kesepakatan'],
                    'dokumen_ba_pdf' => $pathDokumen,
                    'status_persetujuan' => $data['status_persetujuan'],
                    'catatan_komite' => $data['catatan_komite'] ?? null,
                ]
            );
        });
    }
}
