@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h3 class="fw-bold mb-1">
            Dashboard Admin
        </h3>
        <small class="text-muted">
            Selamat datang di Sistem Analisis Pengelompokan Layanan Masyarakat DICIPTABINTAR Kota Bandung.
        </small>
    </div>

    <div class="text-end">
        <small class="text-muted">
            {{ now()->format('d F Y') }}
        </small>
    </div>

</div>

<div class="row g-4">

    <div class="col-lg-4 col-md-6">

        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <small class="text-muted">
                            Total Pengaduan
                        </small>

                        <h2 class="fw-bold text-primary mt-2">
                            {{ $totalPengaduan }}
                        </h2>

                    </div>

                    <div class="bg-primary bg-opacity-10 rounded-circle p-3">

                        <i class="bi bi-chat-dots-fill fs-2 text-primary"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="col-lg-4 col-md-6">

        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <small class="text-muted">
                            Total Dataset
                        </small>

                        <h2 class="fw-bold text-success mt-2">
                            {{ $totalDataset }}
                        </h2>

                    </div>

                    <div class="bg-success bg-opacity-10 rounded-circle p-3">

                        <i class="bi bi-file-earmark-excel-fill fs-2 text-success"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="col-lg-4 col-md-6">

        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <small class="text-muted">
                            Total Analisis
                        </small>

                        <h2 class="fw-bold text-danger mt-2">
                            {{ $totalAnalisis }}
                        </h2>

                    </div>

                    <div class="bg-danger bg-opacity-10 rounded-circle p-3">

                        <i class="bi bi-bar-chart-fill fs-2 text-danger"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<div class="row g-4 mt-1">

    <div class="col-lg-6">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-header bg-white">

                <h5 class="mb-0">
                    Master Data
                </h5>

            </div>

            <div class="card-body">

                <table class="table table-borderless">

                    <tr>

                        <td>Total Bidang</td>

                        <td class="text-end fw-bold">
                            {{ $totalBidang }}
                        </td>

                    </tr>

                    <tr>

                        <td>Total Kategori Layanan</td>

                        <td class="text-end fw-bold">
                            {{ $totalKategori }}
                        </td>

                    </tr>

                </table>

            </div>

        </div>

    </div>

    <div class="col-lg-6">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-header bg-white">

                <h5 class="mb-0">
                    Informasi Sistem
                </h5>

            </div>

            <div class="card-body">

                <p class="mb-2">
                    <i class="bi bi-check-circle-fill text-success"></i>
                    Laravel 12
                </p>

                <p class="mb-2">
                    <i class="bi bi-check-circle-fill text-success"></i>
                    Algoritma K-Means
                </p>

                <p class="mb-2">
                    <i class="bi bi-check-circle-fill text-success"></i>
                    TF-IDF
                </p>

                <p class="mb-0">
                    <i class="bi bi-check-circle-fill text-success"></i>
                    Dashboard Administrator
                </p>

            </div>

        </div>

    </div>

</div>

{{-- ================================================================== --}}
{{-- Dashboard Analisis: Distribusi Cluster                              --}}
{{-- ================================================================== --}}

