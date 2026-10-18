<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kategori_layanans', function (Blueprint $table) {

            // Primary Key
            $table->bigIncrements('kategori_layanan_id');

            // Nama kategori layanan
            $table->string('nama_kategori', 50)->unique();

            // Deskripsi kategori layanan
            $table->text('deskripsi')->nullable();

            // created_at dan updated_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategori_layanans');
    }
};