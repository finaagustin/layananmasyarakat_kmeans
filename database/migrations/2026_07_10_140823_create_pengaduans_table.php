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
        Schema::create('pengaduans', function (Blueprint $table) {

            // Primary Key
            $table->bigIncrements('pengaduan_id');

            // Kode unik pengaduan
            $table->string('kode_pengaduan', 20)
                ->unique();

            /*
            |--------------------------------------------------------------------------
            | Foreign Key User
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('user_id');

            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Foreign Key Kategori Layanan
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger(
                'kategori_layanan_id'
            );

            $table->foreign('kategori_layanan_id')
                ->references('kategori_layanan_id')
                ->on('kategori_layanans')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Foreign Key Bidang
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('bidang_id')
                ->nullable();

            $table->foreign('bidang_id')
                ->references('bidang_id')
                ->on('bidangs')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Data Pengaduan
            |--------------------------------------------------------------------------
            */

            // Judul pengaduan
            $table->string('judul', 100);

            // Isi pengaduan
            $table->longText('isi_pengaduan');

            // Hasil preprocessing teks
            $table->longText('hasil_preprocessing')
                ->nullable();

            // Nomor cluster hasil analisis
            $table->unsignedTinyInteger('cluster')
                ->nullable();

            // Nama cluster hasil analisis
            $table->string('nama_cluster', 50)
                ->nullable();

            // Nama/path file lampiran
            $table->string('lampiran', 255)
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status Pengaduan
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [
                'Diajukan',
                'Diproses',
                'Selesai',
            ])->default('Diajukan');

            // Tanggal pengajuan
            $table->timestamp('tanggal_pengaduan')
                ->useCurrent();

            // created_at dan updated_at
            $table->timestamps();
        });
    }

    /**
     * Menghapus tabel.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaduans');
    }
};