@php
    // Warna tiap cluster -- dipakai konsisten di kartu penjelasan,
    // grafik doughnut, dan badge, supaya cluster yang sama selalu
    // punya warna yang sama di seluruh halaman.
    $warnaCluster = [
        '#0d6efd', '#198754', '#dc3545', '#ffc107',
        '#6f42c1', '#20c997', '#fd7e14', '#0dcaf0',
    ];

    // Penjelasan awam untuk tiap label interpretasi Silhouette Score.
    $penjelasanInterpretasi = [
        'Sangat Baik' => 'Aduan-aduan dalam kelompok ini sangat mirip satu sama lain, dan jelas berbeda dari kelompok lainnya. Hasil pengelompokan pada kategori ini sangat bisa diandalkan.',
        'Baik'        => 'Aduan-aduan dalam kelompok ini cukup mirip satu sama lain dan cukup jelas berbeda dari kelompok lainnya.',
        'Cukup'       => 'Aduan-aduan dalam kelompok ini cukup mirip, meski sebagian ada yang mendekati batas dengan kelompok lain (sedikit tumpang tindih).',
        'Lemah'       => 'Aduan-aduan dalam kelompok ini kurang mirip satu sama lain, atau isinya cukup mirip dengan kelompok tetangganya, sehingga batas antar kelompok kurang tegas.',
    ];

    // Warna badge Bootstrap untuk tiap label interpretasi.
    $warnaInterpretasi = [
        'Sangat Baik' => 'success',
        'Baik'        => 'success',
        'Cukup'       => 'warning',
        'Lemah'       => 'danger',
    ];
@endphp

