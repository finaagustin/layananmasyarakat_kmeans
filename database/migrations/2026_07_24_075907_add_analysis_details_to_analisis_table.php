<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan detail hasil analisis K-Means.
     */
    public function up(): void
    {
        Schema::table('analisis', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Jumlah Data
            |--------------------------------------------------------------------------
            */

            // Total seluruh data dari 4 file Excel
            $table->unsignedInteger('jumlah_data')
                ->nullable()
                ->after('dataset_id');

            // Total data yang masuk proses clustering
            $table->unsignedInteger('jumlah_data_clustering')
                ->nullable()
                ->after('jumlah_data');


            /*
            |--------------------------------------------------------------------------
            | Detail K per Kategori
            |--------------------------------------------------------------------------
            */

            // Hasil Elbow Method per kategori
            $table->json('k_optimal_detail')
                ->nullable()
                ->after('k_optimal');

            // Jumlah cluster final per kategori
            $table->json('jumlah_cluster_detail')
                ->nullable()
                ->after('k_optimal_detail');


            /*
            |--------------------------------------------------------------------------
            | Evaluasi
            |--------------------------------------------------------------------------
            */

            // Interpretasi Silhouette Score
            $table->string('interpretasi', 50)
                ->nullable()
                ->after('silhouette_score');
        });
    }


    /**
     * Mengembalikan struktur tabel seperti sebelumnya.
     */
    public function down(): void
    {
        Schema::table('analisis', function (Blueprint $table) {

            $table->dropColumn([
                'jumlah_data',
                'jumlah_data_clustering',
                'k_optimal_detail',
                'jumlah_cluster_detail',
                'interpretasi',
            ]);
        });
    }
};