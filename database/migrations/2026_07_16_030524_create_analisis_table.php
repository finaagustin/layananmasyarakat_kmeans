<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migration.
     */
    public function up(): void
    {
        Schema::create('analisis', function (Blueprint $table) {

            // Primary Key
            $table->bigIncrements('analisis_id');

            /*
            |--------------------------------------------------------------------------
            | Foreign Key Dataset
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('dataset_id');

            $table->foreign('dataset_id')
                ->references('dataset_id')
                ->on('datasets')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Hasil Analisis K-Means
            |--------------------------------------------------------------------------
            */

            // Jumlah cluster yang dihasilkan
            $table->unsignedTinyInteger('jumlah_cluster');

            // Nilai K optimal
            $table->unsignedTinyInteger('k_optimal');

            // Nilai Silhouette Score
            $table->double(
                'silhouette_score',
                8,
                4
            );

            /*
            |--------------------------------------------------------------------------
            | Informasi Proses Analisis
            |--------------------------------------------------------------------------
            */

            // Lama proses analisis dalam detik
            $table->double(
                'durasi_proses',
                10,
                2
            )->nullable();

            // Status proses analisis
            $table->enum(
                'status_analisis',
                [
                    'Berhasil',
                    'Gagal',
                ]
            )->default('Berhasil');

            /*
            |--------------------------------------------------------------------------
            | Timestamp
            |--------------------------------------------------------------------------
            */

            $table->timestamps();
        });
    }

    /**
     * Menghapus tabel.
     */
    public function down(): void
    {
        Schema::dropIfExists('analisis');
    }
};