<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KelasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $kelasId = $this->route('kela')?->id ?? $this->route('kelas')?->id ?? $this->route('kelas');

        return [
            'nama_kelas' => [
                'required',
                'string',
                'max:50',
                Rule::unique('kelas', 'nama_kelas')->ignore($kelasId),
            ],
            'tingkat' => ['required', 'string', Rule::in(['X', 'XI', 'XII'])],
            'jurusan' => ['required', 'string', 'max:50'],
        ];
    }
}
