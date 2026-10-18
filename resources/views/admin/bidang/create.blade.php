@extends('layouts.admin')

@section('title', 'Tambah Bidang')

@section('content')

<div class="row justify-content-center">

    <div class="col-lg-8">

        <div class="card">

            <div class="card-header">

                <h5 class="mb-0">
                    <i class="bi bi-plus-circle text-primary"></i>
                    Tambah Bidang
                </h5>

            </div>

            <div class="card-body">

                <form action="{{ route('admin.bidang.store') }}" method="POST">

                    @csrf

                    <div class="mb-3">

                        <label class="form-label">

                            Nama Bidang

                        </label>

                        <input
                            type="text"
                            name="nama_bidang"
                            class="form-control @error('nama_bidang') is-invalid @enderror"
                            value="{{ old('nama_bidang') }}">

                        @error('nama_bidang')

                        <div class="invalid-feedback">

                            {{ $message }}

                        </div>

                        @enderror

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

                    <div class="d-flex justify-content-end gap-2">

                        <a href="{{ route('admin.bidang.index') }}"
                           class="btn btn-secondary">

                            Kembali

                        </a>

                        <button
                            type="submit"
                            class="btn btn-primary">

                            <i class="bi bi-save"></i>

                            Simpan

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

@endsection