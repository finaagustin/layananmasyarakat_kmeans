<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class PredictionService
{
    /**
     * Memprediksi cluster sebuah pengaduan baru dengan
     * menjalankan python/predict.py, menggunakan model
     * TF-IDF dan K-Means yang sudah dilatih pada proses
     * "Jalankan Analisis" (lihat DatasetController::analyze()).
     *
     * Mengembalikan null apabila prediksi tidak dapat
     * dilakukan (mis. Admin belum pernah menjalankan
     * analisis dataset, atau proses Python gagal). Ini
     * BUKAN error fatal bagi pengajuan pengaduan -- teks
     * pengaduan tetap disimpan, hanya kolom hasil_preprocessing,
     * cluster, dan nama_cluster yang tetap kosong dan dapat
     * diisi ulang nanti (mis. lewat proses re-prediksi).
     *
     * @return array{
     *     hasil_preprocessing: string,
     *     cluster: int,
     *     nama_cluster: string,
     * }|null
     */
    public function predict(string $judul, string $isiPengaduan): ?array
    {
        /*
        |--------------------------------------------------------------------------
        | Preprocessing (stemming Sastrawi) dapat memakan waktu,
        | jangan sampai dihentikan paksa oleh batas waktu PHP.
        |--------------------------------------------------------------------------
        */

        set_time_limit(0);

        $tempFile = null;

        try {

            /*
            |--------------------------------------------------------------------------
            | Python Executable
            |--------------------------------------------------------------------------
            */

            $python = 'C:\\Program Files\\Python311\\python.exe';

            if (!File::exists($python)) {
                Log::warning(
                    'PredictionService: Python executable tidak ditemukan: '
                    . $python
                );

                return null;
            }

            /*
            |--------------------------------------------------------------------------
            | Script Python
            |--------------------------------------------------------------------------
            */

            $script = base_path('python/predict.py');

            if (!File::exists($script)) {
                Log::warning(
                    'PredictionService: File python/predict.py tidak ditemukan.'
                );

                return null;
            }

            /*
            |--------------------------------------------------------------------------
            | Model Belum Dilatih
            |--------------------------------------------------------------------------
            |
            | Prediksi baru bisa dilakukan setelah Admin pernah
            | menjalankan analisis dataset historis minimal sekali,
            | karena model TF-IDF/K-Means disimpan pada:
            |
            | python/models/tfidf_vectorizer.pkl
            | python/models/kmeans_models.pkl
            |
            */

            $tfidfModel = base_path('python/models/tfidf_vectorizer.pkl');
            $kmeansModel = base_path('python/models/kmeans_models.pkl');

            if (!File::exists($tfidfModel) || !File::exists($kmeansModel)) {
                Log::info(
                    'PredictionService: Model TF-IDF/K-Means belum tersedia, '
                    . 'prediksi cluster dilewati untuk pengaduan ini.'
                );

                return null;
            }

            /*
            |--------------------------------------------------------------------------
            | Tulis Teks Pengaduan ke File Sementara
            |--------------------------------------------------------------------------
            |
            | predict.py membaca teks (judul + isi digabung) dari
            | sebuah file agar aman dari karakter kutip, baris baru,
            | dsb. yang tidak aman apabila dikirim sebagai argument
            | command line langsung.
            |
            */

            $tempDirectory = base_path('python/temp');

            File::ensureDirectoryExists($tempDirectory);

            $tempFile = $tempDirectory
                . DIRECTORY_SEPARATOR
                . 'pengaduan_'
                . Str::uuid()
                . '.txt';

            $teksGabungan = trim($judul . "\n" . $isiPengaduan);

            File::put($tempFile, $teksGabungan);

            /*
            |--------------------------------------------------------------------------
            | Environment Python
            |--------------------------------------------------------------------------
            |
            | Mengikuti pola yang sama dengan
            | DatasetController::runPythonAnalysis(), supaya proses
            | Python selalu mendapat environment Windows yang valid
            | terlepas dari terminal yang menjalankan Laravel.
            |
            */

            $pythonEnvironment = [
                'PYTHONNOUSERSITE' => '1',
            ];

            $criticalWindowsEnv = [
                'SystemRoot'  => 'C:\\Windows',
                'windir'      => 'C:\\Windows',
                'SystemDrive' => 'C:',
                'TEMP'        => sys_get_temp_dir(),
                'TMP'         => sys_get_temp_dir(),
                'USERPROFILE' => getenv('USERPROFILE') ?: 'C:\\Windows\\System32\\config\\systemprofile',
                'COMSPEC'     => 'C:\\Windows\\System32\\cmd.exe',
                'PATH'        => 'C:\\Windows\\System32;C:\\Windows;C:\\Windows\\System32\\Wbem',
            ];

            foreach ($criticalWindowsEnv as $key => $fallback) {
                $current = getenv($key);

                $pythonEnvironment[$key] =
                    ($current !== false && $current !== '')
                        ? $current
                        : $fallback;
            }

            $systemPath = getenv('PATH') ?: getenv('Path');

            if ($systemPath) {
                $pythonEnvironment['PATH'] =
                    $systemPath
                    . ';'
                    . $pythonEnvironment['PATH'];
            }

            /*
            |--------------------------------------------------------------------------
            | Command Prediksi
            |--------------------------------------------------------------------------
            |
            | Format:
            |
            | python predict.py <path_file_teks_pengaduan>
            |
            */

            $process = new Process([
                $python,
                $script,
                $tempFile,
            ]);

            $process->setWorkingDirectory(base_path('python'));

            $process->setEnv($pythonEnvironment);

            $process->setTimeout(60);

            $process->run();

            $output = trim($process->getOutput());

            if ($output === '') {
                Log::warning(
                    'PredictionService: predict.py tidak menghasilkan output. '
                    . trim($process->getErrorOutput())
                );

                return null;
            }

            /*
            |--------------------------------------------------------------------------
            | Cari JSON Terakhir Pada Output
            |--------------------------------------------------------------------------
            */

            $lines = preg_split('/\R/', $output);

            $response = null;

            for ($i = count($lines) - 1; $i >= 0; $i--) {

                $candidate = trim($lines[$i]);

                if ($candidate === '') {
                    continue;
                }

                $decoded = json_decode($candidate, true);

                if (is_array($decoded) && isset($decoded['status'])) {
                    $response = $decoded;
                    break;
                }
            }

            if ($response === null) {
                Log::warning(
                    'PredictionService: Output predict.py bukan JSON yang valid: '
                    . $output
                );

                return null;
            }

            if ($response['status'] !== 'success') {
                Log::info(
                    'PredictionService: Prediksi cluster gagal - '
                    . ($response['message'] ?? 'Tidak diketahui.')
                );

                return null;
            }

            return [
                'hasil_preprocessing' => $response['hasil_preprocessing'],
                'cluster'             => $response['cluster'],
                'nama_cluster'        => $response['nama_cluster'],
            ];

        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Prediksi Gagal
            |--------------------------------------------------------------------------
            |
            | Kegagalan prediksi tidak boleh menggagalkan pengajuan
            | pengaduan itu sendiri, sehingga error dicatat pada log
            | dan null dikembalikan ke pemanggil.
            |
            */

            Log::error(
                'PredictionService: Terjadi kesalahan saat memprediksi cluster - '
                . $e->getMessage()
            );

            return null;

        } finally {

            /*
            |--------------------------------------------------------------------------
            | Hapus File Sementara
            |--------------------------------------------------------------------------
            */

            if ($tempFile && File::exists($tempFile)) {
                File::delete($tempFile);
            }
        }
    }
}