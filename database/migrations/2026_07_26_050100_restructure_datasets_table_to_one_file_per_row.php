<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migration.
     *
     * LATAR BELAKANG
     * ---------------
     * Sebelumnya tabel `datasets` mengasumsikan dataset historis
     * SELALU diunggah sekaligus 4 file dalam satu baris (kolom
     * file_maret, file_april, file_mei, file_pemakaman). Ke
     * depan, Admin akan menambah dataset historis secara
     * BERTAHAP (1 file Excel per upload, kapan pun), sehingga
     * struktur 4-kolom-tetap ini tidak lagi sesuai.
     *
     * Migration ini mengubah `datasets` menjadi SATU FILE PER
     * BARIS (dataset_id, periode, file_excel) -- sesuai rancangan
     * awal pada dokumen Basis Data skripsi -- supaya Admin bisa
     * menambah dataset baru kapan saja tanpa terkunci ke 4 file.
     *
     * TIDAK ADA FILE FISIK YANG DIUBAH/DIHAPUS
     * ------------------------------------------
     * Migration ini HANYA merestrukturisasi metadata di database.
     * File Excel yang sudah tersimpan di
     * public/uploads/dataset/... dan python/input/... TIDAK
     * disentuh sama sekali -- hanya cara mencatatnya di database
     * yang berubah, dari "1 baris = 4 file" menjadi "1 baris = 1
     * file". Setiap file lama yang sudah ada akan otomatis
     * menjadi satu baris dataset baru sendiri-sendiri, tetap
     * tersimpan dan tetap ikut dianalisis.
     *
     * Baris `analisis` yang sudah ada juga ikut disesuaikan:
     * dataset_id diarahkan ke salah satu baris dataset baru
     * (dataset pemicu), dan dataset_ids_terpakai diisi dengan
     * seluruh dataset baru yang berasal dari baris dataset lama
     * tersebut, supaya riwayat analisis lama tidak kehilangan
     * jejak dataset apa saja yang dipakai.
     *
     * Tabel lama tetap disimpan dengan nama
     * `datasets_historis_sebelum_migrasi` (tidak dihapus) sebagai
     * cadangan, karena ukurannya kecil (hanya metadata, bukan
     * file).
     */
    public function up(): void
    {
        // Jika sudah pernah dijalankan sebelumnya (mis. struktur
        // sudah 1 file per baris), tidak perlu diulang.
        if (Schema::hasColumn('datasets', 'file_excel')) {
            return;
        }

        /*
        |----------------------------------------------------------
        | 1. Lepas foreign key "analisis.dataset_id" sementara
        |----------------------------------------------------------
        | Diperlukan karena tabel `datasets` akan dibuat ulang,
        | dan MySQL melarang mengubah/drop tabel yang masih
        | direferensikan foreign key.
        */
        Schema::table('analisis', function (Blueprint $table) {
            $table->dropForeign(['dataset_id']);
        });

        /*
        |----------------------------------------------------------
        | 2. Ganti nama tabel lama sebagai cadangan
        |----------------------------------------------------------
        */
        Schema::rename('datasets', 'datasets_historis_sebelum_migrasi');

        /*
        |----------------------------------------------------------
        | 3. Buat tabel `datasets` baru: satu file per baris
        |----------------------------------------------------------
        */
        Schema::create('datasets', function (Blueprint $table) {

            $table->bigIncrements('dataset_id');

            // Label periode / bulan dataset ini, mis. "Juni 2026".
            $table->string('periode', 100);

            // Nama file Excel dataset historis (1 file).
            $table->string('file_excel', 255);

            $table->timestamps();
        });

        /*
        |----------------------------------------------------------
        | 4. Pindahkan data lama: 1 baris lama -> sampai 4 baris baru
        |----------------------------------------------------------
        */

        $datasetLama = DB::table('datasets_historis_sebelum_migrasi')->get();

        // Memetakan dataset_id lama -> daftar dataset_id baru
        // hasil pemecahannya, dipakai untuk memperbarui tabel
        // `analisis` pada langkah berikutnya.
        $petaIdLama = [];

        // Label setiap kolom file lama, dipakai sebagai akhiran
        // periode pada baris baru supaya tetap mudah dikenali
        // file yang mana.
        $labelFile = [
            'file_maret'     => 'Dataset Maret',
            'file_april'     => 'Dataset April',
            'file_mei'       => 'Dataset Mei',
            'file_pemakaman' => 'Dataset Pemakaman',
        ];

        foreach ($datasetLama as $baris) {

            $idBaruUntukBarisIni = [];

            foreach ($labelFile as $kolom => $label) {

                $namaFile = $baris->{$kolom} ?? null;

                if (!$namaFile) {
                    continue;
                }

                $idBaru = DB::table('datasets')->insertGetId([
                    'periode' =>
                        $baris->periode . ' - ' . $label,

                    'file_excel' =>
                        $namaFile,

                    'created_at' =>
                        $baris->created_at,

                    'updated_at' =>
                        $baris->updated_at,
                ]);

                $idBaruUntukBarisIni[] = $idBaru;
            }

            $petaIdLama[$baris->dataset_id] = $idBaruUntukBarisIni;
        }

        /*
        |----------------------------------------------------------
        | 5. Sesuaikan baris `analisis` yang sudah ada
        |----------------------------------------------------------
        | dataset_id diarahkan ke dataset baru terakhir dari baris
        | lama tersebut (dataset pemicu), dan dataset_ids_terpakai
        | mencatat seluruh dataset baru yang berasal dari baris
        | lama itu.
        */

        foreach ($petaIdLama as $idLama => $idBaruSemua) {

            if (empty($idBaruSemua)) {
                continue;
            }

            DB::table('analisis')
                ->where('dataset_id', $idLama)
                ->update([
                    'dataset_id' =>
                        end($idBaruSemua),

                    'dataset_ids_terpakai' =>
                        json_encode($idBaruSemua),
                ]);
        }

        /*
        |----------------------------------------------------------
        | 6. Pasang kembali foreign key ke tabel `datasets` baru
        |----------------------------------------------------------
        */
        Schema::table('analisis', function (Blueprint $table) {
            $table->foreign('dataset_id')
                ->references('dataset_id')
                ->on('datasets')
                ->cascadeOnDelete();
        });
    }

    /**
     * Membatalkan migration.
     *
     * Mengembalikan tabel `datasets` seperti semula dari cadangan
     * `datasets_historis_sebelum_migrasi`. Perubahan pada baris
     * `analisis` (dataset_id / dataset_ids_terpakai) TIDAK
     * dikembalikan otomatis karena pemetaan 1 baris lama <-> banyak
     * baris baru tidak bisa dibalik dengan pasti apabila sudah ada
     * analisis baru yang dibuat setelah migration ini berjalan.
     */
    public function down(): void
    {
        if (!Schema::hasTable('datasets_historis_sebelum_migrasi')) {
            return;
        }

        Schema::table('analisis', function (Blueprint $table) {
            $table->dropForeign(['dataset_id']);
        });

        Schema::dropIfExists('datasets');

        Schema::rename('datasets_historis_sebelum_migrasi', 'datasets');

        Schema::table('analisis', function (Blueprint $table) {
            $table->foreign('dataset_id')
                ->references('dataset_id')
                ->on('datasets')
                ->cascadeOnDelete();
        });
    }
};
