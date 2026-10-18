<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Analisis;
use App\Models\Dataset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class DatasetController extends Controller
{
    /**
     * Menampilkan daftar dataset.
     */
    public function index()
    {
        $datasets = Dataset::latest('dataset_id')->paginate(10);

        return view('admin.dataset.index', compact('datasets'));
    }

    /**
     * Menampilkan form upload dataset.
     */
    public function create()
    {
        return view('admin.dataset.create');
    }

    /**
     * Upload satu file dataset historis baru, lalu menjalankan
     * analisis ulang terhadap SELURUH dataset historis yang ada
     * (dataset lama + dataset baru ini digabung menjadi satu
     * korpus, karena TF-IDF dan K-Means harus dihitung ulang dari
     * awal atas seluruh data setiap kali ada data baru).
     */
    public function store(Request $request)
    {
        $request->validate(
            [
                'periode' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'file_excel' => [
                    'required',
                    'file',
                    'mimes:xlsx,xls',
                    'max:10240',
                ],
            ],
            [
                'periode.required' =>
                    'Periode/label dataset wajib diisi.',

                'periode.max' =>
                    'Periode maksimal 100 karakter.',

                'file_excel.required' =>
                    'File dataset wajib dipilih.',

                'file_excel.mimes' =>
                    'File dataset harus berformat XLSX atau XLS.',

                'file_excel.max' =>
                    'Ukuran file dataset maksimal 10 MB.',
            ]
        );

        DB::beginTransaction();

        $laravelPath = null;
        $pythonPath  = null;

        try {
            /*
            |--------------------------------------------------------------------------
            | Folder Upload Laravel
            |--------------------------------------------------------------------------
            */

            $safePeriode = preg_replace(
                '/[^A-Za-z0-9_\-]/',
                '_',
                $request->periode
            );

            $uploadFolder = public_path(
                'uploads/dataset/' . $safePeriode
            );

            File::ensureDirectoryExists($uploadFolder);

            /*
            |--------------------------------------------------------------------------
            | Folder Input Python
            |--------------------------------------------------------------------------
            */

            $pythonInput = base_path('python/input');

            File::ensureDirectoryExists($pythonInput);

            /*
            |--------------------------------------------------------------------------
            | Simpan File
            |--------------------------------------------------------------------------
            */

            $file = $request->file('file_excel');

            $originalName = preg_replace(
                '/[^A-Za-z0-9._\-]/',
                '_',
                $file->getClientOriginalName()
            );

            $fileName =
                uniqid('dataset_', true)
                . '_'
                . $originalName;

            $file->move(
                $uploadFolder,
                $fileName
            );

            $laravelPath =
                $uploadFolder
                . DIRECTORY_SEPARATOR
                . $fileName;

            if (!File::exists($laravelPath)) {
                throw new \Exception(
                    'File gagal disimpan: ' . $fileName
                );
            }

            /*
            | Salin ke python/input
            */

            $pythonPath =
                $pythonInput
                . DIRECTORY_SEPARATOR
                . $fileName;

            if (!File::copy($laravelPath, $pythonPath)) {
                throw new \Exception(
                    'File gagal disalin ke folder Python: '
                    . $fileName
                );
            }

            if (!File::exists($pythonPath)) {
                throw new \Exception(
                    'File input Python tidak ditemukan: '
                    . $fileName
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Simpan Dataset (satu baris = satu file)
            |--------------------------------------------------------------------------
            */

            $dataset = Dataset::create([
                'periode' =>
                    $request->periode,

                'file_excel' =>
                    $fileName,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Kumpulkan SELURUH Dataset Historis (lama + baru)
            |--------------------------------------------------------------------------
            |
            | TF-IDF dan K-Means tidak bisa dilatih ulang secara
            | bertahap (incremental) -- setiap kali dijalankan,
            | keduanya harus dihitung ulang dari awal atas SELURUH
            | korpus data historis, bukan cuma file yang baru saja
            | diunggah. Jika hanya file baru yang dianalisis,
            | kosakata TF-IDF dan hasil cluster akan berubah total
            | dan tidak lagi sebanding dengan analisis sebelumnya.
            |
            */

            $seluruhDataset = $this->kumpulkanPathDatasetHistoris();

            /*
            |--------------------------------------------------------------------------
            | Jalankan Python
            |--------------------------------------------------------------------------
            */

            $response = $this->runPythonAnalysis(
                $dataset,
                $seluruhDataset['paths']
            );

            /*
            |--------------------------------------------------------------------------
            | Simpan Hasil Analisis
            |--------------------------------------------------------------------------
            */

            $this->saveAnalysisResult(
                $dataset,
                $seluruhDataset['ids'],
                $response
            );

            DB::commit();

            return redirect()
                ->route('admin.dataset.index')
                ->with(
                    'success',
                    'Dataset baru berhasil diunggah. Analisis '
                    . 'dijalankan ulang atas seluruh '
                    . count($seluruhDataset['ids'])
                    . ' dataset historis (termasuk yang baru '
                    . 'ini). Total data: '
                    . ($response['jumlah_data'] ?? '-')
                    . ', data clustering: '
                    . ($response['jumlah_data_clustering'] ?? '-')
                    . ', K optimal: '
                    . ($response['k_optimal'] ?? '-')
                    . ', Silhouette Score: '
                    . ($response['silhouette_score'] ?? '-')
                    . '.'
                );

        } catch (\Throwable $e) {
            DB::rollBack();

            /*
            |--------------------------------------------------------------------------
            | Hapus file jika proses gagal
            |--------------------------------------------------------------------------
            */

            if ($laravelPath && File::exists($laravelPath)) {
                File::delete($laravelPath);
            }

            if ($pythonPath && File::exists($pythonPath)) {
                File::delete($pythonPath);
            }

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Upload dan analisis gagal: '
                    . $e->getMessage()
                );
        }
    }

    /**
     * Menjalankan analisis ulang terhadap SELURUH dataset
     * historis yang telah diunggah (bukan hanya yang terbaru),
     * karena TF-IDF dan K-Means harus dihitung ulang dari awal
     * atas seluruh korpus data setiap kali dijalankan.
     */
    public function analyze()
    {
        try {
            $seluruhDataset = $this->kumpulkanPathDatasetHistoris();

            if (empty($seluruhDataset['ids'])) {
                throw new \Exception(
                    'Belum ada dataset yang diunggah. '
                    . 'Silakan upload dataset terlebih dahulu.'
                );
            }

            // Dataset "pemicu" -- dataset yang paling baru
            // diunggah -- dicatat sebagai referensi utama pada
            // baris analisis, meski seluruh dataset historis
            // ikut digabungkan dalam analisis ini.
            $datasetPemicu = Dataset::latest('dataset_id')->firstOrFail();

            $response = $this->runPythonAnalysis(
                $datasetPemicu,
                $seluruhDataset['paths']
            );

            DB::transaction(
                function () use (
                    $datasetPemicu,
                    $seluruhDataset,
                    $response
                ) {
                    $this->saveAnalysisResult(
                        $datasetPemicu,
                        $seluruhDataset['ids'],
                        $response
                    );
                }
            );

            return redirect()
                ->route('admin.dataset.index')
                ->with(
                    'success',
                    'Analisis K-Means berhasil dijalankan atas '
                    . count($seluruhDataset['ids'])
                    . ' dataset historis. Total data: '
                    . ($response['jumlah_data'] ?? '-')
                    . ', data clustering: '
                    . ($response['jumlah_data_clustering'] ?? '-')
                    . ', K optimal: '
                    . ($response['k_optimal'] ?? '-')
                    . ', Silhouette Score: '
                    . ($response['silhouette_score'] ?? '-')
                    . '.'
                );

        } catch (\Throwable $e) {
            report($e);

            return back()->with(
                'error',
                'Analisis gagal: '
                . $e->getMessage()
            );
        }
    }

    /**
     * Mengumpulkan path python/input/... dari SELURUH dataset
     * historis yang tersimpan di database (bukan hanya satu
     * dataset), untuk digabungkan menjadi satu korpus analisis.
     *
     * Mengembalikan ['paths' => [...], 'ids' => [...]].
     */
    private function kumpulkanPathDatasetHistoris(): array
    {
        $semuaDataset = Dataset::orderBy('dataset_id')->get();

        $paths = [];
        $ids   = [];

        foreach ($semuaDataset as $ds) {

            if (!$ds->file_excel) {
                continue;
            }

            $path = base_path(
                'python/input/' . $ds->file_excel
            );

            if (!File::exists($path)) {
                throw new \Exception(
                    'File dataset tidak ditemukan: '
                    . $ds->file_excel
                    . ' (periode: '
                    . $ds->periode
                    . '). File mungkin terhapus dari server -- '
                    . 'hapus baris dataset ini atau unggah '
                    . 'ulang filenya.'
                );
            }

            $paths[] = $path;
            $ids[]   = $ds->dataset_id;
        }

        return [
            'paths' => $paths,
            'ids'   => $ids,
        ];
    }

    /**
     * Menjalankan python/analisis.py.
     */
    private function runPythonAnalysis(
        Dataset $dataset,
        array $datasetPaths
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Nonaktifkan Batas Waktu Eksekusi PHP
        |--------------------------------------------------------------------------
        |
        | Preprocessing (stemming) dan clustering (Elbow Method
        | menjalankan K-Means berulang kali) pada data yang cukup
        | banyak dapat memakan waktu lebih dari max_execution_time
        | default PHP (umumnya 60 detik).
        |
        | $process->setTimeout(null) pada Symfony Process hanya
        | menonaktifkan timeout milik Process itu sendiri, TIDAK
        | menonaktifkan batas waktu eksekusi PHP secara keseluruhan.
        | Tanpa baris ini, PHP akan menghentikan paksa request
        | (FatalError: Maximum execution time exceeded) meskipun
        | proses Python masih berjalan normal di background.
        |
        | Batas waktu dinonaktifkan HANYA untuk proses analisis ini,
        | tidak memengaruhi request lain pada aplikasi.
        |
        */

        set_time_limit(0);

        /*
        |--------------------------------------------------------------------------
        | Validasi Input
        |--------------------------------------------------------------------------
        */

        if (count($datasetPaths) < 1) {
            throw new \Exception(
                'Analisis membutuhkan minimal 1 file dataset.'
            );
        }

        foreach ($datasetPaths as $path) {
            if (!File::exists($path)) {
                throw new \Exception(
                    'File input Python tidak ditemukan: '
                    . $path
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Python Executable
        |--------------------------------------------------------------------------
        */

        $python = 'C:\\Program Files\\Python311\\python.exe';

        if (!File::exists($python)) {
            throw new \Exception(
                'Python executable tidak ditemukan: '
                . $python
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Script Python
        |--------------------------------------------------------------------------
        */

        $script = base_path('python/analisis.py');

        if (!File::exists($script)) {
            throw new \Exception(
                'File python/analisis.py tidak ditemukan.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Environment Python
        |--------------------------------------------------------------------------
        |
        | PYTHONNOUSERSITE=1 memastikan Laravel menggunakan library
        | Python global di:
        |
        | C:\Program Files\Python311\Lib\site-packages
        |
        | dan tidak mencampurkannya dengan package user AppData.
        |
        */

        $pythonEnvironment = [
            'PYTHONNOUSERSITE' => '1',
        ];

        /*
        |--------------------------------------------------------------------------
        | Variabel Windows Kritikal
        |--------------------------------------------------------------------------
        |
        | Beberapa terminal (Git Bash, VS Code integrated terminal, dsb.)
        | tidak meneruskan variabel environment Windows tertentu secara utuh
        | ke proses anak (child process). Tanpa SystemRoot/windir yang valid,
        | Windows gagal memuat provider Winsock (mswsock.dll dkk.) sehingga
        | Python akan gagal saat "import asyncio" dengan:
        |
        | OSError: [WinError 10106] The requested service provider
        | could not be loaded or initialized
        |
        | Variabel di bawah diteruskan secara eksplisit dengan fallback ke
        | nilai default Windows apabila tidak ditemukan pada environment
        | PHP saat ini, agar proses Python selalu mendapat environment
        | yang valid terlepas dari terminal yang menjalankan Laravel.
        |
        */

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

        /*
        |--------------------------------------------------------------------------
        | Gabungkan dengan PATH sistem asli (jika ada)
        |--------------------------------------------------------------------------
        |
        | Supaya python.exe tetap bisa menemukan DLL pendukung lain
        | (mis. dari Anaconda/venv) tanpa kehilangan direktori sistem inti.
        |
        */

        $systemPath = getenv('PATH') ?: getenv('Path');

        if ($systemPath) {
            $pythonEnvironment['PATH'] =
                $systemPath
                . ';'
                . $pythonEnvironment['PATH'];
        }

        /*
        |--------------------------------------------------------------------------
        | Tes Environment Python
        |--------------------------------------------------------------------------
        */

        $testProcess = new Process([
            $python,
            '-c',
            'import pandas, numpy, scipy, sklearn, joblib, openpyxl, asyncio, _overlapped; '
            . 'from sklearn.cluster import KMeans; '
            . 'from Sastrawi.Stemmer.StemmerFactory import StemmerFactory; '
            . 'print("LARAVEL_PYTHON_OK")',
        ]);

        $testProcess->setWorkingDirectory(base_path());

        $testProcess->setEnv($pythonEnvironment);

        $testProcess->setTimeout(60);

        $testProcess->run();

        if (!$testProcess->isSuccessful()) {
            $error = trim(
                $testProcess->getErrorOutput()
            );

            if ($error === '') {
                $error = trim(
                    $testProcess->getOutput()
                );
            }

            throw new \Exception(
                'Environment Python Laravel tidak valid. '
                . $error
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Command Analisis
        |--------------------------------------------------------------------------
        |
        | Format (jumlah file bersifat dinamis -- seluruh dataset
        | historis yang tersimpan digabungkan menjadi satu
        | korpus, bukan hanya 4 file tetap):
        |
        | python analisis.py
        |     dataset_id
        |     file1 [file2] [file3] ...
        |
        */

        $command = [
            $python,
            $script,
            (string) $dataset->dataset_id,
            ...$datasetPaths,
        ];

        /*
        |--------------------------------------------------------------------------
        | Jalankan Python
        |--------------------------------------------------------------------------
        */

        $process = new Process($command);

        $process->setWorkingDirectory(base_path());

        /*
        | Gunakan environment yang sama dengan proses test.
        */

        $process->setEnv($pythonEnvironment);

        /*
        | Analisis preprocessing, TF-IDF, K-Means dan Silhouette
        | dapat membutuhkan waktu sehingga timeout dinonaktifkan.
        */

        $process->setTimeout(null);

        $process->run();

        /*
        |--------------------------------------------------------------------------
        | Jika Python Gagal
        |--------------------------------------------------------------------------
        */

        if (!$process->isSuccessful()) {
            $errorOutput = trim(
                $process->getErrorOutput()
            );

            $standardOutput = trim(
                $process->getOutput()
            );

            if ($errorOutput !== '') {
                $message = $errorOutput;
            } elseif ($standardOutput !== '') {
                $message = $standardOutput;
            } else {
                $message =
                    'Proses analisis Python gagal '
                    . 'tanpa menghasilkan pesan error.';
            }

            throw new \Exception($message);
        }

        /*
        |--------------------------------------------------------------------------
        | Ambil Output Python
        |--------------------------------------------------------------------------
        */

        $output = trim(
            $process->getOutput()
        );

        if ($output === '') {
            throw new \Exception(
                'Python tidak menghasilkan output.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Cari JSON Terakhir
        |--------------------------------------------------------------------------
        |
        | analisis.py dapat menampilkan log sebelum JSON.
        | Laravel mengambil JSON valid terakhir dari stdout.
        |
        */

        $lines = preg_split(
            '/\R/',
            $output
        );

        $response = null;

        for (
            $i = count($lines) - 1;
            $i >= 0;
            $i--
        ) {
            $candidate = trim(
                $lines[$i]
            );

            if ($candidate === '') {
                continue;
            }

            $decoded = json_decode(
                $candidate,
                true
            );

            if (
                json_last_error() === JSON_ERROR_NONE
                && is_array($decoded)
            ) {
                $response = $decoded;
                break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Validasi JSON
        |--------------------------------------------------------------------------
        */

        if (!is_array($response)) {
            throw new \Exception(
                'Output JSON dari Python tidak valid. '
                . 'Output Python: '
                . mb_substr(
                    $output,
                    -2000
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validasi Status Python
        |--------------------------------------------------------------------------
        */

        if (
            ($response['status'] ?? null)
            !== 'success'
        ) {
            throw new \Exception(
                $response['message']
                ?? 'Proses analisis Python gagal.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validasi Jumlah File
        |--------------------------------------------------------------------------
        |
        | Jumlah file kini dinamis (seluruh dataset historis
        | digabungkan), sehingga tidak lagi divalidasi harus 4.
        | Validasi hanya memastikan Python memang memproses file
        | (jumlah_file > 0), jika field tersebut dikirim.
        |
        */

        if (
            isset($response['jumlah_file'])
            && (int) $response['jumlah_file'] < 1
        ) {
            throw new \Exception(
                'Python tidak memproses satu pun file dataset.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validasi Field Hasil
        |--------------------------------------------------------------------------
        */

        $requiredKeys = [
            'jumlah_data',
            'jumlah_data_clustering',
            'k_optimal',
            'jumlah_cluster',
            'silhouette_score',
        ];

        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $response)) {
                throw new \Exception(
                    'Hasil analisis Python tidak memiliki nilai '
                    . $key
                    . '.'
                );
            }
        }

        return $response;
    }

    /**
     * Menyimpan hasil analisis.
     *
     * $datasetIdsTerpakai berisi seluruh dataset_id yang
     * digabungkan pada analisis ini (bisa lebih dari satu, karena
     * dataset historis kini bertambah bertahap).
     */
    private function saveAnalysisResult(
        Dataset $dataset,
        array $datasetIdsTerpakai,
        array $response
    ): void {

        $requiredKeys = [
            'jumlah_data',
            'jumlah_data_clustering',
            'k_optimal',
            'jumlah_cluster',
            'silhouette_score',
        ];

        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $response)) {
                throw new \Exception(
                    "Hasil analisis Python "
                    . "tidak memiliki nilai {$key}."
                );
            }
        }

        $jumlahData =
            (int) $response['jumlah_data'];

        $jumlahDataClustering =
            (int) $response['jumlah_data_clustering'];

        $kOptimal =
            (int) $response['k_optimal'];

        $jumlahCluster =
            (int) $response['jumlah_cluster'];

        $silhouetteScore =
            (float) $response['silhouette_score'];

        $interpretasi =
            $response['interpretasi']
            ?? null;

        $duration =
            isset($response['durasi_detik'])
                ? (float) $response['durasi_detik']
                : null;

        $jumlahClusterDetail =
            $response['jumlah_cluster_detail']
            ?? [];

        $perCluster =
            $response['per_cluster']
            ?? [];

        // 5 kata kunci dominan per cluster (untuk penjelasan
        // di dashboard agar mudah dipahami orang awam).
        $topTerms =
            $response['top_terms']
            ?? [];

        // Path relatif 3 grafik dashboard (Elbow, K-Means,
        // Silhouette) yang sudah dibuat Python. Bisa null jika
        // pembuatan grafik gagal (lihat save_grafik_dashboard
        // di python/analisis.py).
        $grafik =
            $response['grafik']
            ?? null;

        // Hasil analisis K-Means terpisah per Bidang (Tata
        // Ruang, Bangunan Gedung, Pemakaman, Lainnya). Bersifat
        // pelengkap: jika Python belum/tidak mengirimkannya
        // (mis. versi python/analisis.py lama), dashboard tetap
        // dapat menampilkan hasil analisis gabungan seperti
        // biasa.
        $perBidang =
            $response['per_bidang']
            ?? [];

        if (!is_array($topTerms)) {
            $topTerms = [];
        }

        if (!is_array($grafik)) {
            $grafik = null;
        }

        if (!is_array($perBidang)) {
            $perBidang = [];
        }

        /*
        |--------------------------------------------------------------------------
        | Validasi Detail Cluster
        |--------------------------------------------------------------------------
        */

        if (!is_array($jumlahClusterDetail)) {
            throw new \Exception(
                'Detail jumlah data per cluster tidak valid.'
            );
        }

        if (!is_array($perCluster)) {
            throw new \Exception(
                'Detail per_cluster tidak valid.'
            );
        }

        $jumlahClusterDetail = array_map(
            'intval',
            $jumlahClusterDetail
        );

        /*
        |--------------------------------------------------------------------------
        | Validasi Jumlah Data
        |--------------------------------------------------------------------------
        */

        if ($jumlahData <= 0) {
            throw new \Exception(
                'Jumlah data hasil analisis tidak valid.'
            );
        }

        if (
            $jumlahData !==
            $jumlahDataClustering
        ) {
            throw new \Exception(
                'Jumlah data clustering tidak sama '
                . 'dengan jumlah dataset.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validasi Cluster
        |--------------------------------------------------------------------------
        */

        if (
            $kOptimal < 2
            || $jumlahCluster < 2
        ) {
            throw new \Exception(
                'K optimal dan jumlah cluster minimal harus 2.'
            );
        }

        if (
            $kOptimal !==
            $jumlahCluster
        ) {
            throw new \Exception(
                'K optimal tidak sama dengan '
                . 'jumlah cluster hasil K-Means.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validasi Detail Cluster
        |--------------------------------------------------------------------------
        */

        if (
            count($jumlahClusterDetail)
            !== $jumlahCluster
        ) {
            throw new \Exception(
                'Jumlah detail cluster tidak sesuai '
                . 'dengan jumlah cluster.'
            );
        }

        if (
            array_sum(
                array_values($jumlahClusterDetail)
            )
            !== $jumlahDataClustering
        ) {
            throw new \Exception(
                'Total data seluruh cluster tidak sama '
                . 'dengan jumlah data clustering.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validasi Silhouette Score
        |--------------------------------------------------------------------------
        */

        if (
            $silhouetteScore < -1
            || $silhouetteScore > 1
        ) {
            throw new \Exception(
                'Nilai Silhouette Score tidak valid.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Simpan Database
        |--------------------------------------------------------------------------
        */

        Analisis::create(
            [
                'dataset_id' =>
                    $dataset->dataset_id,

                'dataset_ids_terpakai' =>
                    $datasetIdsTerpakai,

                'jumlah_data' =>
                    $jumlahData,

                'jumlah_data_clustering' =>
                    $jumlahDataClustering,

                'jumlah_cluster' =>
                    $jumlahCluster,

                'k_optimal' =>
                    $kOptimal,

                'k_optimal_detail' =>
                    null,

                'jumlah_cluster_detail' =>
                    $jumlahClusterDetail,

                'per_cluster' =>
                    $perCluster,

                'per_bidang' =>
                    $perBidang,

                'top_terms' =>
                    $topTerms,

                'grafik' =>
                    $grafik,

                'silhouette_score' =>
                    $silhouetteScore,

                'interpretasi' =>
                    $interpretasi,

                'durasi_proses' =>
                    $duration,

                'status_analisis' =>
                    'Berhasil',
            ]
        );
    }

    /**
     * Menghapus satu baris dataset (satu file) beserta filenya.
     *
     * Catatan: menghapus dataset TIDAK menghapus hasil analisis
     * yang sudah pernah tersimpan (riwayat analisis tetap ada di
     * tabel `analisis`, mencerminkan kondisi data pada saat
     * analisis itu dijalankan) -- kecuali dataset ini pernah
     * menjadi "dataset pemicu" langsung pada suatu baris analisis
     * (kolom dataset_id), yang akan ikut terhapus karena
     * cascadeOnDelete pada foreign key. Analisis berikutnya yang
     * dijalankan setelah dataset ini dihapus otomatis tidak lagi
     * menyertakan file ini.
     */
    public function destroy(Dataset $dataset)
    {
        try {
            DB::transaction(
                function () use ($dataset) {

                    $fileName = $dataset->file_excel;

                    $safePeriode = preg_replace(
                        '/[^A-Za-z0-9_\-]/',
                        '_',
                        $dataset->periode
                    );

                    if ($fileName) {

                        /*
                        | File Laravel
                        */

                        $laravelFile = public_path(
                            'uploads/dataset/'
                            . $safePeriode
                            . DIRECTORY_SEPARATOR
                            . $fileName
                        );

                        if (File::exists($laravelFile)) {
                            File::delete($laravelFile);
                        }

                        /*
                        | File Python
                        */

                        $pythonFile = base_path(
                            'python/input/'
                            . $fileName
                        );

                        if (File::exists($pythonFile)) {
                            File::delete($pythonFile);
                        }

                        /*
                        | Hapus folder jika kosong
                        */

                        $folder = public_path(
                            'uploads/dataset/'
                            . $safePeriode
                        );

                        if (
                            File::isDirectory($folder)
                            && count(File::files($folder)) === 0
                        ) {
                            File::deleteDirectory($folder);
                        }
                    }

                    /*
                    | Hapus Database
                    */

                    $dataset->delete();
                }
            );

            return redirect()
                ->route('admin.dataset.index')
                ->with(
                    'success',
                    'Dataset berhasil dihapus. Jalankan ulang '
                    . 'analisis agar hasil clustering tidak lagi '
                    . 'menyertakan data ini.'
                );

        } catch (\Throwable $e) {
            report($e);

            return back()->with(
                'error',
                'Dataset gagal dihapus: '
                . $e->getMessage()
            );
        }
    }
}