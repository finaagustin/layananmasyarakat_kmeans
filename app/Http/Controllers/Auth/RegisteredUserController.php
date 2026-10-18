<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Menampilkan halaman registrasi.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Menangani proses registrasi pengguna.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
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
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'max:16',
                'confirmed',
            ],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 50 karakter.',

            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 30 karakter.',
            'email.unique' => 'Email sudah terdaftar.',

            'password.required' => 'Password wajib diisi.',
            'password.string' => 'Password harus berupa teks.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.max' => 'Password maksimal 16 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Membuat akun masyarakat
        |--------------------------------------------------------------------------
        */

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'masyarakat',
        ]);

        event(new Registered($user));

        return redirect()
            ->route('login')
            ->with(
                'success',
                'Registrasi berhasil. Silakan login.'
            );
    }
}