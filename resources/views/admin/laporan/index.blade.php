@extends('layouts.admin')

@section('title', 'Laporan')

@section('content')

<style>
    @media print {
        .no-print { display: none !important; }
        .sidebar, .admin-navbar { display: none !important; }
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 no-print">

    <div>
        <h3 class="fw-bold mb-1">
            <i class="bi bi-file-earmark-pdf-fill text-primary"></i>
            Laporan
        </h3>

        <small class="text-muted">
            Rekapitulasi data layanan masyarakat dan hasil analisis K-Means
            DICIPTABINTAR Kota Bandung.
        </small>
    </div>

    <button onclick="window.print()" class="btn btn-primary">
        <i class="bi bi-printer-fill"></i>
        Cetak / Simpan
    </button>

</div>

<div class="text-center mb-4 d-none d-print-block">
    <h4 class="fw-bold mb-0">Laporan Layanan Masyarakat</h4>
    <small>DICIPTABINTAR Kota Bandung — Dicetak {{ now()->format('d-m-Y H:i') }}</small>
</div>

{{-- Ringkasan Umum --}}
<div class="row g-3 mb-4">

    <div class="col-md-3">
        <div class="card shadow-sm border-0 text-center">
            <div class="card-body">
                <small class="text-muted">Total Layanan</small>
                <h3 class="fw-bold mb-0">{{ $totalPengaduan }}</h3>
            </div>
        </div>
    </div>

    @foreach(['Diajukan', 'Diproses', 'Selesai'] as $status)
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-body">
                    <small class="text-muted">{{ $status }}</small>
                    <h3 class="fw-bold mb-0">{{ $rekapStatus[$status] ?? 0 }}</h3>
                </div>
            </div>
        </div>
    @endforeach

</div>

{{-- Hasil Analisis K-Means Terakhir --}}
<div class="card shadow-sm border-0 mb-4">

    <div class="card-header bg-white">
        <h5 class="mb-0">Hasil Analisis K-Means Terakhir</h5>
    </div>

    <div class="card-body">

        @if($analisisTerakhir)

            <div class="row g-3">

                <div class="col-md-3">
                    <small class="text-muted d-block">Jumlah Data</small>
                    <strong>{{ $analisisTerakhir->jumlah_data }}</strong>
                </div>

                <div class="col-md-3">
                    <small class="text-muted d-block">K Optimal (Jumlah Cluster)</small>
                    <strong>{{ $analisisTerakhir->k_optimal }}</strong>
                </div>

                <div class="col-md-3">
                    <small class="text-muted d-block">Silhouette Score</small>
                    <strong>{{ number_format($analisisTerakhir->silhouette_score, 4) }}</strong>
                </div>

                <div class="col-md-3">
                    <small class="text-muted d-block">Tanggal Analisis</small>
                    <strong>{{ $analisisTerakhir->created_at->format('d-m-Y') }}</strong>
                </div>

            </div>

            @if($analisisTerakhir->interpretasi)
                <hr>
                <p class="mb-0">{{ $analisisTerakhir->interpretasi }}</p>
            @endif

            @if(!empty($analisisTerakhir->jumlah_cluster_detail))

                <hr>

                <table class="table table-sm table-bordered mt-2">
                    <thead class="table-light">
                        <tr>
                            <th>Cluster</th>
                            <th>Jumlah Data</th>
                            <th>Kata Kunci Dominan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($analisisTerakhir->jumlah_cluster_detail as $clusterKey => $jumlah)
                            <tr>
                                <td>Cluster {{ $clusterKey }}</td>
                                <td>{{ $jumlah }}</td>
                                <td>
                                    {{ isset($analisisTerakhir->top_terms[$clusterKey])
                                        ? implode(', ', (array) $analisisTerakhir->top_terms[$clusterKey])
                                        : '-'
                                    }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            @endif

        @else

            <p class="text-muted mb-0">
                Belum ada analisis yang dijalankan. Silakan jalankan analisis
                terlebih dahulu pada menu Dataset.
            </p>

        @endif

    </div>

</div>

<div class="row g-4">

    {{-- Rekap per Kategori --}}
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">

            <div class="card-header bg-white">
                <h5 class="mb-0">Rekap per Kategori Layanan</h5>
            </div>

            <div class="card-body">

                <table class="table table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Kategori</th>
                            <th class="text-end">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rekapKategori as $kategori)
                            <tr>
                                <td>{{ $kategori->nama_kategori }}</td>
                                <td class="text-end">{{ $kategori->pengaduans_count }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-center text-muted">Belum ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>

            </div>

        </div>
    </div>

    {{-- Rekap per Bidang --}}
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">

            <div class="card-header bg-white">
                <h5 class="mb-0">Rekap per Bidang</h5>
            </div>

            <div class="card-body">

                <table class="table table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Bidang</th>
                            <th class="text-end">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rekapBidang as $bidang)
                            <tr>
                                <td>{{ $bidang->nama_bidang }}</td>
                                <td class="text-end">{{ $bidang->pengaduans_count }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-center text-muted">Belum ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>

            </div>

        </div>
    </div>

</div>

@endsection
