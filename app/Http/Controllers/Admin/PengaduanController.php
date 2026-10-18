<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bidang;
use App\Models\KategoriLayanan;
use App\Models\Pengaduan;
use App\Models\StatusLog;
use App\Services\PredictionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PengaduanController extends Controller
{
    // Menampilkan daftar seluruh pengaduan
    public function index(Request $request)
    {
        $kategoriLayananId = $request->query('kategori_layanan_id');

        $pengaduans = Pengaduan::with([
                'user',
                'kategori',
                'bidang',
            ])
            ->when(
                $kategoriLayananId,
                function ($query, $kategoriLayananId) {

                    $query->where(
                        'kategori_layanan_id',
                        $kategoriLayananId
                    );
                }
            )
            ->latest('tanggal_pengaduan')
            ->paginate(10)
            ->appends($request->query());

        $kategoriLayanans = KategoriLayanan::orderBy(
            'nama_kategori',
            'asc'
        )->get();

        return view(
            'admin.pengaduan.index',
            compact(
                'pengaduans',
                'kategoriLayanans',
                'kategoriLayananId'
            )
        );
    }

    // Menampilkan detail pengaduan
    public function show(Pengaduan $pengaduan)
    {
        $pengaduan->load([
            'user',
            'kategori',
            'bidang',
            'statusLogs',
        ]);

        return view(
            'admin.pengaduan.show',
            compact('pengaduan')
        );
    }

    // Menampilkan form perubahan bidang dan status pengaduan
    public function edit(Pengaduan $pengaduan)
    {
        $pengaduan->load([
            'user',
            'kategori',
            'bidang',
        ]);

        $bidangs = Bidang::orderBy('nama_bidang', 'asc')
            ->get();

        return view(
            'admin.pengaduan.edit',
            compact(
                'pengaduan',
                'bidangs'
            )
        );
    }

    // Memperbarui bidang tujuan dan status pengaduan
    public function update(
        Request $request,
        Pengaduan $pengaduan
    ) {
        $validated = $request->validate([
            'bidang_id' => [
                'nullable',
                'exists:bidang_layanans,bidang_id',
            ],

            'status' => [
                'required',
                'in:Diajukan,Diproses,Selesai',
            ],

            'keterangan' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ], [
            'bidang_id.exists' =>
                'Bidang yang dipilih tidak valid.',

            'status.required' =>
                'Status pengaduan wajib dipilih.',

            'status.in' =>
                'Status pengaduan tidak valid.',

            'keterangan.string' =>
                'Keterangan harus berupa teks.',

            'keterangan.max' =>
                'Keterangan maksimal 1000 karakter.',
        ]);

        try {

            DB::transaction(function () use (
                $validated,
                $pengaduan
            ) {

                // Simpan Status Lama

                $statusLama = $pengaduan->status;

                //Update Pengaduan

                $dataUpdate = [
                    'status' => $validated['status'],
                ];

                // Bidang hanya diperbarui apabila field bidang_id memang dikirim dari form
                if (array_key_exists('bidang_id', $validated)) {
                    $dataUpdate['bidang_id'] =
                        $validated['bidang_id'];
                }

                $pengaduan->update($dataUpdate);

                // Simpan Riwayat Status
                // Riwayat hanya dibuat apabila status berubah

                if ($statusLama !== $validated['status']) {

                    StatusLog::create([
                        'pengaduan_id' =>
                            $pengaduan->pengaduan_id,

                        'status' =>
                            $validated['status'],

                        'keterangan' =>
                            $validated['keterangan']
                            ?? null,
                    ]);
                }
            });

            return redirect()
                ->route(
                    'admin.pengaduan.show',
                    $pengaduan->pengaduan_id
                )
                ->with(
                    'success',
                    'Data pengaduan berhasil diperbarui.'
                );

        } catch (\Throwable $e) {

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Terjadi kesalahan saat memperbarui pengaduan.'
                );
        }
    }

    // Data pengaduan tidak dapat dihapus oleh admin
    public function destroy(Pengaduan $pengaduan)
    {
        abort(
            403,
            'Data pengaduan tidak dapat dihapus.'
        );
    }

    /**
     * Memprediksi ulang cluster sebuah pengaduan.
     *
     * Dipakai untuk pengaduan yang cluster-nya masih kosong
     * (mis. dikirim sebelum model TF-IDF/K-Means dilatih),
     * atau untuk memperbarui hasil prediksi setelah Admin
     * melatih ulang model dengan dataset historis terbaru.
     */
    public function predictUlang(
        PredictionService $predictionService,
        Pengaduan $pengaduan
    ) {
        $prediksi = $predictionService->predict(
            $pengaduan->judul,
            $pengaduan->isi_pengaduan
        );

        if ($prediksi === null) {

            return back()->with(
                'error',
                'Prediksi cluster gagal. Pastikan Admin sudah '
                . 'pernah menjalankan analisis dataset historis '
                . 'terlebih dahulu.'
            );
        }

        $pengaduan->update([
            'hasil_preprocessing' => $prediksi['hasil_preprocessing'],
            'cluster'             => $prediksi['cluster'],
            'nama_cluster'        => $prediksi['nama_cluster'],
        ]);

        return back()->with(
            'success',
            'Cluster pengaduan berhasil diprediksi ulang.'
        );
    }
}