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
        Schema::create('status_logs', function (Blueprint $table) {

            // Primary Key
            $table->bigIncrements('status_log_id');

            // Foreign Key ke tabel pengaduans
            $table->unsignedBigInteger('pengaduan_id');

            $table->foreign('pengaduan_id')
                ->references('pengaduan_id')
                ->on('pengaduans')
                ->cascadeOnDelete();

            // Status layanan
            $table->enum('status', [
                'Diajukan',
                'Diproses',
                'Selesai'
            ]);

            // Catatan perubahan status
            $table->text('keterangan')
                ->nullable();

            // created_at dan updated_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_logs');
    }
};