<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migration.
     *
     * Sebelumnya, satu baris `analisis` selalu terikat ke SATU
     * baris `dataset` saja (relasi 1:1 lewat dataset_id), karena
     * dataset historis diunggah sekaligus 4 file dalam satu form.
     *
     * Sekarang dataset historis bisa ditambah bertahap (1 file
     * per upload), dan setiap kali "Jalankan Analisis" ditekan,
     * sistem menggabungkan SEMUA file historis yang pernah
     * diunggah -- bukan cuma yang paling baru.
     *
     * Kolom `dataset_ids_terpakai` menyimpan daftar dataset_id apa
     * saja yang digabungkan pada analisis tersebut, sehingga tetap
     * bisa ditelusuri dataset mana saja yang membentuk hasil
     * analisis tertentu. Kolom `dataset_id` yang lama tetap
     * dipertahankan sebagai "dataset pemicu" (dataset yang baru
     * saja diunggah/dipilih saat analisis itu dijalankan).
     */
    public function up(): void
    {
        Schema::table('analisis', function (Blueprint $table) {

            if (!Schema::hasColumn('analisis', 'dataset_ids_terpakai')) {
                $table->json('dataset_ids_terpakai')
                    ->nullable()
                    ->after('dataset_id');
            }

        });
    }

    /**
     * Membatalkan migration.
     */
    public function down(): void
    {
        Schema::table('analisis', function (Blueprint $table) {

            if (Schema::hasColumn('analisis', 'dataset_ids_terpakai')) {
                $table->dropColumn('dataset_ids_terpakai');
            }

        });
    }
};
