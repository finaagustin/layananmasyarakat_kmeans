<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Menampilkan halaman login.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Menangani proses login.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Autentikasi
        |--------------------------------------------------------------------------
        */

        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Redirect Berdasarkan Role
        |--------------------------------------------------------------------------
        */

        return match ($user->role) {

            'admin' => redirect()
                ->route('admin.dashboard')
                ->with(
                    'success',
                    'Login berhasil! Selamat datang Admin.'
                ),

            'masyarakat' => redirect()
                ->route('user.dashboard')
                ->with(
                    'success',
                    'Login berhasil! Selamat datang.'
                ),

            default => $this->logoutInvalidRole($request),
        };
    }

    /**
     * Logout pengguna.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()
            ->route('login');
    }

    /**
     * Logout apabila role pengguna tidak dikenali.
     */
    private function logoutInvalidRole(
        Request $request
    ): RedirectResponse {

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with(
                'error',
                'Role pengguna tidak valid.'
            );
    }
}