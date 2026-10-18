@extends('layouts.user')

@section('title', 'Buat Pengaduan')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm border-0">

        <div class="card-header bg-primary text-white">

            <h5 class="mb-0">
                <i class="bi bi-plus-circle-fill me-2"></i>
                Form Pengaduan Masyarakat
            </h5>

        </div>

        <div class="card-body">

            <form action="{{ route('user.pengaduan.store') }}"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf

                <div class="mb-3">

                    <label class="form-label">
                        Kategori Layanan
                    </label>

                    <select
                        name="kategori_layanan_id"
                        class="form-select @error('kategori_layanan_id') is-invalid @enderror">

                        <option value="">
                            -- Pilih Kategori --
                        </option>

                        @foreach($kategoriLayanans as $kategori)

                            <option
                                value="{{ $kategori->kategori_layanan_id }}"
                                {{ old('kategori_layanan_id')==$kategori->kategori_layanan_id ? 'selected':'' }}>

                                {{ $kategori->nama_kategori }}

                            </option>

                        @endforeach

                    </select>

                    @error('kategori_layanan_id')

                        <div class="invalid-feedback">

                            {{ $message }}

                        </div>

                    @enderror

                </div>

                <div class="mb-3">

                    <label class="form-label">

                        Judul Pengaduan

                    </label>

                    <input
                        type="text"
                        name="judul"
                        class="form-control @error('judul') is-invalid @enderror"
                        value="{{ old('judul') }}">

                    @error('judul')

                        <div class="invalid-feedback">

                            {{ $message }}

                        </div>

                    @enderror

                </div>

                <div class="mb-3">

                    <label class="form-label">

                        Isi Pengaduan

                    </label>

                    <textarea
                        rows="6"
                        name="isi_pengaduan"
                        class="form-control @error('isi_pengaduan') is-invalid @enderror">{{ old('isi_pengaduan') }}</textarea>

                    @error('isi_pengaduan')

                        <div class="invalid-feedback">

                            {{ $message }}

                        </div>

                    @enderror

                </div>

                <div class="mb-4">

                    <label class="form-label">

                        Lampiran

                    </label>

                    <input
                        type="file"
                        name="lampiran"
                        class="form-control @error('lampiran') is-invalid @enderror">

                    <small class="text-muted">

                        Format:
                        JPG,
                        JPEG,
                        PNG,
                        PDF
                        (Maksimal 2 MB)

                    </small>

                    @error('lampiran')

                        <div class="invalid-feedback">

                            {{ $message }}

                        </div>

                    @enderror

                </div>

                <div class="d-flex justify-content-end gap-2">

                    <a
                        href="{{ route('user.pengaduan.index') }}"
                        class="btn btn-secondary">

                        Kembali

                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary">

                        <i class="bi bi-send-fill me-1"></i>

                        Kirim Pengaduan

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection