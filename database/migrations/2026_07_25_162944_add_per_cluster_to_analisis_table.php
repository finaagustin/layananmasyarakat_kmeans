<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migration.
     *
     * Python (engine/evaluation.py) sudah menghasilkan detail
     * per cluster (nama_cluster, jumlah_data, silhouette_score,
     * interpretasi) lewat variabel "per_cluster", tapi
     * sebelumnya detail ini tidak pernah disimpan ke database
     * -- hanya jumlah data per cluster (tanpa nama) yang
     * tersimpan di kolom jumlah_cluster_detail.
     *
     * Kolom ini menyimpan detail lengkapnya agar dashboard
     * analisis bisa menampilkan distribusi cluster beserta
     * namanya, bukan cuma nomor cluster.
     */
    public function up(): void
    {
        Schema::table('analisis', function (Blueprint $table) {

            $table->json('per_cluster')
                ->nullable()
                ->after('jumlah_cluster_detail');

        });
    }

    /**
     * Membatalkan migration.
     */
    public function down(): void
    {
        Schema::table('analisis', function (Blueprint $table) {

            $table->dropColumn('per_cluster');

        });
    }
};
