@extends('layouts.admin')

@section('title', 'Analisis Dataset')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h3 class="fw-bold mb-1">
                <i class="bi bi-bar-chart-fill text-primary"></i>
                Hasil Analisis Dataset
            </h3>

            <small class="text-muted">
                Informasi dataset dan hasil pengelompokan (clustering)
                layanan masyarakat menggunakan algoritma K-Means.
            </small>

        </div>

        <a href="{{ route('admin.dataset.index') }}"
           class="btn btn-secondary">

            <i class="bi bi-arrow-left"></i>
            Kembali

        </a>

    </div>


    {{-- Notifikasi Berhasil --}}
    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show">

            <i class="bi bi-check-circle-fill me-2"></i>

            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>

    @endif


    {{-- Notifikasi Error --}}
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


    {{-- =====================================================
         PENJELASAN UMUM UNTUK ORANG AWAM
    ====================================================== --}}

    <div class="card shadow-sm border-0 mb-4 border-start border-4 border-info">

        <div class="card-body">

            <h5 class="mb-2">
                <i class="bi bi-info-circle-fill text-info me-1"></i>
                Apa yang Dilakukan Sistem Saat "Jalankan Analisis"?
            </h5>

            <p class="mb-2">
                Setiap kali tombol
                <strong>"Jalankan Analisis"</strong>
                ditekan, sistem secara otomatis membaca seluruh
                data permohonan/pengaduan yang sudah diunggah,
                lalu mengelompokkannya ke dalam beberapa
                <strong>cluster (kelompok)</strong>
                berdasarkan kemiripan isi/topik permohonan tersebut
                &mdash; permohonan dengan topik yang mirip akan
                dikumpulkan pada cluster yang sama.
            </p>

            <p class="mb-2">
                Proses ini melalui 4 tahap otomatis:
            </p>

            <ol class="mb-2">

                <li>
                    <strong>Preprocessing &amp; TF-IDF</strong>
                    &mdash; teks permohonan dibersihkan dan diubah
                    menjadi angka agar bisa dibandingkan kemiripannya
                    oleh komputer.
                </li>

                <li>
                    <strong>Elbow Method</strong>
                    &mdash; menentukan jumlah cluster (kelompok)
                    yang paling sesuai untuk data tersebut.
                </li>

                <li>
                    <strong>K-Means</strong>
                    &mdash; membagi seluruh data ke dalam jumlah
                    cluster yang telah ditentukan.
                </li>

                <li>
                    <strong>Silhouette Score</strong>
                    &mdash; mengukur seberapa baik kualitas
                    pengelompokan yang dihasilkan (apakah anggota
                    dalam satu cluster benar-benar mirip satu
                    sama lain).
                </li>

            </ol>

            <p class="mb-0 text-muted">
                Seluruh grafik dan penjelasan pada halaman ini
                dibuat ulang secara otomatis setiap kali analisis
                dijalankan, sehingga selalu mencerminkan data
                terbaru yang sudah diunggah.
            </p>

        </div>

    </div>


    {{-- =====================================================
         INFORMASI DATASET
    ====================================================== --}}

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-header bg-white">

            <h5 class="mb-0">
                Informasi Dataset
            </h5>

        </div>

        <div class="card-body">

            <table class="table table-bordered align-middle mb-0">

                <tr>

                    <th width="220">
                        ID Dataset
                    </th>

                    <td>
                        {{ $dataset->dataset_id }}
                    </td>

                </tr>

                <tr>

                    <th>
                        Periode
                    </th>

                    <td>
                        {{ $dataset->periode }}
                    </td>

                </tr>

                <tr>

                    <th>
                        File Dataset Ini
                    </th>

                    <td>

                        <i class="bi bi-file-earmark-excel text-success me-1"></i>

                        {{ $dataset->file_excel }}

                    </td>

                </tr>

                <tr>

                    <th>
                        Tanggal Upload
                    </th>

                    <td>

                        {{ optional($dataset->created_at)
                            ->format('d-m-Y H:i')
                            ?? '-'
                        }}

                    </td>

                </tr>

            </table>

        </div>

    </div>


    {{-- =====================================================
         SELURUH DATASET YANG DIGABUNGKAN PADA ANALISIS INI
    ====================================================== --}}

    @if($analisis && $datasetTerpakai->isNotEmpty())

        <div class="card shadow-sm border-0 mb-4">

            <div class="card-header bg-white">

                <h5 class="mb-0">
                    Seluruh Dataset yang Digabungkan pada Analisis Ini
                </h5>

                <small class="text-muted">
                    TF-IDF dan K-Means dihitung ulang dari awal atas
                    gabungan {{ $datasetTerpakai->count() }} file
                    dataset historis berikut, bukan hanya satu file.
                </small>

            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-sm table-bordered align-middle mb-0">

                        <thead class="table-light">
                            <tr>
                                <th width="60">No</th>
                                <th>Periode</th>
                                <th>File</th>
                                <th width="180">Tanggal Upload</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($datasetTerpakai as $index => $ds)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $ds->periode }}</td>
                                    <td>
                                        <i class="bi bi-file-earmark-excel text-success me-1"></i>
                                        {{ $ds->file_excel }}
                                    </td>
                                    <td>
                                        {{ optional($ds->created_at)->format('d-m-Y H:i') ?? '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    @endif


    @if($analisis)

        {{-- =====================================================
             RINGKASAN HASIL ANALISIS
        ====================================================== --}}

        <div class="card shadow-sm border-0 mb-4">

            <div class="card-header bg-primary text-white">

                <h5 class="mb-0">

                    <i class="bi bi-graph-up me-1"></i>

                    Ringkasan Hasil Analisis K-Means

                </h5>

            </div>

            <div class="card-body">

                @php

                    $badgeInterpretasi = match($analisis->interpretasi) {
                        'Sangat Baik' => 'bg-success',
                        'Baik'        => 'bg-primary',
                        'Cukup'       => 'bg-warning text-dark',
                        'Lemah'       => 'bg-danger',
                        default       => 'bg-secondary',
                    };

                @endphp

                <table class="table table-bordered align-middle">

                    <tr>

                        <th width="260">
                            Jumlah Data Dianalisis
                        </th>

                        <td>

                            {{ $analisis->jumlah_data_clustering
                                ?? $analisis->jumlah_data
                                ?? '-'
                            }}

                            data permohonan/pengaduan

                        </td>

                    </tr>

                    <tr>

                        <th>
                            Jumlah Cluster (K Optimal)
                        </th>

                        <td>

                            <span class="badge bg-primary fs-6">
                                {{ $analisis->k_optimal }}
                                Cluster
                            </span>

                            <div class="form-text mb-0">
                                Data dikelompokkan menjadi
                                {{ $analisis->k_optimal }}
                                kelompok, ditentukan otomatis oleh
                                sistem menggunakan Elbow Method
                                (lihat grafik Elbow di bawah).
                            </div>

                        </td>

                    </tr>

                    <tr>

                        <th>
                            Silhouette Score
                        </th>

                        <td>

                            <span class="fs-5 fw-bold">
                                {{ number_format(
                                    $analisis->silhouette_score,
                                    4
                                ) }}
                            </span>

                            <span class="badge {{ $badgeInterpretasi }} ms-2">
                                {{ $analisis->interpretasi ?? '-' }}
                            </span>

                            <div class="form-text mb-0">
                                Silhouette Score adalah angka
                                antara -1 sampai 1 yang menunjukkan
                                seberapa rapi hasil pengelompokan.
                                Semakin mendekati 1, semakin jelas
                                perbedaan antar-cluster dan semakin
                                mirip data di dalam cluster yang
                                sama. Nilai di atas 0.50 dianggap
                                <strong>Baik</strong>,
                                0.25&ndash;0.49 dianggap
                                <strong>Cukup</strong>,
                                dan di bawah 0.25 dianggap
                                <strong>Lemah</strong> (cluster masih
                                bertumpang tindih).
                            </div>

                        </td>

                    </tr>

                    <tr>

                        <th>
                            Durasi Proses
                        </th>

                        <td>

                            @if(!is_null($analisis->durasi_proses))

                                {{ $analisis->durasi_proses }}
                                detik

                            @else

                                -

                            @endif

                        </td>

                    </tr>

                    <tr>

                        <th>
                            Status Analisis
                        </th>

                        <td>

                            @if($analisis->status_analisis === 'Berhasil')

                                <span class="badge bg-success">
                                    Berhasil
                                </span>

                            @else

                                <span class="badge bg-danger">
                                    Gagal
                                </span>

                            @endif

                        </td>

                    </tr>

                    <tr>

                        <th>
                            Tanggal Analisis
                        </th>

                        <td>

                            {{ optional($analisis->created_at)
                                ->format('d-m-Y H:i')
                                ?? '-'
                            }}

                        </td>

                    </tr>

                </table>

            </div>

        </div>


        {{-- =====================================================
             DISTRIBUSI & PENJELASAN TIAP CLUSTER
        ====================================================== --}}

        <div class="card shadow-sm border-0 mb-4">

            <div class="card-header bg-white">

                <h5 class="mb-0">
                    <i class="bi bi-diagram-3-fill text-primary me-1"></i>
                    Pembagian dan Penjelasan Tiap Cluster
                </h5>

                <small class="text-muted">
                    Penjelasan ini menjawab pertanyaan
                    "Cluster 0 itu apa, Cluster 1 itu apa, dan
                    seterusnya" agar mudah dipahami oleh pengguna
                    di luar bidang IT.
                </small>

            </div>

            <div class="card-body">

                @php

                    // Penjelasan tambahan untuk cluster yang sudah
                    // memiliki nama deskriptif (dihasilkan otomatis
                    // saat jumlah cluster optimal = 4). Jika nama
                    // cluster belum dikenali (mis. jumlah cluster
                    // bukan 4), sistem akan memakai penjelasan umum.
                    $deskripsiCluster = [
                        'Layanan Administrasi dan Informasi Umum' =>
                            'Berisi permohonan/pengaduan yang '
                            . 'umumnya berupa pertanyaan umum, '
                            . 'permintaan informasi prosedur, dan '
                            . 'hal administratif yang sifatnya '
                            . 'tidak spesifik ke satu jenis '
                            . 'layanan tertentu.',

                        'Persyaratan dan Konsultasi KRK/SLF' =>
                            'Berisi permohonan/pengaduan yang '
                            . 'berkaitan dengan Keterangan Rencana '
                            . 'Kota (KRK) dan Sertifikat Laik '
                            . 'Fungsi (SLF), termasuk pertanyaan '
                            . 'mengenai persyaratan dan konsultasi '
                            . 'teknis.',

                        'Layanan PBG dan Reklame' =>
                            'Berisi permohonan/pengaduan yang '
                            . 'berkaitan dengan Persetujuan '
                            . 'Bangunan Gedung (PBG) serta '
                            . 'perizinan maupun pengaduan terkait '
                            . 'reklame.',

                        'Layanan Data dan Informasi Pemakaman' =>
                            'Berisi permohonan yang berkaitan '
                            . 'dengan data dan informasi '
                            . 'pemakaman, seperti pencarian data '
                            . 'makam dan pengajuan pemakaman baru.',
                    ];

                    $deskripsiUmum =
                        'Kelompok permohonan/pengaduan yang '
                        . 'memiliki topik dan kata-kata yang mirip '
                        . 'satu sama lain. Sistem mengelompokkan '
                        . 'data ini secara otomatis berdasarkan '
                        . 'kemiripan isi teks, bukan berdasarkan '
                        . 'kategori yang dipilih pemohon saat '
                        . 'mengajukan permohonan.';

                    $perCluster = $analisis->per_cluster ?? [];

                    $totalData = collect($perCluster)
                        ->sum('jumlah_data');

                @endphp

                @if(count($perCluster) > 0)

                    <div class="row g-3">

                        @foreach($perCluster as $clusterId => $cluster)

                            @php

                                $nama = $cluster['nama_cluster']
                                    ?? "Cluster {$clusterId}";

                                $jumlah = $cluster['jumlah_data'] ?? 0;

                                $persentase = $totalData > 0
                                    ? round(
                                        ($jumlah / $totalData) * 100,
                                        1
                                    )
                                    : 0;

                                $skorCluster =
                                    $cluster['silhouette_score']
                                    ?? null;

                                $interpretasiCluster =
                                    $cluster['interpretasi']
                                    ?? '-';

                                $badgeCluster = match($interpretasiCluster) {
                                    'Sangat Baik' => 'bg-success',
                                    'Baik'        => 'bg-primary',
                                    'Cukup'       => 'bg-warning text-dark',
                                    'Lemah'       => 'bg-danger',
                                    default       => 'bg-secondary',
                                };

                                $deskripsi = $deskripsiCluster[$nama]
                                    ?? $deskripsiUmum;

                            @endphp

                            <div class="col-md-6">

                                <div class="border rounded p-3 h-100">

                                    <div class="d-flex justify-content-between align-items-start mb-2">

                                        <h6 class="fw-bold mb-0">

                                            <span class="badge bg-dark me-1">
                                                Cluster {{ $clusterId }}
                                            </span>

                                            {{ $nama }}

                                        </h6>

                                    </div>

                                    <p class="small text-muted mb-2">
                                        {{ $deskripsi }}
                                    </p>

                                    <div class="d-flex justify-content-between small mb-1">

                                        <span>
                                            {{ $jumlah }}
                                            data
                                            ({{ $persentase }}%
                                            dari total)
                                        </span>

                                        @if(!is_null($skorCluster))

                                            <span>

                                                Silhouette:
                                                {{ number_format(
                                                    $skorCluster,
                                                    4
                                                ) }}

                                                <span class="badge {{ $badgeCluster }}">
                                                    {{ $interpretasiCluster }}
                                                </span>

                                            </span>

                                        @endif

                                    </div>

                                    <div class="progress" style="height: 10px;">

                                        <div class="progress-bar"
                                             role="progressbar"
                                             style="width: {{ $persentase }}%">
                                        </div>

                                    </div>

                                </div>

                            </div>

                        @endforeach

                    </div>

                @else

                    <div class="alert alert-warning mb-0">

                        <i class="bi bi-exclamation-triangle-fill me-2"></i>

                        Detail per cluster belum tersedia untuk hasil
                        analisis ini (kemungkinan dihasilkan sebelum
                        fitur penjelasan cluster ditambahkan).
                        Klik <strong>"Jalankan Analisis"</strong>
                        kembali dari halaman Dataset untuk
                        memperbarui hasilnya.

                    </div>

                @endif

            </div>

        </div>


        {{-- =====================================================
             GRAFIK HASIL ANALISIS
        ====================================================== --}}

        @php

            $folderGrafik = "analisis/{$dataset->dataset_id}";

            $pathElbow      = "storage/{$folderGrafik}/grafik_elbow.png";
            $pathCluster    = "storage/{$folderGrafik}/grafik_cluster.png";
            $pathSilhouette = "storage/{$folderGrafik}/grafik_silhouette.png";

            $adaElbow      = file_exists(public_path($pathElbow));
            $adaCluster    = file_exists(public_path($pathCluster));
            $adaSilhouette = file_exists(public_path($pathSilhouette));

        @endphp

        <div class="card shadow-sm border-0 mb-4">

            <div class="card-header bg-white">

                <h5 class="mb-0">
                    <i class="bi bi-bar-chart-line-fill text-primary me-1"></i>
                    Grafik Hasil Analisis
                </h5>

                <small class="text-muted">
                    Grafik berikut dibuat otomatis oleh sistem setiap
                    kali analisis dijalankan, berdasarkan data
                    terbaru yang sedang ditampilkan di halaman ini.
                </small>

            </div>

            <div class="card-body">

                @if(!$adaElbow && !$adaCluster && !$adaSilhouette)

                    <div class="alert alert-warning mb-0">

                        <i class="bi bi-exclamation-triangle-fill me-2"></i>

                        Grafik belum tersedia untuk hasil analisis ini
                        (kemungkinan dihasilkan sebelum fitur grafik
                        ditambahkan, atau proses pembuatan grafik
                        sempat gagal). Klik
                        <strong>"Jalankan Analisis"</strong>
                        kembali dari halaman Dataset untuk membuat
                        grafik terbaru.

                    </div>

                @else

                    {{-- GRAFIK 1: ELBOW METHOD --}}

                    <div class="mb-5">

                        <h6 class="fw-bold">
                            1. Grafik Elbow Method
                            (Penentuan Jumlah Cluster)
                        </h6>

                        @if($adaElbow)

                            <img src="{{ asset($pathElbow) }}"
                                 class="img-fluid border rounded mb-2"
                                 alt="Grafik Elbow Method">

                        @else

                            <div class="alert alert-secondary">
                                Grafik Elbow Method belum tersedia.
                            </div>

                        @endif

                        <div class="alert alert-light border small mb-0">

                            <strong>Cara membaca grafik ini:</strong>
                            Garis pada grafik menunjukkan seberapa
                            besar "kesalahan" (inertia) pengelompokan
                            untuk setiap kemungkinan jumlah cluster.
                            Semakin banyak cluster, nilai inertia
                            akan terus menurun. Sistem memilih titik
                            di mana penurunan mulai melandai
                            (ditandai bintang dan garis putus-putus)
                            sebagai jumlah cluster yang paling
                            seimbang &mdash; cukup detail untuk
                            membedakan topik, tapi tidak terlalu
                            banyak sehingga sulit dipahami.

                        </div>

                    </div>


                    {{-- GRAFIK 2: HASIL K-MEANS --}}

                    <div class="mb-5">

                        <h6 class="fw-bold">
                            2. Grafik Hasil Pengelompokan K-Means
                        </h6>

                        @if($adaCluster)

                            <img src="{{ asset($pathCluster) }}"
                                 class="img-fluid border rounded mb-2"
                                 alt="Grafik Hasil K-Means">

                        @else

                            <div class="alert alert-secondary">
                                Grafik hasil K-Means belum tersedia.
                            </div>

                        @endif

                        <div class="alert alert-light border small mb-0">

                            <strong>Cara membaca grafik ini:</strong>
                            Setiap titik pada grafik mewakili satu
                            data permohonan/pengaduan. Karena isi
                            teks aslinya terlalu kompleks untuk
                            digambar langsung, sistem
                            menyederhanakannya menjadi 2 sumbu
                            (Komponen 1 dan Komponen 2) agar bisa
                            divisualisasikan di atas kertas/layar.
                            Titik-titik dengan warna yang sama berada
                            pada cluster yang sama. Semakin
                            titik-titik dengan warna sama saling
                            berdekatan dan menjauh dari warna lain,
                            semakin baik kualitas pengelompokannya.

                        </div>

                    </div>


                    {{-- GRAFIK 3: SILHOUETTE PLOT --}}

                    <div>

                        <h6 class="fw-bold">
                            3. Grafik Silhouette Score
                        </h6>

                        @if($adaSilhouette)

                            <img src="{{ asset($pathSilhouette) }}"
                                 class="img-fluid border rounded mb-2"
                                 alt="Grafik Silhouette Score">

                        @else

                            <div class="alert alert-secondary">
                                Grafik Silhouette Score belum
                                tersedia.
                            </div>

                        @endif

                        <div class="alert alert-light border small mb-0">

                            <strong>Cara membaca grafik ini:</strong>
                            Setiap "bilah" horizontal mewakili satu
                            cluster. Panjang tiap garis tipis di
                            dalam bilah menunjukkan skor kecocokan
                            satu data terhadap clusternya sendiri
                            (semakin panjang ke kanan/mendekati 1,
                            semakin cocok data itu berada di cluster
                            tersebut; jika sampai bernilai negatif,
                            berarti data tersebut sebenarnya lebih
                            mirip dengan cluster lain). Garis putus
                            vertikal menunjukkan rata-rata Silhouette
                            Score dari seluruh data
                            (lihat penjelasan angka ini pada bagian
                            "Ringkasan Hasil Analisis" di atas).

                        </div>

                    </div>

                @endif

            </div>

        </div>

    @else

        <div class="alert alert-warning mb-0">

            <i class="bi bi-exclamation-triangle-fill me-2"></i>

            Hasil analisis untuk dataset ini belum tersedia. Silakan
            klik <strong>"Jalankan Analisis"</strong> dari halaman
            Dataset terlebih dahulu.

        </div>

    @endif

</div>

@endsection
