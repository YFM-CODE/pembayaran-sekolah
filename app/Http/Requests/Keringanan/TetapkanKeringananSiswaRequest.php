<?php

namespace App\Http\Requests\Keringanan;

use Illuminate\Foundation\Http\FormRequest;

class TetapkanKeringananSiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'siswa_id'          => ['required', 'exists:siswa,id'],
            'keringanan_spp_id' => ['nullable', 'exists:keringanan_spp,id'],
            'tahun_ajaran'      => ['required', 'string', 'max:9'],
            'nomor_sk'          => ['nullable', 'string', 'max:100'],
            'aktif'             => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'siswa_id.required'     => 'Data siswa wajib dipilih.',
            'tahun_ajaran.required' => 'Tahun ajaran wajib ditentukan.',
        ];
    }
}