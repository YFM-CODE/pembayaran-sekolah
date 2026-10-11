<?php

namespace App\Http\Requests\Keringanan;

use Illuminate\Foundation\Http\FormRequest;

class SimpanProgramKeringananRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'nama_program'     => ['required', 'string', 'max:100'],
            'tipe_potongan'    => ['required', 'in:nominal,persentase'],
            'nilai_potongan'   => ['required', 'numeric', 'min:1'],
            'syarat_ketentuan' => ['nullable', 'string', 'max:1000'],
            'aktif'            => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_program.required'   => 'Nama program beasiswa/keringanan wajib diisi.',
            'tipe_potongan.required'  => 'Tipe potongan wajib dipilih (nominal atau persentase).',
            'nilai_potongan.required' => 'Nilai besaran potongan wajib diisi minimal 1.',
        ];
    }
}