<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migration.
     *
     * python/analisis.py (fungsi save_grafik_dashboard) sudah
     * lama menghasilkan 3 grafik dashboard (Elbow Method,
     * K-Means, Silhouette Plot) beserta 5 kata kunci dominan
     * per cluster (top_terms), tetapi field "grafik" dan
     * "top_terms" pada response JSON tidak pernah disimpan ke
     * database -- sehingga halaman Dashboard tidak pernah bisa
     * menampilkannya.
     *
     * Kolom ini menyimpan:
     * - grafik: path relatif ke 3 gambar grafik
     *   (storage/analisis/{dataset_id}/grafik_*.png)
     * - top_terms: 5 kata kunci dominan per cluster, dipakai
     *   untuk penjelasan cluster yang mudah dipahami orang awam
     *
     * Dicek dulu dengan hasColumn() supaya migration ini aman
     * dijalankan meski salah satu kolom sudah pernah dibuat
     * sebelumnya (misalnya dari percobaan migration terdahulu).
     */
    public function up(): void
    {
        Schema::table('analisis', function (Blueprint $table) {

            if (!Schema::hasColumn('analisis', 'top_terms')) {
                $table->json('top_terms')
                    ->nullable()
                    ->after('per_cluster');
            }

            if (!Schema::hasColumn('analisis', 'grafik')) {
                $table->json('grafik')
                    ->nullable()
                    ->after(
                        Schema::hasColumn('analisis', 'top_terms')
                            ? 'top_terms'
                            : 'per_cluster'
                    );
            }

        });
    }

    /**
     * Membatalkan migration.
     */
    public function down(): void
    {
        Schema::table('analisis', function (Blueprint $table) {

            $columns = array_filter([
                Schema::hasColumn('analisis', 'top_terms') ? 'top_terms' : null,
                Schema::hasColumn('analisis', 'grafik') ? 'grafik' : null,
            ]);

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }

        });
    }
};
