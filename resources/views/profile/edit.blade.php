@extends('layouts.user')

@section('title', 'Profil Saya')

@section('content')

@php
    $bulanIndo = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    $tanggalBergabung = $user->created_at
        ? $user->created_at->day . ' ' . $bulanIndo[$user->created_at->month] . ' ' . $user->created_at->year
        : '-';
@endphp

<div class="row justify-content-center">

    <div class="col-lg-8 col-xl-7">

        <div class="card border-0 shadow-sm overflow-hidden">

            {{-- ================= COVER + AVATAR ================= --}}
            <div style="height:100px;background:linear-gradient(135deg,#1e3a5f 0%,#2563eb 100%);"></div>

            <div class="text-center px-4">

                <div
                    class="mx-auto d-flex align-items-center justify-content-center rounded-circle bg-white"
                    style="width:100px;height:100px;margin-top:-50px;border:4px solid #fff;box-shadow:0 4px 12px rgba(0,0,0,.08);"
                >
                    <div
                        class="d-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10"
                        style="width:100%;height:100%;"
                    >
                        <i class="bi bi-person-fill fs-1 text-primary"></i>
                    </div>
                </div>

                <h5 class="fw-bold mt-3 mb-1">{{ $user->name }}</h5>

                <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis px-3 py-1 text-capitalize mb-3">
                    {{ $user->role }}
                </span>

            </div>

            {{-- ================= META INFO ================= --}}
            <div class="px-4 pb-3">

                <div class="row g-3 text-start border-top pt-3">

                    <div class="col-12 d-flex justify-content-between align-items-center">
                        <span class="text-muted small">
                            <i class="bi bi-calendar3 me-1"></i>
                            Bergabung sejak
                        </span>
                        <span class="fw-semibold small">{{ $tanggalBergabung }}</span>
                    </div>

                </div>

            </div>

            <hr class="m-0">

            {{-- ================= FORM EDIT PROFIL ================= --}}
            <div class="card-body p-4">

                <div class="d-flex align-items-start gap-3 mb-4">

                    <div
                        class="d-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 flex-shrink-0"
                        style="width:46px;height:46px;"
                    >
                        <i class="bi bi-person-lines-fill text-primary fs-5"></i>
                    </div>

                    <div>
                        <h5 class="fw-bold mb-1">Informasi Profil</h5>
                        <p class="text-muted small mb-0">
                            Perbarui nama dan alamat email akun Anda.
                        </p>
                    </div>

                </div>

                <form method="post" action="{{ route('profile.update') }}">

                    @csrf
                    @method('patch')

                    <div class="mb-3">

                        <label for="name" class="form-label small fw-semibold text-secondary">
                            Nama Lengkap
                        </label>

                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-person text-muted"></i>
                            </span>
                            <input
                                id="name"
                                name="name"
                                type="text"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $user->name) }}"
                                required
                                autofocus
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>

                    <div class="mb-4">

                        <label for="email" class="form-label small fw-semibold text-secondary">
                            Email
                        </label>

                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-envelope text-muted"></i>
                            </span>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $user->email) }}"
                                required
                            >
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-circle me-1"></i>
                            Simpan Perubahan
                        </button>
                    </div>

                </form>

                <hr class="my-4">

                {{-- ================= FORM GANTI PASSWORD ================= --}}
                <div class="d-flex align-items-start gap-3 mb-4">

                    <div
                        class="d-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 flex-shrink-0"
                        style="width:46px;height:46px;"
                    >
                        <i class="bi bi-shield-lock-fill text-primary fs-5"></i>
                    </div>

                    <div>
                        <h5 class="fw-bold mb-1">Ganti Password</h5>
                        <p class="text-muted small mb-0">
                            Pastikan password baru Anda cukup panjang dan mudah diingat hanya oleh Anda.
                        </p>
                    </div>

                </div>

                <form method="post" action="{{ route('password.update') }}">

                    @csrf
                    @method('put')

                    <div class="mb-3">

                        <label for="current_password" class="form-label small fw-semibold text-secondary">
                            Password Saat Ini
                        </label>

                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-lock text-muted"></i>
                            </span>
                            <input
                                id="current_password"
                                name="current_password"
                                type="password"
                                class="form-control @error('current_password') is-invalid @enderror"
                                autocomplete="current-password"
                            >
                            <button
                                type="button"
                                class="btn btn-outline-secondary toggle-password"
                                data-target="current_password"
                                tabindex="-1"
                                aria-label="Tampilkan/Sembunyikan password"
                            >
                                <i class="bi bi-eye"></i>
                            </button>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>

                    <div class="mb-3">

                        <label for="password" class="form-label small fw-semibold text-secondary">
                            Password Baru
                        </label>

                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-key text-muted"></i>
                            </span>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                class="form-control @error('password') is-invalid @enderror"
                                autocomplete="new-password"
                            >
                            <button
                                type="button"
                                class="btn btn-outline-secondary toggle-password"
                                data-target="password"
                                tabindex="-1"
                                aria-label="Tampilkan/Sembunyikan password"
                            >
                                <i class="bi bi-eye"></i>
                            </button>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>

                    <div class="mb-4">

                        <label for="password_confirmation" class="form-label small fw-semibold text-secondary">
                            Konfirmasi Password Baru
                        </label>

                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-key-fill text-muted"></i>
                            </span>
                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                class="form-control"
                                autocomplete="new-password"
                            >
                            <button
                                type="button"
                                class="btn btn-outline-secondary toggle-password"
                                data-target="password_confirmation"
                                tabindex="-1"
                                aria-label="Tampilkan/Sembunyikan password"
                            >
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>

                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-circle me-1"></i>
                            Simpan Password
                        </button>
                    </div>

                </form>

                <hr class="my-4">

                {{-- ================= HAPUS AKUN ================= --}}
                <div class="d-flex align-items-start gap-3 mb-3">

                    <div
                        class="d-flex align-items-center justify-content-center rounded-3 bg-danger bg-opacity-10 flex-shrink-0"
                        style="width:46px;height:46px;"
                    >
                        <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                    </div>

                    <div>
                        <h5 class="fw-bold text-danger mb-1">Hapus Akun</h5>
                        <p class="text-muted small mb-0">
                            Setelah akun dihapus, seluruh data dan riwayat pengaduan Anda akan hilang
                            secara permanen. Pastikan Anda sudah menyimpan data penting sebelum melanjutkan.
                        </p>
                    </div>

                </div>

                <button
                    type="button"
                    class="btn btn-danger"
                    data-bs-toggle="modal"
                    data-bs-target="#confirmDeleteModal"
                >
                    <i class="bi bi-trash-fill me-1"></i>
                    Hapus Akun
                </button>

            </div>

        </div>

    </div>

