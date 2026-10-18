<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Analisis;
use App\Models\Bidang;
use App\Models\Dataset;
use App\Models\KategoriLayanan;
use App\Models\Pengaduan;
use App\Models\User;

class DashboardController extends Controller
{
    // Menampilkan Dashboard Admin
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | Analisis Terbaru (untuk Grafik Distribusi Cluster)
        |--------------------------------------------------------------------------
        */

        $analisisTerbaru = Analisis::with('dataset')
            ->latest('analisis_id')
            ->first();

        $distribusiCluster = collect();

        if ($analisisTerbaru && is_array($analisisTerbaru->per_cluster)) {

            // Kata kunci dominan tiap cluster (hasil TF-IDF),
            // dipakai supaya penjelasan cluster di dashboard
            // mudah dipahami orang awam ("aduan-aduan ini
            // dikelompokkan bersama karena sering menyebut...").
            $topTermsPerCluster =
                is_array($analisisTerbaru->top_terms)
                    ? $analisisTerbaru->top_terms
                    : [];

            $totalDataClustering =
                $analisisTerbaru->jumlah_data_clustering
                ?: 1;

            $distribusiCluster = collect(
                $analisisTerbaru->per_cluster
            )
            ->map(function ($detail, $clusterId) use (
                $topTermsPerCluster,
                $totalDataClustering
            ) {

                $jumlahData =
                    $detail['jumlah_data']
                    ?? 0;

                return [
                    'cluster_id' =>
                        $clusterId,

                    'nama_cluster' =>
                        $detail['nama_cluster']
                        ?? "Cluster {$clusterId}",

                    'jumlah_data' =>
                        $jumlahData,

                    'persentase' =>
                        round(
                            $jumlahData
                            / $totalDataClustering
                            * 100,
                            1
                        ),

                    'silhouette_score' =>
                        $detail['silhouette_score']
                        ?? null,

                    'interpretasi' =>
                        $detail['interpretasi']
                        ?? null,

                    'kata_kunci' =>
                        $topTermsPerCluster[$clusterId]
                        ?? $topTermsPerCluster[(string) $clusterId]
                        ?? [],
                ];
            })
            ->values();
        }

        // Path relatif 3 grafik dashboard (Elbow, K-Means,
        // Silhouette) hasil python/analisis.py. Null jika belum
        // pernah ada analisis, atau jika pembuatan grafik pada
        // analisis terakhir gagal.
        $grafikAnalisis =
            $analisisTerbaru
            && is_array($analisisTerbaru->grafik)
                ? $analisisTerbaru->grafik
                : null;

        /*
        |--------------------------------------------------------------------------
        | Analisis per Bidang
        |--------------------------------------------------------------------------
        |
        | Pelengkap analisis gabungan di atas: hasil clustering
        | K-Means yang dijalankan TERPISAH untuk tiap Bidang
        | (Tata Ruang, Bangunan Gedung, Pemakaman, Lainnya).
        | Lihat python/engine/clustering.py
        | (run_clustering_per_bidang).
        */

        $analisisPerBidang = collect();

        if (
            $analisisTerbaru
            && is_array($analisisTerbaru->per_bidang)
        ) {

            $analisisPerBidang = collect(
                $analisisTerbaru->per_bidang
            )
            ->map(function ($detail, $namaBidang) {

                $totalBidang =
                    $detail['jumlah_data']
                    ?: 1;

                $perClusterBidang = collect(
                    $detail['per_cluster']
                    ?? []
                )
                ->map(function ($clusterDetail, $clusterId) use (
                    $detail,
                    $totalBidang
                ) {

                    $jumlahDataCluster =
                        $clusterDetail['jumlah_data']
                        ?? 0;

                    return [
                        'cluster_id' =>
                            $clusterId,

                        'nama_cluster' =>
                            $clusterDetail['nama_cluster']
                            ?? "Cluster {$clusterId}",

                        'jumlah_data' =>
                            $jumlahDataCluster,

                        'persentase' =>
                            round(
                                $jumlahDataCluster
                                / $totalBidang
                                * 100,
                                1
                            ),

                        'silhouette_score' =>
                            $clusterDetail['silhouette_score']
                            ?? null,

                        'interpretasi' =>
                            $clusterDetail['interpretasi']
                            ?? null,

                        'kata_kunci' =>
                            ($detail['top_terms'] ?? [])[$clusterId]
                            ?? ($detail['top_terms'] ?? [])[(string) $clusterId]
                            ?? [],
                    ];
                })
                ->values();

                return [
                    'nama_bidang' =>
                        $namaBidang,

                    'status' =>
                        $detail['status']
                        ?? 'gagal',

                    'alasan' =>
                        $detail['alasan']
                        ?? null,

                    'jumlah_data' =>
                        $detail['jumlah_data']
                        ?? 0,

                    'k_optimal' =>
                        $detail['k_optimal']
                        ?? null,

                    'silhouette_score' =>
                        $detail['silhouette_score']
                        ?? null,

                    'interpretasi' =>
                        $detail['interpretasi']
                        ?? null,

                    'per_cluster' =>
                        $perClusterBidang,
                ];
            })
            ->values();
        }

        return view('admin.dashboard', [

            // Ringkasan Data Master
            'totalUsers'       => User::count(),
            'totalKategori'    => KategoriLayanan::count(),
            'totalBidang'      => Bidang::count(),

            // Ringkasan Dataset & Analisis
            'totalDataset'     => Dataset::count(),
            'totalAnalisis'    => Analisis::count(),

            // Ringkasan Pengaduan
            'totalPengaduan'   => Pengaduan::count(),
            'pengaduanBaru'    => Pengaduan::where('status', 'Diajukan')->count(),
            'pengaduanProses'  => Pengaduan::where('status', 'Diproses')->count(),
            'pengaduanSelesai' => Pengaduan::where('status', 'Selesai')->count(),

            // Aktivitas Terbaru
            'pengaduanTerbaru' => Pengaduan::with([
                'user',
                'kategori',
            ])
            ->latest('tanggal_pengaduan')
            ->take(5)
            ->get(),

            // Dashboard Analisis (distribusi cluster)
            'analisisTerbaru'    => $analisisTerbaru,
            'distribusiCluster'  => $distribusiCluster,
            'grafikAnalisis'     => $grafikAnalisis,
            'analisisPerBidang'  => $analisisPerBidang,

        ]);
    }
}