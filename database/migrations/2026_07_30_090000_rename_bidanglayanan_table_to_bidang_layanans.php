<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Mengganti nama tabel "bidanglayanan" menjadi "bidang_layanans"
     * (mengikuti konvensi penamaan tabel Laravel: snake_case jamak).
     * Struktur kolom, data, dan relasi foreign key tidak berubah,
     * hanya nama tabelnya saja.
     */
    public function up(): void
    {
        Schema::rename('bidanglayanan', 'bidang_layanans');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('bidang_layanans', 'bidanglayanan');
    }
};
