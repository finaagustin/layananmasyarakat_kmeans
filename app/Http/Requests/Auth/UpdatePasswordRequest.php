<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
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
     * Aturan validasi ganti password.
     *
     * Catatan: password pada aplikasi ini disimpan
     * plaintext (bukan hash Bcrypt), sesuai desain
     * yang sudah dipakai pada LoginRequest &
     * RegisteredUserController. Karena itu validasi
     * "current_password" bawaan Laravel (yang memanggil
     * Hash::check()) TIDAK dipakai di sini, digantikan
     * pengecekan manual pada withValidator().
     */
    public function rules(): array
    {
        return [
            'current_password' => [
                'required',
                'string',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'max:16',
                'confirmed',
            ],
        ];
    }

    /**
     * Pesan validasi.
     */
    public function messages(): array
    {
        return [
            'current_password.required' =>
                'Password saat ini wajib diisi.',

            'password.required' =>
                'Password baru wajib diisi.',

            'password.min' =>
                'Password baru minimal 8 karakter.',

            'password.max' =>
                'Password baru maksimal 16 karakter.',

            'password.confirmed' =>
                'Konfirmasi password baru tidak cocok.',
        ];
    }

    /**
     * Validasi tambahan setelah aturan dasar lolos:
     * memastikan password saat ini yang diinput
     * cocok dengan password pengguna (plaintext).
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {

            if ($this->input('current_password') !== $this->user()->password) {

                $validator->errors()->add(
                    'current_password',
                    'Password saat ini yang Anda masukkan salah.'
                );
            }
        });
    }
}
