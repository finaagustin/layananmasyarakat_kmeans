<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Menentukan apakah request diizinkan.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi login.
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'email',
                'max:30',
            ],

            'password' => [
                'required',
                'string',
                'max:16',
            ],
        ];
    }

    /**
     * Pesan validasi login.
     */
    public function messages(): array
    {
        return [
            'email.required' =>
                'Email wajib diisi.',

            'email.string' =>
                'Email harus berupa teks.',

            'email.email' =>
                'Format email tidak valid.',

            'email.max' =>
                'Email maksimal 30 karakter.',

            'password.required' =>
                'Password wajib diisi.',

            'password.string' =>
                'Password harus berupa teks.',

            'password.max' =>
                'Password maksimal 16 karakter.',
        ];
    }

    /**
     * Melakukan autentikasi berdasarkan
     * email dan password.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        /*
        |--------------------------------------------------------------------------
        | Cari pengguna berdasarkan email
        |--------------------------------------------------------------------------
        */

        $user = User::where(
            'email',
            $this->input('email')
        )->first();

        /*
        |--------------------------------------------------------------------------
        | Validasi password plaintext
        |--------------------------------------------------------------------------
        */

        if (
            !$user ||
            $user->password !== $this->input('password')
        ) {

            RateLimiter::hit(
                $this->throttleKey()
            );

            throw ValidationException::withMessages([
                'email' =>
                    'Email atau password yang dimasukkan salah.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Login pengguna
        |--------------------------------------------------------------------------
        */

        Auth::login($user);

        RateLimiter::clear(
            $this->throttleKey()
        );
    }

    /**
     * Membatasi percobaan login maksimal
     * lima kali.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts(
            $this->throttleKey(),
            5
        )) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn(
            $this->throttleKey()
        );

        throw ValidationException::withMessages([
            'email' =>
                'Terlalu banyak percobaan login. Silakan coba kembali dalam '
                . $seconds
                . ' detik.',
        ]);
    }

    /**
     * Membuat key rate limiter berdasarkan
     * email dan alamat IP.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(
            Str::lower(
                $this->string('email')
            )
            . '|'
            . $this->ip()
        );
    }
}