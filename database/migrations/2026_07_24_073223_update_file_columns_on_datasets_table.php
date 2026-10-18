<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan empat kolom file dataset
     * dan menghapus kolom file_excel lama.
     */
    public function up(): void
    {
        Schema::table('datasets', function (Blueprint $table) {

            // Hapus kolom file Excel lama
            $table->dropColumn('file_excel');

            // File dataset yang digunakan dalam analisis
            $table->string('file_maret', 255);
            $table->string('file_april', 255);
            $table->string('file_mei', 255);
            $table->string('file_pemakaman', 255);
        });
    }

    /**
     * Mengembalikan struktur tabel seperti semula.
     */
    public function down(): void
    {
        Schema::table('datasets', function (Blueprint $table) {

            // Hapus empat kolom dataset
            $table->dropColumn([
                'file_maret',
                'file_april',
                'file_mei',
                'file_pemakaman',
            ]);

            // Kembalikan kolom lama
            $table->string('file_excel', 255);
        });
    }
};