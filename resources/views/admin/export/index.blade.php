@extends('layouts.admin')

@section('title', 'Export Dataset')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h3 class="fw-bold mb-1">
            <i class="bi bi-file-earmark-arrow-down-fill text-primary"></i>
            Export Dataset
        </h3>

        <small class="text-muted">
            Ekspor data layanan masyarakat berdasarkan periode menjadi berkas
            Excel (.xlsx) yang dapat diunggah kembali sebagai dataset historis
            baru pada menu Dataset untuk dianalisis ulang.
        </small>
    </div>

</div>

@if(session('error'))

<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>

@endif

<div class="card shadow-sm border-0">

    <div class="card-header bg-white">
        <h5 class="mb-0">Pilih Periode Data Layanan</h5>
    </div>

    <div class="card-body">

        @if($periodeList->isEmpty())

            <div class="text-center py-5">
                <i class="bi bi-folder-x display-4 text-secondary"></i>
                <h5 class="mt-3">Belum Ada Data Layanan</h5>
                <small class="text-muted">
                    Data pengaduan masyarakat belum tersedia untuk diekspor.
                </small>
            </div>

        @else

            <form action="{{ route('admin.export.download') }}" method="GET">

                <div class="row g-3 align-items-end">

                    <div class="col-md-6">
                        <label class="form-label">Periode</label>

                        <select name="periode" class="form-select" required>
                            <option value="" disabled selected>
                                -- Pilih Periode --
                            </option>

                            @foreach($periodeList as $periode)
                                <option value="{{ $periode->periode_value }}">
                                    {{ $periode->periode_label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-download"></i>
                            Export ke Excel
                        </button>
                    </div>

                </div>

            </form>

        @endif

    </div>

</div>

@endsection
