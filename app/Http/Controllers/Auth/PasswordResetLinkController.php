<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Menampilkan halaman permintaan reset password (lupa password).
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Menangani permintaan pengiriman tautan reset password.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status == Password::RESET_LINK_SENT) {

            return back()->with(
                'status',
                'Tautan reset password telah dikirim ke email Anda. Silakan periksa kotak masuk Anda.'
            );
        }

        $pesan = $status == Password::INVALID_USER
            ? 'Email tersebut tidak terdaftar pada sistem kami.'
            : 'Gagal mengirim tautan reset password. Silakan coba lagi.';

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => $pesan]);
    }
}
