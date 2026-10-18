<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Menentukan apakah pengguna diizinkan
     * melakukan request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi pembaruan profil.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:50',
            ],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:30',

                Rule::unique(
                    'users',
                    'email'
                )->ignore(
                    $this->user()->user_id,
                    'user_id'
                ),
            ],
        ];
    }

    /**
     * Pesan validasi.
     */
    public function messages(): array
    {
        return [
            'name.required' =>
                'Nama wajib diisi.',

            'name.string' =>
                'Nama harus berupa teks.',

            'name.max' =>
                'Nama maksimal 50 karakter.',

            'email.required' =>
                'Email wajib diisi.',

            'email.string' =>
                'Email harus berupa teks.',

            'email.email' =>
                'Format email tidak valid.',

            'email.max' =>
                'Email maksimal 30 karakter.',

            'email.unique' =>
                'Email sudah digunakan oleh pengguna lain.',
        ];
    }
}