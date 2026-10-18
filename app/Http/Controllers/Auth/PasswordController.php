<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;

class PasswordController extends Controller
{
    /**
     * Memperbarui password pengguna (termasuk masyarakat).
     *
     * Catatan: mengikuti desain aplikasi ini, password
     * disimpan plaintext (lihat LoginRequest::authenticate()
     * dan RegisteredUserController@store), sehingga di sini
     * password baru TIDAK di-hash agar tetap konsisten dan
     * bisa dipakai login setelah diganti.
     */
    public function update(UpdatePasswordRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $request->user()->update([
            'password' => $validated['password'],
        ]);

        return back()->with(
            'success',
            'Password berhasil diperbarui.'
        );
    }
}
