<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Menambahkan kolom bidang_id pada kategori_layanans sebagai
     * dasar penentuan bidang secara otomatis. Setiap kategori
     * layanan dipetakan ke satu bidang tertentu, sehingga saat
     * masyarakat memilih kategori pada form pengaduan, sistem
     * dapat langsung menentukan bidang tujuan tanpa campur
     * tangan Admin.
     */
    public function up(): void
    {
        Schema::table('kategori_layanans', function (Blueprint $table) {

            $table->unsignedBigInteger('bidang_id')
                ->nullable()
                ->after('deskripsi');

            $table->foreign('bidang_id')
                ->references('bidang_id')
                ->on('bidangs')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kategori_layanans', function (Blueprint $table) {

            $table->dropForeign(['bidang_id']);
            $table->dropColumn('bidang_id');
        });
    }
};
