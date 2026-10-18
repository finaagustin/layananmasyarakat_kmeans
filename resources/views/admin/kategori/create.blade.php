@extends('layouts.admin')

@section('title', 'Tambah Kategori Layanan')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h3 class="mb-1">Tambah Kategori Layanan</h3>

            <small class="text-muted">

                Tambahkan data kategori layanan baru.

            </small>

        </div>

        <a href="{{ route('admin.kategori-layanan.index') }}"
           class="btn btn-secondary">

            <i class="bi bi-arrow-left"></i>

            Kembali

        </a>

    </div>

    <div class="card shadow-sm">

        <div class="card-body">

            <form action="{{ route('admin.kategori-layanan.store') }}"
                  method="POST">

                @csrf

                <div class="mb-3">

                    <label class="form-label">

                        Nama Kategori

                    </label>

                    <input
                        type="text"
                        name="nama_kategori"
                        class="form-control @error('nama_kategori') is-invalid @enderror"
                        value="{{ old('nama_kategori') }}">

                    @error('nama_kategori')

                    <div class="invalid-feedback">

                        {{ $message }}

                    </div>

                    @enderror

                </div>

                <div class="mb-3">

                    <label class="form-label">

                        Bidang Terkait

                    </label>

                    <select
                        name="bidang_id"
                        class="form-select @error('bidang_id') is-invalid @enderror">

                        <option value="">-- Belum Ditentukan --</option>

                        @foreach($bidangs as $bidang)

                        <option
                            value="{{ $bidang->bidang_id }}"
                            {{ old('bidang_id') == $bidang->bidang_id ? 'selected' : '' }}>

                            {{ $bidang->nama_bidang }}

                        </option>

                        @endforeach

                    </select>

                    @error('bidang_id')

                    <div class="invalid-feedback">

                        {{ $message }}

                    </div>

                    @enderror

                    <small class="text-muted">

                        Pengaduan dengan kategori ini akan otomatis
                        diarahkan ke bidang yang dipilih di sini.

                    </small>

                </div>

                <div class="mb-4">

                    <label class="form-label">

                        Deskripsi

                    </label>

                    <textarea
                        name="deskripsi"
                        rows="4"
                        class="form-control">{{ old('deskripsi') }}</textarea>

                </div>

                <button class="btn btn-primary">

                    <i class="bi bi-save"></i>

                    Simpan

                </button>

                <a href="{{ route('admin.kategori-layanan.index') }}"
                   class="btn btn-outline-secondary">

                    Batal

                </a>

            </form>

        </div>

    </div>

</div>

@endsection