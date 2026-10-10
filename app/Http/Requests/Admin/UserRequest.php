<?php

namespace App\Http\Requests\Admin;

use App\Enums\PeranPengguna;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id ?? $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'role' => ['required', new Enum(PeranPengguna::class)],
            'password' => $this->isMethod('POST')
                ? ['required', 'string', Password::default()]
                : ['nullable', 'string', Password::default()],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}