@extends('layouts.admin')

@section('title', 'Dataset')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h3 class="fw-bold mb-1">
            <i class="bi bi-file-earmark-excel-fill text-success"></i>
            Dataset
        </h3>

        <small class="text-muted">
            Kelola dataset layanan masyarakat yang digunakan untuk proses analisis K-Means.
        </small>
    </div>

    <a href="{{ route('admin.dataset.create') }}"
       class="btn btn-success">

        <i class="bi bi-upload"></i>
        Upload Dataset

    </a>

</div>


{{-- ========================================================= --}}
{{-- NOTIFIKASI BERHASIL --}}
{{-- ========================================================= --}}

@if(session('success'))

    <div class="alert alert-success alert-dismissible fade show">

        <i class="bi bi-check-circle-fill me-2"></i>

        {{ session('success') }}

        <button type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Close">
        </button>

    </div>

@endif


{{-- ========================================================= --}}
{{-- NOTIFIKASI ERROR --}}
{{-- ========================================================= --}}

@if(session('error'))

    <div class="alert alert-danger alert-dismissible fade show">

        <i class="bi bi-exclamation-triangle-fill me-2"></i>

        {{ session('error') }}

        <button type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Close">
        </button>

    </div>

@endif


{{-- ========================================================= --}}
{{-- PANEL ANALISIS --}}
{{-- ========================================================= --}}

@if($datasets->total() > 0)

<div class="card shadow-sm border-0 mb-4">

    <div class="card-body">

        <div class="d-flex justify-content-between align-items-center">

            <div>

                <h5 class="fw-bold mb-1">

                    <i class="bi bi-cpu-fill text-primary me-1"></i>

                    Analisis Dataset

                </h5>

                <small class="text-muted">

                    Jalankan preprocessing, TF-IDF, Elbow Method,
                    K-Means, dan Silhouette Score terhadap seluruh
                    dataset yang telah diunggah.

                </small>

            </div>


            <form
                action="{{ route('admin.dataset.analyze') }}"
                method="POST"
                id="formAnalisis">

                @csrf

                <button
                    type="submit"
                    class="btn btn-primary"
                    id="btnAnalisis"
                    onclick="return confirm('Seluruh dataset yang telah diunggah akan dianalisis. Lanjutkan?')">

                    <i class="bi bi-play-circle-fill me-1"></i>

                    Jalankan Analisis

                </button>

            </form>

        </div>

    </div>

</div>

@endif


{{-- ========================================================= --}}
{{-- DAFTAR DATASET --}}
{{-- ========================================================= --}}

<div class="card shadow-sm border-0">

    <div class="card-header bg-white">

        <div class="d-flex justify-content-between align-items-center">

            <h5 class="mb-0">
                Daftar Dataset
            </h5>

            <span class="badge bg-success fs-6">

                Total Dataset:
                {{ $datasets->total() }}

            </span>

        </div>

    </div>


    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead class="table-light text-center">

                    <tr>

                        <th width="60">
                            No
                        </th>

                        <th>
                            Periode
                        </th>

                        <th>
                            File Dataset
                        </th>

                        <th width="180">
                            Tanggal Upload
                        </th>

                        <th width="100">
                            Aksi
                        </th>

                    </tr>

                </thead>


                <tbody>

                @forelse($datasets as $dataset)

                    <tr>

                        {{-- Nomor --}}
                        <td class="text-center">

                            {{ $datasets->firstItem() + $loop->index }}

                        </td>


                        {{-- Periode --}}
                        <td>

                            <strong>
                                {{ $dataset->periode }}
                            </strong>

                        </td>


                        {{-- File Dataset --}}
                        <td>

                            <i class="bi bi-file-earmark-excel-fill text-success me-2"></i>

                            {{ $dataset->file_excel }}

                        </td>


                        {{-- Tanggal Upload --}}
                        <td class="text-center">

                            {{ optional($dataset->created_at)
                                ->format('d-m-Y H:i')
                                ?? '-'
                            }}

                        </td>


                        {{-- Aksi --}}
                        <td class="text-center">

                            <div class="d-flex justify-content-center align-items-center gap-2">

                                {{-- Lihat Hasil Analisis --}}
                                <a href="{{ route(
                                        'admin.analisis.index',
                                        $dataset->dataset_id
                                    ) }}"
                                class="btn btn-primary btn-sm d-inline-flex align-items-center justify-content-center"
                                style="width: 34px; height: 34px;"
                                title="Lihat Hasil Analisis">

                                    <i class="bi bi-bar-chart-fill"></i>

                                </a>

                                {{-- Hapus Dataset --}}
                                <form
                                    action="{{ route(
                                        'admin.dataset.destroy',
                                        $dataset->dataset_id
                                    ) }}"
                                    method="POST"
                                    class="m-0">

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="btn btn-danger btn-sm d-inline-flex align-items-center justify-content-center"
                                        style="width: 34px; height: 34px;"
                                        title="Hapus Dataset"
                                        onclick="return confirm('Dataset ini akan dihapus. Lanjutkan?')">

                                        <i class="bi bi-trash-fill"></i>

                                    </button>

                                </form>

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="5"
                            class="text-center py-5">

                            <i class="bi bi-file-earmark-excel display-1 text-success"></i>

                            <h4 class="mt-3">
                                Belum Ada Dataset
                            </h4>

                            <p class="text-muted">

                                Silakan upload dataset layanan masyarakat
                                terlebih dahulu.

                            </p>

                            <a href="{{ route('admin.dataset.create') }}"
                               class="btn btn-success">

                                <i class="bi bi-upload"></i>
                                Upload Dataset

                            </a>

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>


        {{-- Pagination --}}
        @if($datasets->hasPages())

            <div class="d-flex justify-content-end mt-3">

                {{ $datasets->links() }}

            </div>

        @endif

    </div>

</div>


{{-- ========================================================= --}}
{{-- LOADING SAAT ANALISIS --}}
{{-- ========================================================= --}}

<script>

document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('formAnalisis');
    const button = document.getElementById('btnAnalisis');

    if (!form || !button) {
        return;
    }

    form.addEventListener('submit', function () {

        setTimeout(function () {

            button.disabled = true;

            button.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2"
                      role="status"
                      aria-hidden="true">
                </span>

                Memproses Analisis...
            `;

        }, 10);

    });

});

</script>

@endsection