<div class="row g-4 mt-1">

    <div class="col-12">

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white d-flex justify-content-between align-items-center">

                <h5 class="mb-0">
                    Dashboard Analisis
                </h5>

                @if ($analisisTerbaru)
                    <small class="text-muted">
                        Dataset: {{ $analisisTerbaru->dataset->periode ?? '-' }}
                    </small>
                @endif

            </div>

            <div class="card-body">

                @if (!$analisisTerbaru || $distribusiCluster->isEmpty())

                    <div class="text-center py-5">

                        <i class="bi bi-bar-chart-line display-4 text-muted"></i>

                        <p class="text-muted mt-3 mb-0">
                            Belum ada hasil analisis. Jalankan analisis pada
                            halaman Dataset terlebih dahulu untuk melihat
                            distribusi cluster di sini.
                        </p>

                    </div>

                @else

                    {{-- Ringkasan Angka --}}
                    <div class="row g-3 mb-4">

                        <div class="col-md-3 col-6">
                            <div class="border rounded p-3 text-center h-100">
                                <small class="text-muted d-block">Jumlah Data</small>
                                <span class="fw-bold fs-4">
                                    {{ $analisisTerbaru->jumlah_data_clustering }}
                                </span>
                            </div>
                        </div>

                        <div class="col-md-3 col-6">
                            <div class="border rounded p-3 text-center h-100">
                                <small class="text-muted d-block">K Optimal (Jumlah Kelompok)</small>
                                <span class="fw-bold fs-4">
                                    {{ $analisisTerbaru->k_optimal }}
                                </span>
                            </div>
                        </div>

                        <div class="col-md-3 col-6">
                            <div class="border rounded p-3 text-center h-100">
                                <small class="text-muted d-block">Silhouette Score</small>
                                <span class="fw-bold fs-4">
                                    {{ number_format($analisisTerbaru->silhouette_score, 4) }}
                                </span>
                            </div>
                        </div>

                        <div class="col-md-3 col-6">
                            <div class="border rounded p-3 text-center h-100">
                                <small class="text-muted d-block">Interpretasi</small>
                                <span class="badge bg-{{ $warnaInterpretasi[$analisisTerbaru->interpretasi] ?? 'secondary' }} fs-6">
                                    {{ $analisisTerbaru->interpretasi ?? '-' }}
                                </span>
                            </div>
                        </div>

                    </div>

                    @if ($analisisTerbaru->interpretasi && isset($penjelasanInterpretasi[$analisisTerbaru->interpretasi]))
                        <div class="alert alert-light border small mb-4">
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>Apa artinya Silhouette Score {{ number_format($analisisTerbaru->silhouette_score, 4) }} ({{ $analisisTerbaru->interpretasi }})?</strong>
                            {{ $penjelasanInterpretasi[$analisisTerbaru->interpretasi] }}
                            Skor berkisar dari -1 sampai 1: semakin mendekati 1, semakin rapi pengelompokannya.
                        </div>
                    @endif

                    {{-- ============================================== --}}
                    {{-- Penjelasan Rinci Tiap Cluster                   --}}
                    {{-- ============================================== --}}

                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-diagram-3 me-1"></i>
                        Penjelasan Tiap Kelompok (Cluster)
                    </h6>

                    <div class="row g-3 mb-4">

                        @foreach ($distribusiCluster as $index => $item)

                            @php
                                $warna = $warnaCluster[$index % count($warnaCluster)];
                                $badgeWarna = $warnaInterpretasi[$item['interpretasi']] ?? 'secondary';
                            @endphp

                            <div class="col-lg-6">

                                <div class="border rounded p-3 h-100" style="border-left: 5px solid {{ $warna }} !important;">

                                    <div class="d-flex justify-content-between align-items-start mb-2">

                                        <h6 class="fw-bold mb-0">
                                            <span class="d-inline-block rounded-circle me-2" style="width:10px;height:10px;background:{{ $warna }}"></span>
                                            {{ $item['nama_cluster'] }}
                                        </h6>

                                        <span class="badge bg-{{ $badgeWarna }}">
                                            {{ $item['interpretasi'] ?? '-' }}
                                        </span>

                                    </div>

                                    <p class="small text-muted mb-2">
                                        Berisi <strong>{{ $item['jumlah_data'] }} pengaduan/permohonan</strong>
                                        ({{ $item['persentase'] }}% dari seluruh data),
                                        dengan Silhouette Score kelompok
                                        <strong>{{ $item['silhouette_score'] !== null ? number_format($item['silhouette_score'], 4) : '-' }}</strong>.
                                    </p>

                                    @if (!empty($item['kata_kunci']))
                                        <p class="small mb-1 text-muted">Kata kunci yang paling sering muncul di kelompok ini:</p>
                                        <div class="mb-2">
                                            @foreach ($item['kata_kunci'] as $kata)
                                                <span class="badge bg-light text-dark border me-1 mb-1">{{ $kata }}</span>
                                            @endforeach
                                        </div>
                                        <p class="small mb-0">
                                            Sistem menempatkan aduan-aduan ini dalam satu kelompok karena isinya
                                            sering menyebut kata-kata di atas, sehingga dinamakan
                                            <em>"{{ $item['nama_cluster'] }}"</em>.
                                        </p>
                                    @endif

                                </div>

                            </div>

                        @endforeach

                    </div>

                    <div class="row g-4 mb-2">

                        {{-- Grafik Doughnut --}}
                        <div class="col-lg-5">

                            <canvas id="chartDistribusiCluster" height="260"></canvas>

                        </div>

                        {{-- Tabel Breakdown --}}
                        <div class="col-lg-7">

                            <table class="table table-sm align-middle">

                                <thead>
                                    <tr>
                                        <th>Cluster</th>
                                        <th class="text-end">Jumlah Data</th>
                                        <th class="text-end">%</th>
                                        <th class="text-end">Silhouette Score</th>
                                        <th>Interpretasi</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    @foreach ($distribusiCluster as $item)
                                        <tr>
                                            <td>{{ $item['nama_cluster'] }}</td>
                                            <td class="text-end">{{ $item['jumlah_data'] }}</td>
                                            <td class="text-end">{{ $item['persentase'] }}%</td>
                                            <td class="text-end">
                                                {{ $item['silhouette_score'] !== null ? number_format($item['silhouette_score'], 4) : '-' }}
                                            </td>
                                            <td>{{ $item['interpretasi'] ?? '-' }}</td>
                                        </tr>
                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    </div>

                @endif

            </div>

        </div>

    </div>

</div>

{{-- ================================================================== --}}
{{-- Hasil Analisis per Bidang (pelengkap analisis gabungan di atas)     --}}
{{-- ================================================================== --}}

