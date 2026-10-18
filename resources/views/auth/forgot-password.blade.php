<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lupa Password - SI PETRUK</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-slate-100">

<div class="min-h-screen flex items-center justify-center p-4 sm:p-6">

    <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden">

        <div class="bg-gradient-to-br from-blue-700 via-blue-600 to-blue-500 px-8 pt-8 pb-10 relative overflow-hidden">

            <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full bg-white/5"></div>
            <div class="absolute bottom-0 -left-8 w-28 h-28 rounded-full bg-white/5"></div>

            <div class="relative z-10 flex items-center gap-3 mb-6">
                <div class="w-11 h-11 flex items-center justify-center">
                    <svg viewBox="0 0 48 48" class="w-11 h-11">
                        <path d="M24 2 L44 13 V35 L24 46 L4 35 V13 Z" fill="white" fill-opacity="0.12" stroke="white" stroke-width="2"/>
                        <path d="M16 24 L21 30 L32 17" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div>
                    <p class="text-white font-bold tracking-wide leading-tight">SI PETRUK</p>
                    <p class="text-blue-100 text-xs leading-tight">Sistem Pengaduan Masyarakat</p>
                </div>
            </div>

            <div class="relative z-10 w-14 h-14 rounded-full bg-white/15 flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z"/>
                </svg>
            </div>

            <h1 class="relative z-10 text-2xl font-bold text-white leading-snug">
                Lupa Password?
            </h1>
            <p class="relative z-10 text-blue-100 text-sm leading-relaxed mt-1">
                Masukkan email akun Anda. Kami akan mengirimkan tautan untuk membuat password baru.
            </p>

        </div>

        <div class="p-8">

            @if (session('status'))
                <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                    <ul class="list-disc pl-5 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-1.5">Email</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0a2.25 2.25 0 0 0-2.25-2.25h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>
                            </svg>
                        </span>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="Masukkan email Anda"
                            autofocus
                            class="w-full pl-10 pr-4 py-2.5 rounded-lg bg-white border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition text-sm">
                    </div>
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="w-full mt-2 flex items-center justify-center gap-2 bg-blue-600 text-white py-2.5 rounded-lg font-medium hover:bg-blue-700 transition duration-200 shadow-md shadow-blue-600/20">
                    Kirim Tautan Reset Password
                </button>
            </form>

            <p class="text-center text-sm text-gray-500 mt-6">
                Sudah ingat password Anda?
                <a href="{{ route('login') }}" class="text-blue-600 font-semibold hover:underline">Kembali ke Login</a>
            </p>

        </div>

    </div>

</div>

</body>
</html>
