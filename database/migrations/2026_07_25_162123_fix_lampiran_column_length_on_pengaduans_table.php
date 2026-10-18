<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Menjalankan migration.
     *
     * Kolom 'lampiran' sebelumnya hanya VARCHAR(50), padahal
     * path file yang disimpan Laravel (folder + nama file acak
     * 40 karakter + ekstensi, contoh:
     * "lampiran_pengaduan/yL1fhJdNzv4bs6baxIs4Ht0T6oXfOrtLwl2FUzAk.jpg")
     * selalu lebih dari 50 karakter, sehingga INSERT selalu
     * gagal dengan error:
     *
     * SQLSTATE[22001]: String data, right truncated:
     * 1406 Data too long for column 'lampiran'
     *
     * Diperbesar menjadi VARCHAR(255), ukuran standar untuk
     * menyimpan path file di Laravel.
     *
     * Menggunakan DB::statement (raw SQL) alih-alih
     * Schema::table()->change() supaya tidak perlu
     * menambah dependency doctrine/dbal.
     */
    public function up(): void
    {
        DB::statement(
            'ALTER TABLE pengaduans '
            . 'MODIFY lampiran VARCHAR(255) NULL'
        );
    }

    /**
     * Membatalkan migration.
     */
    public function down(): void
    {
        DB::statement(
            'ALTER TABLE pengaduans '
            . 'MODIFY lampiran VARCHAR(50) NULL'
        );
    }
};
