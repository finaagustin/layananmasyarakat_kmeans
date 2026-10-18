<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Menampilkan halaman reset password.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Menangani permintaan penyimpanan password baru.
     *
     * Catatan: password pada aplikasi ini disimpan dalam bentuk
     * plaintext (bukan hashed), sesuai desain sistem yang berlaku
     * untuk seluruh alur login/registrasi. Oleh karena itu proses
     * reset password di sini juga tidak melakukan Hash::make(),
     * agar tetap konsisten dan pengguna dapat login kembali.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:16',
                'confirmed',
            ],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',

            'password.required' => 'Password wajib diisi.',
            'password.string' => 'Password harus berupa teks.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.max' => 'Password maksimal 16 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password' => $request->password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status == Password::PASSWORD_RESET) {

            return redirect()
                ->route('login')
                ->with(
                    'success',
                    'Password berhasil diperbarui. Silakan login dengan password baru Anda.'
                );
        }

        $pesan = match ($status) {
            Password::INVALID_USER =>
                'Email tidak terdaftar pada sistem kami.',

            Password::INVALID_TOKEN =>
                'Tautan reset password tidak valid atau sudah kedaluwarsa. Silakan ajukan permintaan baru.',

            default =>
                'Gagal memperbarui password. Silakan coba lagi.',
        };

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => $pesan]);
    }
}
