<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Menambahkan kolom "remember_token" pada tabel users.
     * Kolom ini dibutuhkan oleh alur reset password bawaan
     * Laravel (Password::reset) untuk mengganti token sesi
     * setiap kali password berhasil diperbarui. Tanpa kolom
     * ini, fitur "Lupa Password" akan gagal (500 error) saat
     * menyimpan password baru.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->rememberToken();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropColumn('remember_token');
        });
    }
};
