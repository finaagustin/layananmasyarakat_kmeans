@extends('layouts.admin')

@section('title', 'Upload Dataset')

@section('content')

<div class="row justify-content-center">

    <div class="col-lg-9">

        <div class="card shadow-sm border-0">

            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="bi bi-upload text-success"></i>
                    Upload Dataset Analisis
                </h5>
            </div>

            <div class="card-body">

                {{-- =====================================================
                     NOTIFIKASI ERROR VALIDASI
                ====================================================== --}}
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">

                        <div class="fw-semibold mb-2">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Upload dataset gagal.
                        </div>

                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>

                        <button type="button"
                                class="btn-close"
                                data-bs-dismiss="alert"
                                aria-label="Close">
                        </button>

                    </div>
                @endif


                {{-- =====================================================
                     NOTIFIKASI ERROR PROSES
                ====================================================== --}}
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">

                        <i class="bi bi-exclamation-circle me-1"></i>
                        {{ session('error') }}

                        <button type="button"
                                class="btn-close"
                                data-bs-dismiss="alert"
                                aria-label="Close">
                        </button>

                    </div>
                @endif


                {{-- =====================================================
                     NOTIFIKASI BERHASIL
                ====================================================== --}}
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">

                        <i class="bi bi-check-circle me-1"></i>
                        {{ session('success') }}

                        <button type="button"
                                class="btn-close"
                                data-bs-dismiss="alert"
                                aria-label="Close">
                        </button>

                    </div>
                @endif


                {{-- =====================================================
                     FORM UPLOAD DATASET
                ====================================================== --}}
                <form action="{{ route('admin.dataset.store') }}"
                      method="POST"
                      enctype="multipart/form-data">

                    @csrf


                    {{-- =====================================================
                         PERIODE / LABEL DATASET
                    ====================================================== --}}
                    <div class="mb-4">

                        <label class="form-label fw-semibold">
                            Periode / Label Dataset
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            name="periode"
                            class="form-control @error('periode') is-invalid @enderror"
                            value="{{ old('periode') }}"
                            placeholder="Contoh: Juni 2026"
                            required>

                        @error('periode')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="form-text">
                            Beri label periode/bulan untuk file ini, mis.
                            "Juni 2026" atau "Dataset Pemakaman Juni 2026".
                        </div>

                    </div>


                    {{-- =====================================================
                         FILE DATASET
                    ====================================================== --}}
                    <div class="mb-4">

                        <label class="form-label fw-semibold">
                            <i class="bi bi-file-earmark-excel text-success"></i>
                            File Excel Dataset
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="file"
                            name="file_excel"
                            class="form-control @error('file_excel') is-invalid @enderror"
                            accept=".xlsx,.xls"
                            required>

                        @error('file_excel')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="form-text">
                            Data layanan hasil ekspor SIPETRUK/Front Office
                            untuk periode di atas.
                        </div>

                    </div>


                    {{-- =====================================================
                         INFORMASI PROSES ANALISIS
                    ====================================================== --}}
                    <div class="alert alert-info">

                        <i class="bi bi-info-circle me-1"></i>

                        Dataset ini akan ditambahkan ke dataset historis
                        yang sudah ada (tidak menggantikan dataset
                        sebelumnya). Setelah tersimpan, sistem otomatis
                        menjalankan analisis ulang atas
                        <strong>seluruh dataset historis</strong>
                        (dataset lama + dataset baru ini digabung menjadi
                        satu), melalui tahap preprocessing, pembobotan
                        TF-IDF, penentuan K optimal (Elbow Method),
                        pengelompokan K-Means, dan evaluasi Silhouette
                        Score.

                        <div class="mt-2">
                            <strong>Format file:</strong>
                            XLSX atau XLS.
                            Maksimal 10 MB.
                        </div>

                    </div>


                    {{-- =====================================================
                         TOMBOL
                    ====================================================== --}}
                    <div class="d-flex justify-content-between">

                        <a href="{{ route('admin.dataset.index') }}"
                           class="btn btn-secondary">

                            <i class="bi bi-arrow-left"></i>
                            Kembali

                        </a>

                        <button type="submit"
                                class="btn btn-success"
                                id="btnUploadDataset">

                            <i class="bi bi-cloud-upload"></i>
                            Upload &amp; Analisis Ulang

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

<script>

document.addEventListener('DOMContentLoaded', function () {

    const form = document.querySelector('form[action="{{ route("admin.dataset.store") }}"]');
    const button = document.getElementById('btnUploadDataset');

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

                Mengunggah &amp; Menganalisis Ulang...
            `;

        }, 10);

    });

});

</script>

@endsection