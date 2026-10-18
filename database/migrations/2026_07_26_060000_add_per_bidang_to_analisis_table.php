<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migration.
     *
     * Selain hasil analisis gabungan (seluruh data sekaligus,
     * sudah tersimpan lewat kolom jumlah_cluster, k_optimal,
     * silhouette_score, per_cluster, top_terms, dst), dashboard
     * kini juga menampilkan hasil analisis K-Means yang
     * dijalankan TERPISAH untuk tiap Bidang (Tata Ruang,
     * Bangunan Gedung, Pemakaman, Lainnya) -- lihat
     * python/engine/clustering.py (run_clustering_per_bidang).
     *
     * Kolom ini menyimpan hasil lengkap analisis per Bidang
     * tersebut (jumlah_data, k_optimal, silhouette_score,
     * interpretasi, top_terms, per_cluster per Bidang), agar
     * halaman Dashboard dapat menampilkannya tanpa perlu
     * menjalankan ulang proses Python.
     */
    public function up(): void
    {
        Schema::table('analisis', function (Blueprint $table) {

            if (!Schema::hasColumn('analisis', 'per_bidang')) {
                $table->json('per_bidang')
                    ->nullable()
                    ->after('per_cluster');
            }

        });
    }

    /**
     * Membatalkan migration.
     */
    public function down(): void
    {
        Schema::table('analisis', function (Blueprint $table) {

            if (Schema::hasColumn('analisis', 'per_bidang')) {
                $table->dropColumn('per_bidang');
            }

        });
    }
};
