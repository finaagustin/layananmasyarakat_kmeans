<?php

namespace App\Console\Commands;

use App\Models\Pengaduan;
use App\Services\PredictionService;
use Illuminate\Console\Command;

class RePredictPengaduanCluster extends Command
{
    /**
     * Nama dan signature command.
     *
     * Tambahkan opsi --all untuk memprediksi ulang SEMUA
     * pengaduan (termasuk yang sudah punya cluster), bukan
     * hanya yang cluster-nya masih kosong.
     *
     * @var string
     */
    protected $signature = 'pengaduan:re-predict {--all : Prediksi ulang seluruh pengaduan, bukan hanya yang cluster-nya kosong}';

    /**
     * Deskripsi command.
     *
     * @var string
     */
    protected $description = 'Memprediksi ulang cluster (TF-IDF + K-Means) untuk pengaduan yang cluster-nya masih kosong';

    /**
     * Jalankan command.
     */
    public function handle(PredictionService $predictionService): int
    {
        $query = Pengaduan::query();

        if (!$this->option('all')) {
            $query->whereNull('cluster');
        }

        $pengaduans = $query->get();

        if ($pengaduans->isEmpty()) {

            $this->info(
                'Tidak ada pengaduan yang perlu diprediksi ulang.'
            );

            return self::SUCCESS;
        }

        $this->info(
            'Memprediksi ulang cluster untuk '
            . $pengaduans->count()
            . ' pengaduan...'
        );

        $bar = $this->output->createProgressBar(
            $pengaduans->count()
        );

        $bar->start();

        $berhasil = 0;

        $gagal = 0;

        foreach ($pengaduans as $pengaduan) {

            $prediksi = $predictionService->predict(
                $pengaduan->judul,
                $pengaduan->isi_pengaduan
            );

            if ($prediksi === null) {

                $gagal++;

            } else {

                $pengaduan->update([
                    'hasil_preprocessing' => $prediksi['hasil_preprocessing'],
                    'cluster'             => $prediksi['cluster'],
                    'nama_cluster'        => $prediksi['nama_cluster'],
                ]);

                $berhasil++;
            }

            $bar->advance();
        }

        $bar->finish();

        $this->newLine(2);

        $this->info(
            "Selesai. Berhasil: {$berhasil}, Gagal: {$gagal}."
        );

        if ($gagal > 0) {

            $this->warn(
                'Pengaduan yang gagal biasanya karena model '
                . 'TF-IDF/K-Means belum tersedia. Jalankan '
                . '"Jalankan Analisis" pada dataset historis '
                . 'terlebih dahulu, lalu ulangi command ini.'
            );
        }

        return self::SUCCESS;
    }
}