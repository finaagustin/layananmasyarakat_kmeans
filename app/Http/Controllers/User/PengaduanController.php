<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\KategoriLayanan;
use App\Models\Pengaduan;
use App\Models\StatusLog;
use App\Services\PredictionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PengaduanController extends Controller
{
    /**
     * Menampilkan riwayat pengaduan milik user
     * yang sedang login.
     */
    public function index()
    {
        $pengaduans = Pengaduan::with([
                'kategori',
                'bidang',
            ])
            ->where('user_id', Auth::id())
            ->latest('tanggal_pengaduan')
            ->paginate(10);

        return view(
            'user.pengaduan.index',
            compact('pengaduans')
        );
    }

    /**
     * Menampilkan form pengajuan pengaduan.
     */
    public function create()
    {
        $kategoriLayanans = KategoriLayanan::orderBy(
            'nama_kategori',
            'asc'
        )->get();

        return view(
            'user.pengaduan.create',
            compact('kategoriLayanans')
        );
    }

    /**
     * Menyimpan pengaduan baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kategori_layanan_id' => [
                'required',
                'exists:kategori_layanans,kategori_layanan_id',
            ],

            'judul' => [
                'required',
                'string',
                'max:100',
            ],

            'isi_pengaduan' => [
                'required',
                'string',
            ],

            'lampiran' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:2048',
            ],
        ], [
            'kategori_layanan_id.required' =>
                'Kategori layanan wajib dipilih.',

            'kategori_layanan_id.exists' =>
                'Kategori layanan yang dipilih tidak valid.',

            'judul.required' =>
                'Judul pengaduan wajib diisi.',

            'judul.string' =>
                'Judul pengaduan harus berupa teks.',

            'judul.max' =>
                'Judul pengaduan maksimal 100 karakter.',

            'isi_pengaduan.required' =>
                'Isi pengaduan wajib diisi.',

            'isi_pengaduan.string' =>
                'Isi pengaduan harus berupa teks.',

            'lampiran.file' =>
                'Lampiran harus berupa file.',

            'lampiran.mimes' =>
                'Lampiran harus berformat JPG, JPEG, PNG, atau PDF.',

            'lampiran.max' =>
                'Ukuran lampiran maksimal 2 MB.',
        ]);

        $lampiran = null;

        try {

            /*
            |--------------------------------------------------------------------------
            | Upload Lampiran
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('lampiran')) {

                $lampiran = $request
                    ->file('lampiran')
                    ->store(
                        'lampiran_pengaduan',
                        'public'
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | Prediksi Cluster (TF-IDF + K-Means)
            |--------------------------------------------------------------------------
            |
            | Dijalankan SEBELUM transaksi database dibuka, karena
            | proses Python (preprocessing + prediksi) memerlukan
            | waktu dan tidak boleh menahan transaksi DB tetap
            | terbuka. Apabila prediksi gagal (mis. Admin belum
            | pernah menjalankan analisis dataset historis),
            | pengaduan tetap disimpan dengan cluster kosong.
            |
            */

            $prediksi = app(PredictionService::class)->predict(
                $validated['judul'],
                $validated['isi_pengaduan']
            );

            /*
            |--------------------------------------------------------------------------
            | Penentuan Bidang Otomatis
            |--------------------------------------------------------------------------
            |
            | Bidang ditentukan otomatis berdasarkan kategori layanan
            | yang dipilih masyarakat (lihat KategoriLayanan::bidang()
            | dan kriteria pemetaannya di KategoriLayananSeeder /
            | halaman Kelola Kategori Layanan). Admin tetap dapat
            | mengubah bidang secara manual di halaman Kelola
            | Pengaduan apabila diperlukan.
            |
            */

            $bidangId = KategoriLayanan::find(
                $validated['kategori_layanan_id']
            )?->bidang_id;

            /*
            |--------------------------------------------------------------------------
            | Simpan Pengaduan dan Status Awal
            |--------------------------------------------------------------------------
            */

            DB::transaction(function () use (
                $validated,
                $lampiran,
                $prediksi,
                $bidangId
            ) {

                $pengaduan = Pengaduan::create([
                    'kode_pengaduan' =>
                        $this->generateKodePengaduan(),

                    'user_id' =>
                        Auth::id(),

                    'kategori_layanan_id' =>
                        $validated['kategori_layanan_id'],

                    /*
                     * Ditentukan otomatis dari bidang terkait
                     * kategori layanan yang dipilih masyarakat.
                     * Bisa null jika kategori belum dipetakan ke
                     * bidang manapun; Admin tetap bisa mengubahnya
                     * secara manual di Kelola Pengaduan.
                     */
                    'bidang_id' =>
                        $bidangId,

                    'judul' =>
                        $validated['judul'],

                    'isi_pengaduan' =>
                        $validated['isi_pengaduan'],

                    /*
                     * Diisi oleh hasil prediksi TF-IDF + K-Means
                     * apabila model sudah tersedia. Tetap kosong
                     * apabila prediksi belum dapat dilakukan.
                     */
                    'hasil_preprocessing' =>
                        $prediksi['hasil_preprocessing'] ?? null,

                    'cluster' =>
                        $prediksi['cluster'] ?? null,

                    'nama_cluster' =>
                        $prediksi['nama_cluster'] ?? null,

                    'lampiran' =>
                        $lampiran,

                    'status' =>
                        'Diajukan',

                    'tanggal_pengaduan' =>
                        now(),
                ]);

                /*
                |--------------------------------------------------------------------------
                | Status Awal
                |--------------------------------------------------------------------------
                */

                StatusLog::create([
                    'pengaduan_id' =>
                        $pengaduan->pengaduan_id,

                    'status' =>
                        'Diajukan',

                    'keterangan' =>
                        'Pengaduan berhasil dikirim oleh masyarakat.',
                ]);
            });

            return redirect()
                ->route('user.pengaduan.index')
                ->with(
                    'success',
                    'Pengaduan berhasil dikirim.'
                );

        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Hapus Lampiran Jika Database Gagal
            |--------------------------------------------------------------------------
            */

            if ($lampiran) {

                Storage::disk('public')
                    ->delete($lampiran);
            }

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Terjadi kesalahan saat mengirim pengaduan.'
                );
        }
    }

    /**
     * Menampilkan detail pengaduan.
     */
    public function show(Pengaduan $pengaduan)
    {
        /*
        |--------------------------------------------------------------------------
        | Pastikan pengaduan milik user yang login
        |--------------------------------------------------------------------------
        */

        if (
            (int) $pengaduan->user_id !==
            (int) Auth::id()
        ) {
            abort(
                403,
                'Anda tidak memiliki akses ke pengaduan ini.'
            );
        }

        $pengaduan->load([
            'kategori',
            'bidang',
            'statusLogs',
        ]);

        return view(
            'user.pengaduan.show',
            compact('pengaduan')
        );
    }

    /**
     * Membuat kode pengaduan unik.
     */
    private function generateKodePengaduan(): string
    {
        do {

            $kode =
                'PGD'
                . now()->format('YmdHis')
                . random_int(100, 999);

        } while (
            Pengaduan::where(
                'kode_pengaduan',
                $kode
            )->exists()
        );

        return $kode;
    }
}