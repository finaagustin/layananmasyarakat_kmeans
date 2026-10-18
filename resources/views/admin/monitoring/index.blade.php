@extends('layouts.admin')

@section('title', 'Monitoring Status')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h3 class="fw-bold mb-1">
            <i class="bi bi-clipboard-data-fill text-primary"></i>
            Monitoring Status
        </h3>

        <small class="text-muted">
            Pantau sebaran status layanan masyarakat DICIPTABINTAR Kota
            Bandung secara langsung.
        </small>
    </div>

</div>

<div class="card shadow-sm border-0 mb-4">

    <div class="card-header bg-white">

        <span class="fw-semibold">
            <i class="bi bi-diagram-3-fill text-primary"></i>
            Kriteria Bidang
        </span>

    </div>

    <div class="card-body">

        <p class="small text-muted mb-3">
            Kategori layanan dipilih sendiri oleh masyarakat saat
            mengajukan pengaduan. Bidang penanganan kemudian
            ditentukan <strong>otomatis oleh sistem</strong>
            berdasarkan kategori tersebut, sesuai cakupan
            masing-masing bidang berikut. Admin tetap dapat
            mengubah bidang secara manual di halaman Kelola
            Pengaduan apabila diperlukan.
        </p>

        <div class="row g-3">

            @forelse($kriteriaBidang as $bidang)

            <div class="col-md-4">

                <div class="border rounded p-3 h-100">

                    <div class="d-flex align-items-center justify-content-between mb-2">

                        <span class="fw-semibold">
                            {{ $bidang->nama_bidang }}
                        </span>

                        <span class="badge bg-primary">
                            {{ $bidang->kategoriLayanans->count() }} kategori
                        </span>

                    </div>

                    <p class="small text-muted mb-2">
                        {{ $bidang->deskripsi ?? 'Belum ada deskripsi.' }}
                    </p>

                    @if($bidang->kategoriLayanans->isNotEmpty())

                    <div class="d-flex flex-wrap gap-1">

                        @foreach($bidang->kategoriLayanans as $kategori)

                        <span class="badge bg-light text-dark border">
                            {{ $kategori->nama_kategori }}
                        </span>

                        @endforeach

                    </div>

                    @else

                    <small class="text-muted fst-italic">
                        Belum ada kategori layanan yang dipetakan
                        ke bidang ini.
                    </small>

                    @endif

                </div>

            </div>

            @empty

            <div class="col-12 text-center text-muted py-3">
                Belum ada data bidang.
            </div>

            @endforelse

        </div>

        <small class="text-muted d-block mt-3">
            Pemetaan kategori ke bidang dapat diubah melalui menu
            <a href="{{ route('admin.kategori-layanan.index') }}">Kategori Layanan</a>,
            dan deskripsi cakupan bidang dapat diubah melalui menu
            <a href="{{ route('admin.bidang.index') }}">Bidang</a>.
        </small>

    </div>

</div>

<div class="row g-3">

    @php
        $kolom = [
            'Diajukan' => ['data' => $diajukan, 'warna' => 'warning', 'icon' => 'bi-hourglass-split'],
            'Diproses' => ['data' => $diproses, 'warna' => 'primary', 'icon' => 'bi-gear-fill'],
            'Selesai'  => ['data' => $selesai,  'warna' => 'success', 'icon' => 'bi-check-circle-fill'],
        ];
    @endphp

    @foreach($kolom as $label => $item)

        <div class="col-md-4">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-header bg-{{ $item['warna'] }} bg-opacity-10">

                    <div class="d-flex justify-content-between align-items-center">

                        <span class="fw-semibold">
                            <i class="bi {{ $item['icon'] }} text-{{ $item['warna'] }}"></i>
                            {{ $label }}
                        </span>

                        <span class="badge bg-{{ $item['warna'] }}">
                            {{ $item['data']->count() }}
                        </span>

                    </div>

                </div>

                <div class="card-body" style="max-height: 520px; overflow-y: auto;">

                    @forelse($item['data'] as $pengaduan)

                        <a href="{{ route('admin.pengaduan.edit', $pengaduan->pengaduan_id) }}"
                           class="text-decoration-none text-dark">

                            <div class="border rounded p-2 mb-2">

                                <div class="d-flex justify-content-between">
                                    <strong class="small">{{ $pengaduan->kode_pengaduan }}</strong>
                                    <small class="text-muted">
                                        {{ optional($pengaduan->tanggal_pengaduan)->format('d-m-Y') }}
                                    </small>
                                </div>

                                <div class="small text-truncate">{{ $pengaduan->judul }}</div>

                                <small class="text-muted">
                                    {{ optional($pengaduan->kategori)->nama_kategori ?? '-' }}
                                    &middot;
                                    {{ optional($pengaduan->bidang)->nama_bidang ?? 'Belum ditentukan' }}
                                </small>

                            </div>

                        </a>

                    @empty

                        <p class="text-muted text-center small mb-0">
                            Tidak ada layanan dengan status ini.
                        </p>

                    @endforelse

                </div>

            </div>

        </div>

    @endforeach

</div>

@endsection
