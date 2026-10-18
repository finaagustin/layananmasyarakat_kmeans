<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - SI PETRUK</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-slate-100">

<div class="min-h-screen flex items-center justify-center p-4 sm:p-6">

    <div class="w-full max-w-5xl bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col lg:flex-row">

        {{-- ============ PANEL KIRI (branding) ============ --}}
        <div class="hidden lg:flex lg:w-1/2 relative bg-gradient-to-br from-blue-700 via-blue-600 to-blue-500 p-10 flex-col justify-between overflow-hidden">

            {{-- dekorasi lingkaran --}}
            <div class="absolute -top-16 -right-16 w-64 h-64 rounded-full bg-white/5"></div>
            <div class="absolute bottom-24 -left-10 w-40 h-40 rounded-full bg-white/5"></div>

            <div class="relative z-10">
                {{-- Logo --}}
                <div class="flex items-center gap-3 mb-10">
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

                <h1 class="text-3xl font-bold text-white leading-snug mb-3">
                    Selamat Datang<br>Kembali!
                </h1>
                <p class="text-blue-100 text-sm leading-relaxed max-w-sm">
                    Silakan masuk untuk melanjutkan dan mengelola layanan pengaduan masyarakat dengan mudah, cepat, dan transparan.
                </p>
                <div class="w-10 h-1 bg-white/60 rounded-full mt-5"></div>
            </div>

            {{-- Ilustrasi kota (SVG orisinal) --}}
            <div class="relative z-10 -mx-2 my-4">
                <svg viewBox="0 0 480 220" class="w-full h-auto">
                    <ellipse cx="120" cy="40" rx="26" ry="9" fill="white" opacity="0.15"/>
                    <ellipse cx="360" cy="25" rx="20" ry="7" fill="white" opacity="0.12"/>

                    <rect x="20" y="120" width="34" height="80" fill="white" opacity="0.9"/>
                    <rect x="60" y="95" width="30" height="105" fill="white" opacity="0.8"/>
                    <rect x="96" y="130" width="26" height="70" fill="white" opacity="0.85"/>
                    <rect x="128" y="70" width="18" height="130" fill="white" opacity="0.95"/>
                    <rect x="150" y="110" width="28" height="90" fill="white" opacity="0.8"/>

                    <g fill="#2563eb" opacity="0.35">
                        <rect x="26" y="130" width="6" height="8"/><rect x="38" y="130" width="6" height="8"/>
                        <rect x="26" y="150" width="6" height="8"/><rect x="38" y="150" width="6" height="8"/>
                        <rect x="66" y="105" width="6" height="8"/><rect x="78" y="105" width="6" height="8"/>
                        <rect x="66" y="125" width="6" height="8"/><rect x="78" y="125" width="6" height="8"/>
                        <rect x="66" y="145" width="6" height="8"/><rect x="78" y="145" width="6" height="8"/>
                        <rect x="132" y="80" width="5" height="7"/><rect x="132" y="100" width="5" height="7"/>
                        <rect x="132" y="120" width="5" height="7"/><rect x="132" y="140" width="5" height="7"/>
                    </g>

                    <path d="M240 200 V60 M240 60 C260 60 260 90 240 90 C220 90 220 60 240 60 Z" fill="none" stroke="white" stroke-width="3" opacity="0.9"/>
                    <path d="M180 145 Q240 60 300 145" fill="none" stroke="white" stroke-width="3" opacity="0.9"/>
                    <path d="M170 200 L200 145 M195 200 L215 145 M220 200 L232 145 M260 200 L248 145 M285 200 L265 145 M310 200 L280 145" stroke="white" stroke-width="1.5" opacity="0.6"/>
                    <rect x="160" y="197" width="160" height="6" fill="white" opacity="0.9"/>

                    <rect x="330" y="140" width="24" height="60" fill="white" opacity="0.85"/>
                    <rect x="358" y="110" width="20" height="90" fill="white" opacity="0.9"/>
                    <rect x="382" y="150" width="26" height="50" fill="white" opacity="0.8"/>
                    <rect x="412" y="125" width="18" height="75" fill="white" opacity="0.85"/>
                    <rect x="434" y="160" width="24" height="40" fill="white" opacity="0.8"/>

                    <g opacity="0.8" fill="#1d4ed8">
                        <ellipse cx="30" cy="205" rx="14" ry="8"/>
                        <ellipse cx="440" cy="208" rx="16" ry="8"/>
                    </g>
                    <rect x="0" y="203" width="480" height="17" fill="white" opacity="0.55"/>
                </svg>
            </div>

            {{-- Feature badges --}}
            <div class="relative z-10 grid grid-cols-3 gap-3 bg-white/10 backdrop-blur-sm rounded-2xl p-4">
                <div class="text-center">
                    <div class="w-9 h-9 mx-auto mb-2 rounded-full bg-white/15 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75 11.25 15 15 9.75M21 12c0 4.556-3.04 8.36-7.19 9.573-.526.153-1.093.153-1.62 0C7.04 20.36 4 16.556 4 12V6.75a.75.75 0 0 1 .585-.732 34 34 0 0 1 7.242-1.68.75.75 0 0 1 .346 0 34 34 0 0 1 7.242 1.68.75.75 0 0 1 .585.732V12Z"/>
                        </svg>
                    </div>
                    <p class="text-white text-xs font-semibold">Aman</p>
                    <p class="text-blue-100 text-[10px] leading-snug mt-0.5">Data Anda terlindungi dengan sistem keamanan berlapis.</p>
                </div>
                <div class="text-center">
                    <div class="w-9 h-9 mx-auto mb-2 rounded-full bg-white/15 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 13.5 10.5 3.75 8.25 10.5h7.5L9 20.25l2.25-6.75h-7.5Z"/>
                        </svg>
                    </div>
                    <p class="text-white text-xs font-semibold">Cepat</p>
                    <p class="text-blue-100 text-[10px] leading-snug mt-0.5">Proses pengaduan dan respons lebih cepat.</p>
                </div>
                <div class="text-center">
                    <div class="w-9 h-9 mx-auto mb-2 rounded-full bg-white/15 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 18.72a9.09 9.09 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.94 11.94 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6 6 0 0 1 6 18.719m12 0a5.97 5.97 0 0 0-.941-3.197m0 0A5.99 5.99 0 0 0 12 12.75a5.99 5.99 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.99 8.99 0 0 0 3.74.477m.94-3.197a5.97 5.97 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/>
                        </svg>
                    </div>
                    <p class="text-white text-xs font-semibold">Terpercaya</p>
                    <p class="text-blue-100 text-[10px] leading-snug mt-0.5">Layanan transparan dan dapat dipertanggungjawabkan.</p>
                </div>
            </div>
        </div>

        {{-- ============ PANEL KANAN (form) ============ --}}
        <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-10 lg:p-12">
            <div class="w-full max-w-sm">

                {{-- Logo (tampil hanya di mobile) --}}
                <div class="flex lg:hidden items-center gap-2 justify-center mb-6">
                    <svg viewBox="0 0 48 48" class="w-8 h-8">
                        <path d="M24 2 L44 13 V35 L24 46 L4 35 V13 Z" fill="#2563eb" fill-opacity="0.1" stroke="#2563eb" stroke-width="2"/>
                        <path d="M16 24 L21 30 L32 17" fill="none" stroke="#2563eb" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span class="font-bold text-blue-800">SI PETRUK</span>
                </div>

                <div class="w-14 h-14 rounded-full bg-blue-50 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
                    </svg>
                </div>

                <div class="text-center mb-7">
                    <h2 class="text-2xl font-bold text-gray-900">Masuk ke Akun</h2>
                    <p class="text-sm text-gray-500 mt-1">Masukkan email dan password Anda untuk masuk</p>
                </div>

                {{-- Validasi error --}}
                @if ($errors->any())
                    <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                        <ul class="list-disc pl-5 space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('success'))
                    <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" autocomplete="off">
                    @csrf

                    {{-- Anti autofill --}}
                    <input type="text" name="fake_user" style="display:none">
                    <input type="password" name="fake_pass" style="display:none">

                    {{-- Email --}}
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
                                autocomplete="off"
                                class="w-full pl-10 pr-4 py-2.5 rounded-lg bg-white border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition text-sm">
                        </div>
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-gray-700 text-sm font-medium">Password</label>
                            <a href="{{ route('password.request') }}" class="text-xs text-blue-600 font-medium hover:underline">
                                Lupa password?
                            </a>
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
                                </svg>
                            </span>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Masukkan password Anda"
                                autocomplete="new-password"
                                class="w-full pl-10 pr-10 py-2.5 rounded-lg bg-white border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition text-sm">
                            <button type="button" onclick="togglePassword()" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600">
                                <svg id="eyeIcon" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        class="w-full mt-2 flex items-center justify-center gap-2 bg-blue-600 text-white py-2.5 rounded-lg font-medium hover:bg-blue-700 transition duration-200 shadow-md shadow-blue-600/20">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25"/>
                        </svg>
                        Masuk
                    </button>
                </form>

                <p class="text-center text-sm text-gray-500 mt-6">
                    Belum punya akun?
                    <a href="{{ route('register') }}" class="text-blue-600 font-semibold hover:underline">Daftar sekarang</a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const password = document.getElementById("password");
    const icon = document.getElementById("eyeIcon");

    if (password.type === "password") {
        password.type = "text";
        icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18"/>`;
    } else {
        password.type = "password";
        icon.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
        `;
    }
}
</script>

</body>
</html>