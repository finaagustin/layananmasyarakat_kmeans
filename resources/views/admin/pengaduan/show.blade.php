@extends('layouts.admin')

@section('title', 'Detail Pengaduan')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h3 class="fw-bold mb-1">
            <i class="bi bi-eye-fill text-primary"></i>
            Detail Pengaduan
        </h3>

        <small class="text-muted">
            Informasi lengkap pengaduan masyarakat.
        </small>
    </div>

    <div>

        <a href="{{ route('admin.pengaduan.index') }}"
           class="btn btn-secondary">

            <i class="bi bi-arrow-left"></i>
            Kembali

        </a>

        <a href="{{ route(
                'admin.pengaduan.edit',
                $pengaduan->pengaduan_id
            ) }}"
           class="btn btn-warning">

            <i class="bi bi-pencil-square"></i>
            Kelola Pengaduan

        </a>

    </div>

</div>


<div class="row">

    {{-- =====================================================
         INFORMASI PENGADUAN
    ====================================================== --}}

    <div class="col-lg-8">

        <div class="card shadow-sm border-0 mb-4">

            <div class="card-header bg-primary text-white">

                <strong>Informasi Pengaduan</strong>

            </div>

            <div class="card-body">

                <table class="table table-borderless">

                    <tr>
                        <th width="220">Kode Pengaduan</th>

                        <td>
                            {{ $pengaduan->kode_pengaduan }}
                        </td>
                    </tr>

                    <tr>
                        <th>Tanggal Pengaduan</th>

                        <td>
                            {{ optional($pengaduan->tanggal_pengaduan)
                                ->format('d-m-Y H:i')
                                ?? '-'
                            }}
                        </td>
                    </tr>

                    <tr>
                        <th>Judul</th>

                        <td>
                            {{ $pengaduan->judul }}
                        </td>
                    </tr>

                    <tr>
                        <th>Kategori</th>

                        <td>
                            {{ optional($pengaduan->kategori)
                                ->nama_kategori
                                ?? '-'
                            }}
                        </td>
                    </tr>

                    <tr>
                        <th>Bidang</th>

                        <td>
                            {{ optional($pengaduan->bidang)
                                ->nama_bidang
                                ?? 'Belum ditentukan'
                            }}
                        </td>
                    </tr>

                    <tr>
                        <th>Status</th>

                        <td>

                            @switch($pengaduan->status)

                                @case('Diajukan')

                                    <span class="badge bg-warning text-dark">
                                        Diajukan
                                    </span>

                                    @break


                                @case('Diproses')

                                    <span class="badge bg-primary">
                                        Diproses
                                    </span>

                                    @break


                                @case('Selesai')

                                    <span class="badge bg-success">
                                        Selesai
                                    </span>

                                    @break


                                @default

                                    <span class="badge bg-secondary">
                                        {{ $pengaduan->status }}
                                    </span>

                            @endswitch

                        </td>
                    </tr>

                </table>


                <hr>


                {{-- Isi Pengaduan --}}

                <h6 class="fw-bold">
                    Isi Pengaduan
                </h6>

                <div class="border rounded p-3 bg-light">

                    {{ $pengaduan->isi_pengaduan }}

                </div>


                {{-- Lampiran --}}

                @if($pengaduan->lampiran)

                    <hr>

                    <h6 class="fw-bold">
                        Lampiran
                    </h6>

                    <a href="{{ asset('storage/' . $pengaduan->lampiran) }}"
                       class="btn btn-outline-primary btn-sm"
                       target="_blank">

                        <i class="bi bi-paperclip"></i>
                        Lihat Lampiran

                    </a>

                @endif

            </div>

        </div>


        {{-- =================================================
             HASIL ANALISIS
        ================================================== --}}

        <div class="card shadow-sm border-0 mb-4">

            <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">

                <strong>Hasil Analisis Pengaduan</strong>

                <form method="POST"
                      action="{{ route(
                          'admin.pengaduan.predict-ulang',
                          $pengaduan->pengaduan_id
                      ) }}"
                      class="mb-0">

                    @csrf

                    <button type="submit"
                            class="btn btn-light btn-sm">

                        <i class="bi bi-arrow-repeat"></i>
                        Prediksi Ulang Cluster

                    </button>

                </form>

            </div>

            <div class="card-body">

                @if(
                    $pengaduan->hasil_preprocessing ||
                    !is_null($pengaduan->cluster) ||
                    $pengaduan->nama_cluster
                )

                <table class="table table-borderless mb-0">

                    <tr>

                        <th width="220">
                            Hasil Preprocessing
                        </th>

                        <td>
                            {{ $pengaduan->hasil_preprocessing ?? '-' }}
                        </td>

                    </tr>

                    <tr>

                        <th>
                            Cluster
                        </th>

                        <td>
                            {{ !is_null($pengaduan->cluster)
                                ? $pengaduan->cluster
                                : '-'
                            }}
                        </td>

                    </tr>

                    <tr>

                        <th>
                            Nama Cluster
                        </th>

                        <td>
                            {{ $pengaduan->nama_cluster ?? '-' }}
                        </td>

                    </tr>

                </table>

                @else

                <p class="text-muted mb-0">
                    Belum ada hasil analisis cluster untuk pengaduan ini.
                    Kemungkinan model TF-IDF/K-Means belum dilatih saat
                    pengaduan ini dikirim. Tekan "Prediksi Ulang Cluster"
                    di atas untuk mencoba memprediksi ulang.
                </p>

                @endif

            </div>

        </div>

    </div>


    {{-- =====================================================
         BAGIAN KANAN
    ====================================================== --}}

    <div class="col-lg-4">

        {{-- Data Pelapor --}}

        <div class="card shadow-sm border-0 mb-4">

            <div class="card-header bg-success text-white">

                <strong>Data Pelapor</strong>

            </div>

            <div class="card-body">

                <table class="table table-borderless mb-0">

                    <tr>

                        <th>Nama</th>

                        <td>
                            {{ optional($pengaduan->user)->name ?? '-' }}
                        </td>

                    </tr>

                    <tr>

                        <th>Email</th>

                        <td>
                            {{ optional($pengaduan->user)->email ?? '-' }}
                        </td>

                    </tr>

                </table>

            </div>

        </div>


        {{-- Riwayat Status --}}

        <div class="card shadow-sm border-0">

            <div class="card-header bg-info text-white">

                <strong>Riwayat Status</strong>

            </div>

            <div class="card-body">

                @forelse($pengaduan->statusLogs as $log)

                    <div class="border-start border-4 border-primary ps-3 mb-4">

                        <strong>
                            {{ $log->status }}
                        </strong>

                        <br>

                        <small class="text-muted">

                            {{ optional($log->created_at)
                                ->format('d-m-Y H:i')
                                ?? '-'
                            }}

                        </small>


                        @if($log->keterangan)

                            <p class="mb-0 mt-2">

                                {{ $log->keterangan }}

                            </p>

                        @endif

                    </div>

                @empty

                    <div class="alert alert-warning mb-0">

                        Belum ada riwayat status.

                    </div>

                @endforelse

            </div>

        </div>

    </div>

</div>

@endsection