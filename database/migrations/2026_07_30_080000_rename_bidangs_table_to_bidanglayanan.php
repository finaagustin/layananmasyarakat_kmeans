<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Mengganti nama tabel "bidangs" menjadi "bidanglayanan".
     * Struktur kolom, data, dan relasi foreign key tidak berubah,
     * hanya nama tabelnya saja.
     */
    public function up(): void
    {
        Schema::rename('bidangs', 'bidanglayanan');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('bidanglayanan', 'bidangs');
    }
};