</div>

{{-- ================= MODAL KONFIRMASI HAPUS AKUN ================= --}}
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;">

            <form method="post" action="{{ route('profile.destroy') }}">

                @csrf
                @method('delete')

                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Konfirmasi Hapus Akun</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <p class="text-muted">
                        Tindakan ini tidak dapat dibatalkan. Masukkan password Anda untuk mengonfirmasi
                        penghapusan akun.
                    </p>

                    <label for="delete_password" class="form-label small fw-semibold text-secondary">
                        Password
                    </label>

                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <i class="bi bi-lock text-muted"></i>
                        </span>
                        <input
                            id="delete_password"
                            name="password"
                            type="password"
                            class="form-control @error('password', 'userDeletion') is-invalid @enderror"
                            placeholder="Masukkan password Anda"
                        >
                        <button
                            type="button"
                            class="btn btn-outline-secondary toggle-password"
                            data-target="delete_password"
                            tabindex="-1"
                            aria-label="Tampilkan/Sembunyikan password"
                        >
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>

                    @error('password', 'userDeletion')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror

                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash-fill me-1"></i>
                        Hapus Akun Saya
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

{{-- ================= TOGGLE SHOW/HIDE PASSWORD ================= --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {

        document.querySelectorAll('.toggle-password').forEach(function (btn) {

            btn.addEventListener('click', function () {

                var input = document.getElementById(btn.dataset.target);

                if (!input) {
                    return;
                }

                var icon = btn.querySelector('i');

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }

            });

        });

    });
</script>

@if ($errors->userDeletion->isNotEmpty())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('confirmDeleteModal');
            var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        });
    </script>
@endif

@endsection