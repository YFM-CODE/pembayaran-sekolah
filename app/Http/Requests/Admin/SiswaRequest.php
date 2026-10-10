<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $siswaId = $this->route('siswa')?->id ?? $this->route('siswa');

        return [
            'nis' => [
                'required',
                'string',
                'max:20',
                Rule::unique('siswa', 'nis')->ignore($siswaId),
            ],
            'nisn' => [
                'required',
                'string',
                'max:20',
                Rule::unique('siswa', 'nisn')->ignore($siswaId),
            ],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'status' => ['required', Rule::in(['aktif', 'lulus', 'pindah', 'keluar'])],
            'jenis_kelamin' => ['nullable', Rule::in(['L', 'P'])],
            'no_telepon' => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string'],
            'user_id' => [
                'nullable',
                'exists:users,id',
                Rule::unique('siswa', 'user_id')->ignore($siswaId),
            ],
        ];
    }
}