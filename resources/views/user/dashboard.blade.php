@extends('layouts.user')

@section('title', 'Dashboard')

@section('content')

<div class="row g-4">

    <div class="col-md-4">

        <div class="card shadow-sm border-0">

            <div class="card-body">

                <h6 class="text-muted mb-2">

                    Total Pengaduan

                </h6>

                <h2 class="fw-bold">

                    {{ $totalPengaduan }}

                </h2>

            </div>

        </div>

    </div>

    <div class="col-md-4">

        <div class="card shadow-sm border-0">

            <div class="card-body">

                <h6 class="text-muted mb-2">

                    Diproses

                </h6>

                <h2 class="fw-bold text-warning">

                    {{ $pengaduanDiproses }}

                </h2>

            </div>

        </div>

    </div>

    <div class="col-md-4">

        <div class="card shadow-sm border-0">

            <div class="card-body">

                <h6 class="text-muted mb-2">

                    Selesai

                </h6>

                <h2 class="fw-bold text-success">

                    {{ $pengaduanSelesai }}

                </h2>

            </div>

        </div>

    </div>

</div>

<div class="card mt-4 shadow-sm border-0">

    <div class="card-body">

        <h5 class="card-title">

            Selamat Datang

        </h5>

        <p class="mb-0">

            Halo,
            <strong>{{ auth()->user()->name }}</strong>.
            Selamat datang di Sistem DICIPTABINTAR Kota Bandung.
            Melalui halaman ini Anda dapat mengajukan layanan atau pengaduan, melihat riwayat pengajuan, serta memantau perkembangan status layanan yang sedang diproses.

        </p>

    </div>

</div>

<div class="card mt-4 shadow-sm border-0">

    <div class="card-body">

        <h5 class="card-title mb-1">

            <i class="bi bi-tags-fill text-primary"></i>
            Kriteria Kategori Layanan

        </h5>

        <p class="text-muted small mb-3">

            Sebelum mengajukan pengaduan, pilih kategori layanan
            yang paling sesuai dengan kebutuhan Anda. Berikut
            penjelasan singkat tiap kategori:

        </p>

        <div class="row g-3">

            @forelse($kategoriLayanans as $kategori)

            <div class="col-md-6">

                <div class="border rounded p-3 h-100">

                    <strong>{{ $kategori->nama_kategori }}</strong>

                    <p class="small text-muted mb-0 mt-1">
                        {{ $kategori->deskripsi ?? 'Belum ada deskripsi.' }}
                    </p>

                </div>

            </div>

            @empty

            <div class="col-12 text-center text-muted py-3">
                Belum ada data kategori layanan.
            </div>

            @endforelse

        </div>

    </div>

</div>

@endsection