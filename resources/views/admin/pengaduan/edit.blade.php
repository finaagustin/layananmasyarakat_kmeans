@extends('layouts.admin')

@section('title', 'Kelola Pengaduan')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h3 class="fw-bold mb-1">
            <i class="bi bi-pencil-square text-warning"></i>
            Kelola Pengaduan
        </h3>

        <small class="text-muted">
            Tentukan bidang tujuan dan perbarui status pengaduan masyarakat.
        </small>
    </div>

    <a href="{{ route('admin.pengaduan.index') }}"
       class="btn btn-secondary">

        <i class="bi bi-arrow-left"></i>
        Kembali

    </a>

</div>


{{-- Error Validasi --}}
@if ($errors->any())

<div class="alert alert-danger">

    <strong>Terjadi kesalahan.</strong>

    <ul class="mb-0 mt-2">

        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach

    </ul>

</div>

@endif


{{-- Error Proses --}}
@if(session('error'))

<div class="alert alert-danger alert-dismissible fade show">

    <i class="bi bi-exclamation-triangle-fill me-2"></i>

    {{ session('error') }}

    <button type="button"
            class="btn-close"
            data-bs-dismiss="alert">
    </button>

</div>

@endif


<div class="row">

    {{-- =====================================================
         FORM PENGELOLAAN
    ====================================================== --}}

    <div class="col-lg-8">

        <div class="card shadow-sm border-0">

            <div class="card-header bg-warning">

                <strong>Form Pengelolaan Pengaduan</strong>

            </div>

            <div class="card-body">

                <form
                    action="{{ route(
                        'admin.pengaduan.update',
                        $pengaduan->pengaduan_id
                    ) }}"
                    method="POST">

                    @csrf
                    @method('PUT')


                    {{-- Kode Pengaduan --}}
                    <div class="mb-3">

                        <label class="form-label fw-semibold">
                            Kode Pengaduan
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="{{ $pengaduan->kode_pengaduan }}"
                            readonly>

                    </div>


                    {{-- Judul Pengaduan --}}
                    <div class="mb-3">

                        <label class="form-label fw-semibold">
                            Judul Pengaduan
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="{{ $pengaduan->judul }}"
                            readonly>

                    </div>


                    {{-- Bidang Tujuan --}}
                    <div class="mb-3">

                        <label class="form-label fw-semibold">

                            Bidang Tujuan

                        </label>

                        <select
                            name="bidang_id"
                            class="form-select @error('bidang_id') is-invalid @enderror">

                            <option value="">
                                -- Pilih Bidang Tujuan --
                            </option>

                            @foreach($bidangs as $bidang)

                                <option
                                    value="{{ $bidang->bidang_id }}"
                                    {{ (string) old(
                                        'bidang_id',
                                        $pengaduan->bidang_id
                                    ) === (string) $bidang->bidang_id
                                        ? 'selected'
                                        : ''
                                    }}>

                                    {{ $bidang->nama_bidang }}

                                </option>

                            @endforeach

                        </select>

                        @error('bidang_id')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>


                    {{-- Status Saat Ini --}}
                    <div class="mb-3">

                        <label class="form-label fw-semibold">
                            Status Saat Ini
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="{{ $pengaduan->status }}"
                            readonly>

                    </div>


                    {{-- Status Baru --}}
                    <div class="mb-3">

                        <label class="form-label fw-semibold">

                            Status Baru
                            <span class="text-danger">*</span>

                        </label>

                        <select
                            name="status"
                            class="form-select @error('status') is-invalid @enderror"
                            required>

                            <option value="">
                                -- Pilih Status --
                            </option>

                            <option
                                value="Diajukan"
                                {{ old(
                                    'status',
                                    $pengaduan->status
                                ) === 'Diajukan'
                                    ? 'selected'
                                    : ''
                                }}>

                                Diajukan

                            </option>

                            <option
                                value="Diproses"
                                {{ old(
                                    'status',
                                    $pengaduan->status
                                ) === 'Diproses'
                                    ? 'selected'
                                    : ''
                                }}>

                                Diproses

                            </option>

                            <option
                                value="Selesai"
                                {{ old(
                                    'status',
                                    $pengaduan->status
                                ) === 'Selesai'
                                    ? 'selected'
                                    : ''
                                }}>

                                Selesai

                            </option>

                        </select>

                        @error('status')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>


                    {{-- Keterangan --}}
                    <div class="mb-4">

                        <label class="form-label fw-semibold">
                            Keterangan
                        </label>

                        <textarea
                            name="keterangan"
                            rows="5"
                            maxlength="1000"
                            class="form-control @error('keterangan') is-invalid @enderror"
                            placeholder="Masukkan keterangan perubahan status (opsional)...">{{ old('keterangan') }}</textarea>

                        @error('keterangan')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                        <small class="text-muted">
                            Keterangan akan disimpan pada riwayat status
                            apabila status berubah.
                        </small>

                    </div>


                    {{-- Tombol --}}
                    <div class="text-end">

                        <button
                            type="submit"
                            class="btn btn-warning">

                            <i class="bi bi-check-circle"></i>
                            Simpan Perubahan

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>


    {{-- =====================================================
         INFORMASI PENGADUAN
    ====================================================== --}}

    <div class="col-lg-4">

        <div class="card shadow-sm border-0">

            <div class="card-header bg-primary text-white">

                <strong>Informasi Pengaduan</strong>

            </div>

            <div class="card-body">

                <table class="table table-borderless">

                    <tr>

                        <th>Pelapor</th>

                        <td>
                            {{ optional($pengaduan->user)->name ?? '-' }}
                        </td>

                    </tr>

                    <tr>

                        <th>Kategori</th>

                        <td>
                            {{ optional($pengaduan->kategori)->nama_kategori ?? '-' }}
                        </td>

                    </tr>

                    <tr>

                        <th>Bidang</th>

                        <td>
                            {{ optional($pengaduan->bidang)->nama_bidang ?? 'Belum ditentukan' }}
                        </td>

                    </tr>

                    <tr>

                        <th>Status</th>

                        <td>
                            {{ $pengaduan->status }}
                        </td>

                    </tr>

                    <tr>

                        <th>Tanggal</th>

                        <td>
                            {{ optional($pengaduan->tanggal_pengaduan)->format('d-m-Y H:i') ?? '-' }}
                        </td>

                    </tr>

                </table>

            </div>

        </div>

    </div>

</div>

@endsection