<div class="row g-4 mt-1">

    <div class="col-12">

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white">

                <h5 class="mb-0">
                    Hasil Analisis per Bidang
                </h5>

                <small class="text-muted">
                    Selain analisis gabungan (seluruh data sekaligus) di atas,
                    K-Means di bawah ini dijalankan terpisah untuk tiap Bidang
                    (Tata Ruang, Bangunan Gedung, Pemakaman, Lainnya), sehingga
                    kelompok yang terbentuk benar-benar mencerminkan pola pada
                    Bidang tersebut.
                </small>

            </div>

            <div class="card-body">

                @if (!$analisisTerbaru || $analisisPerBidang->isEmpty())

                    <div class="text-center py-5">

                        <i class="bi bi-diagram-3 display-4 text-muted"></i>

                        <p class="text-muted mt-3 mb-0">
                            Belum ada hasil analisis per Bidang. Jalankan
                            analisis pada halaman Dataset terlebih dahulu
                            untuk melihatnya di sini.
                        </p>

                    </div>

                @else

                    <div class="row g-3">

                        @foreach ($analisisPerBidang as $bidang)

                            <div class="col-lg-6">

                                <div class="border rounded p-3 h-100">

                                    <div class="d-flex justify-content-between align-items-start mb-2">

                                        <h6 class="fw-bold mb-0">
                                            {{ $bidang['nama_bidang'] }}
                                        </h6>

                                        @if ($bidang['status'] === 'berhasil')
                                            <span class="badge bg-{{ $warnaInterpretasi[$bidang['interpretasi']] ?? 'secondary' }}">
                                                {{ $bidang['interpretasi'] ?? '-' }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                {{ $bidang['status'] === 'dilewati' ? 'Dilewati' : 'Gagal' }}
                                            </span>
                                        @endif

                                    </div>

                                    @if ($bidang['status'] !== 'berhasil')

                                        <p class="small text-muted mb-0">
                                            {{ $bidang['jumlah_data'] }} data.
                                            {{ $bidang['alasan'] ?? 'Analisis untuk Bidang ini tidak tersedia.' }}
                                        </p>

                                    @else

                                        <p class="small text-muted mb-3">
                                            <strong>{{ $bidang['jumlah_data'] }} pengaduan/permohonan</strong>,
                                            dikelompokkan menjadi
                                            <strong>{{ $bidang['k_optimal'] }} kelompok</strong>,
                                            dengan Silhouette Score
                                            <strong>{{ number_format($bidang['silhouette_score'], 4) }}</strong>.
                                        </p>

                                        <div class="table-responsive">

                                            <table class="table table-sm align-middle mb-0">

                                                <thead>
                                                    <tr>
                                                        <th>Cluster</th>
                                                        <th class="text-end">Jumlah</th>
                                                        <th class="text-end">%</th>
                                                        <th class="text-end">Silhouette</th>
                                                        <th>Kata Kunci</th>
                                                    </tr>
                                                </thead>

                                                <tbody>

                                                    @foreach ($bidang['per_cluster'] as $index => $item)
                                                        <tr>
                                                            <td>
                                                                <span class="d-inline-block rounded-circle me-1" style="width:8px;height:8px;background:{{ $warnaCluster[$index % count($warnaCluster)] }}"></span>
                                                                {{ $item['nama_cluster'] }}
                                                            </td>
                                                            <td class="text-end">{{ $item['jumlah_data'] }}</td>
                                                            <td class="text-end">{{ $item['persentase'] }}%</td>
                                                            <td class="text-end">
                                                                {{ $item['silhouette_score'] !== null ? number_format($item['silhouette_score'], 4) : '-' }}
                                                            </td>
                                                            <td class="small">
                                                                @foreach (array_slice($item['kata_kunci'], 0, 3) as $kata)
                                                                    <span class="badge bg-light text-dark border me-1">{{ $kata }}</span>
                                                                @endforeach
                                                            </td>
                                                        </tr>
                                                    @endforeach

                                                </tbody>

                                            </table>

                                        </div>

                                    @endif

                                </div>

                            </div>

                        @endforeach

                    </div>

                @endif

            </div>

        </div>

    </div>

</div>

{{-- ================================================================== --}}
{{-- Grafik Elbow Method, K-Means, dan Silhouette Plot                   --}}
{{-- ================================================================== --}}

@if ($analisisTerbaru)

    @php
        // Cache-busting sederhana: setiap kali analisis dijalankan
        // ulang, updated_at berubah -> gambar di browser ikut
        // ter-refresh (tidak memakai gambar lama dari cache).
        $versiGrafik = $analisisTerbaru->updated_at?->timestamp;
    @endphp

    <div class="row g-4 mt-1">

        <div class="col-12">
            <h5 class="fw-bold">
                <i class="bi bi-graph-up me-1"></i>
                Grafik Proses Analisis K-Means
            </h5>
            <p class="text-muted small mb-0">
                Grafik di bawah ini dibuat otomatis oleh sistem setiap kali
                analisis dijalankan pada halaman Dataset, sehingga selalu
                menampilkan hasil dari data terbaru.
            </p>
        </div>

        @if (!$grafikAnalisis)

            <div class="col-12">
                <div class="alert alert-warning mb-0">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Grafik untuk hasil analisis ini belum tersedia.
                    Jalankan ulang analisis pada halaman
                    <strong>Dataset</strong> agar grafik Elbow Method,
                    K-Means, dan Silhouette Plot dapat dibuat dan
                    ditampilkan di sini.
                </div>
            </div>

        @else

            {{-- 1. GRAFIK ELBOW METHOD --}}
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">1. Metode Elbow &mdash; Menentukan Jumlah Kelompok (K) Optimal</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-4 align-items-center">
                            <div class="col-lg-7 text-center">
                                <img
                                    src="{{ asset($grafikAnalisis['elbow']) }}?v={{ $versiGrafik }}"
                                    alt="Grafik Elbow Method"
                                    class="img-fluid rounded border"
                                >
                            </div>
                            <div class="col-lg-5">
                                <p class="mb-2">
                                    Grafik ini menunjukkan seberapa "rapat" anggota di dalam
                                    tiap kelompok (disebut <em>inertia</em>) untuk beberapa
                                    pilihan jumlah kelompok, dari K=2 sampai K=8.
                                </p>
                                <p class="mb-2">
                                    Semakin banyak kelompok yang dibuat, nilai inertia akan
                                    semakin kecil &mdash; tetapi menambah kelompok terus-menerus
                                    tidak selalu berguna. Sistem mencari titik <strong>"siku"</strong>
                                    pada grafik, yaitu titik saat penurunan mulai melambat drastis.
                                </p>
                                <p class="mb-0">
                                    Titik itulah yang dipilih sebagai jumlah kelompok optimal,
                                    ditandai bintang pada grafik: <strong>K = {{ $analisisTerbaru->k_optimal }}</strong>.
                                    Artinya, seluruh data pengaduan/permohonan dibagi menjadi
                                    {{ $analisisTerbaru->k_optimal }} kelompok.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. GRAFIK K-MEANS (SCATTER) --}}
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">2. Hasil Pengelompokan K-Means</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-4 align-items-center">
                            <div class="col-lg-7 text-center">
                                <img
                                    src="{{ asset($grafikAnalisis['cluster']) }}?v={{ $versiGrafik }}"
                                    alt="Grafik Hasil Clustering K-Means"
                                    class="img-fluid rounded border"
                                >
                            </div>
                            <div class="col-lg-5">
                                <p class="mb-2">
                                    Setiap titik pada grafik ini mewakili
                                    <strong>satu pengaduan/permohonan warga</strong>.
                                    Karena teks aduan aslinya memiliki ratusan kata
                                    berbeda (terlalu banyak untuk digambar langsung),
                                    sistem meringkasnya menjadi 2 sumbu (Komponen 1
                                    dan Komponen 2) hanya untuk keperluan visualisasi
                                    &mdash; perhitungan kelompoknya sendiri tetap
                                    menggunakan data teks aslinya, bukan hasil ringkasan ini.
                                </p>
                                <p class="mb-0">
                                    Titik-titik dengan <strong>warna yang sama</strong>
                                    berarti berada dalam kelompok yang sama karena
                                    kemiripan kata-kata dalam aduannya. Semakin
                                    berdekatan posisi titik-titik tersebut, semakin
                                    mirip pula isi aduannya.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. GRAFIK SILHOUETTE PLOT --}}
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">3. Silhouette Plot &mdash; Seberapa Baik Kualitas Pengelompokan Ini</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-4 align-items-center">
                            <div class="col-lg-7 text-center">
                                <img
                                    src="{{ asset($grafikAnalisis['silhouette']) }}?v={{ $versiGrafik }}"
                                    alt="Grafik Silhouette Plot"
                                    class="img-fluid rounded border"
                                >
                            </div>
                            <div class="col-lg-5">
                                <p class="mb-2">
                                    Setiap "gerombolan warna" mewakili satu kelompok,
                                    disusun dari skor terendah ke tertinggi. Sumbu
                                    horizontal (Silhouette Coefficient) menunjukkan
                                    seberapa cocok satu data berada di kelompoknya
                                    dibanding kelompok lain:
                                </p>
                                <ul class="mb-2">
                                    <li><strong>Mendekati +1</strong>: data sangat cocok di kelompoknya.</li>
                                    <li><strong>Mendekati 0</strong>: data berada di antara dua kelompok (ambigu).</li>
                                    <li><strong>Negatif</strong>: data kemungkinan salah kelompok.</li>
                                </ul>
                                <p class="mb-3">
                                    Garis putus-putus vertikal menandai rata-rata seluruh
                                    data (Silhouette Score = {{ number_format($analisisTerbaru->silhouette_score, 4) }}).
                                    Semakin banyak bagian warna yang melewati garis ini ke
                                    kanan, semakin baik kualitas pengelompokannya.
                                </p>

                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Rentang Skor</th>
                                            <th>Interpretasi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="{{ $analisisTerbaru->interpretasi === 'Sangat Baik' ? 'table-success' : '' }}">
                                            <td>&ge; 0.70</td>
                                            <td>Sangat Baik</td>
                                        </tr>
                                        <tr class="{{ $analisisTerbaru->interpretasi === 'Baik' ? 'table-success' : '' }}">
                                            <td>0.50 &ndash; 0.69</td>
                                            <td>Baik</td>
                                        </tr>
                                        <tr class="{{ $analisisTerbaru->interpretasi === 'Cukup' ? 'table-warning' : '' }}">
                                            <td>0.25 &ndash; 0.49</td>
                                            <td>Cukup</td>
                                        </tr>
                                        <tr class="{{ $analisisTerbaru->interpretasi === 'Lemah' ? 'table-danger' : '' }}">
                                            <td>&lt; 0.25</td>
                                            <td>Lemah</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        @endif

    </div>

@endif

@endsection

@push('scripts')

    @if ($analisisTerbaru && $distribusiCluster->isNotEmpty())

        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

        <script>
            const ctxDistribusiCluster = document
                .getElementById('chartDistribusiCluster')
                .getContext('2d');

            new Chart(ctxDistribusiCluster, {
                type: 'doughnut',
                data: {
                    labels: @json($distribusiCluster->pluck('nama_cluster')),
                    datasets: [{
                        data: @json($distribusiCluster->pluck('jumlah_data')),
                        backgroundColor: [
                            '#0d6efd',
                            '#198754',
                            '#dc3545',
                            '#ffc107',
                            '#6f42c1',
                            '#20c997',
                            '#fd7e14',
                            '#0dcaf0',
                        ],
                    }],
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        title: {
                            display: true,
                            text: 'Distribusi Data per Cluster',
                        },
                    },
                },
            });
        </script>

    @endif

@endpush