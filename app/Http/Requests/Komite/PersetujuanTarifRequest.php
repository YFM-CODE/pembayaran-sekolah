<?php

namespace App\Http\Requests\Komite;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PersetujuanTarifRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Pastikan hanya user yang terotentikasi (middleware role sudah menangani peran)
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $tarifSpp = $this->route('tarifSpp');
        $persetujuanEksis = $tarifSpp ? $tarifSpp->persetujuanKomite : null;

        return [
            'status_persetujuan' => ['required', Rule::in(['disetujui', 'ditolak'])],
            'nomor_berita_acara' => ['required', 'string', 'max:100'],
            'tanggal_kesepakatan' => ['required', 'date'],
            'catatan_komite' => ['nullable', 'string', 'max:1000'],
            'dokumen_ba_pdf' => [
                // Wajib unggah file jika status disetujui DAN belum ada berkas PDF tersimpan sebelumnya
                Rule::requiredIf(function () use ($persetujuanEksis) {
                    return $this->input('status_persetujuan') === 'disetujui' && empty($persetujuanEksis?->dokumen_ba_pdf);
                }),
                'nullable',
                'file',
                'mimes:pdf',
                'max:2048', // Batas maksimal 2MB
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status_persetujuan.required' => 'Keputusan persetujuan wajib ditentukan.',
            'status_persetujuan.in' => 'Pilihan keputusan harus berupa disetujui atau ditolak.',
            'nomor_berita_acara.required' => 'Nomor Berita Acara wajib diisi.',
            'tanggal_kesepakatan.required' => 'Tanggal kesepakatan musyawarah wajib diisi.',
            'dokumen_ba_pdf.required' => 'Berkas Berita Acara (format PDF) wajib dilampirkan saat menyetujui tarif.',
            'dokumen_ba_pdf.mimes' => 'Berkas Berita Acara harus berformat PDF.',
            'dokumen_ba_pdf.max' => 'Ukuran berkas Berita Acara maksimal 2MB.',
        ];
    }
}

