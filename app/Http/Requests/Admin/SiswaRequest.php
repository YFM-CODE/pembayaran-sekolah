<?php

namespace App\Http\Requests\Admin;

use App\Models\Siswa;
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
        $siswa = $this->route('siswa');
        if (! $siswa instanceof Siswa) {
            $siswa = Siswa::find($siswa);
        }

        $siswaId = $siswa?->id;
        $userId = $siswa?->user_id;

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
            'status_siswa' => ['required', Rule::in(['aktif', 'lulus', 'pindah', 'keluar'])],
            'jenis_kelamin' => ['nullable', Rule::in(['L', 'P'])],
            'no_telepon' => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string'],

            // Field Akun Pengguna
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => [
                'nullable',
                'string',
                'min:6',
            ],
        ];
    }
}