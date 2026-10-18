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
        Schema::create('datasets', function (Blueprint $table) {

            // Primary Key
            $table->bigIncrements('dataset_id');

            // Periode dataset
            $table->string('periode', 50);

            // Dataset SIPETRUK / FO Maret
            $table->string('file_maret', 255);

            // Dataset SIPETRUK / FO April
            $table->string('file_april', 255);

            // Dataset SIPETRUK / FO Mei
            $table->string('file_mei', 255);

            // Dataset pelayanan pemakaman
            $table->string('file_pemakaman', 255);

            // Timestamp
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('datasets');
    }
};