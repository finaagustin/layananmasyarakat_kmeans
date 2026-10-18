<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengaduan;
use App\Support\SimpleXlsxWriter;
use Illuminate\Support\Facades\DB;

class ExportController extends Controller
{
    /**
     * Menampilkan halaman pilihan periode sebelum mengekspor
     * data layanan masyarakat menjadi dataset baru.
     */
    public function index()
    {
        // Kumpulkan daftar periode (bulan-tahun) yang tersedia
        // dari data pengaduan yang sudah masuk ke sistem.
        $periodeList = Pengaduan::select(
                DB::raw("DATE_FORMAT(tanggal_pengaduan, '%Y-%m') as periode_value"),
                DB::raw("DATE_FORMAT(tanggal_pengaduan, '%M %Y') as periode_label")
            )
            ->whereNotNull('tanggal_pengaduan')
            ->groupBy('periode_value', 'periode_label')
            ->orderByDesc('periode_value')
            ->get();

        return view(
            'admin.export.index',
            compact('periodeList')
        );
    }

    /**
     * Menghasilkan dan mengunduh berkas Excel (.xlsx) berisi data
     * layanan masyarakat pada periode yang dipilih Admin.
     *
     * Berkas ini dirancang agar dapat diunggah kembali sebagai
     * dataset historis baru pada menu Dataset, untuk dianalisis
     * ulang menggunakan algoritma K-Means.
     */
    public function download(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'periode' => [
                'required',
                'date_format:Y-m',
            ],
        ], [
            'periode.required' => 'Periode wajib dipilih.',
            'periode.date_format' => 'Format periode tidak valid.',
        ]);

        $periode = $request->periode;

        $pengaduans = Pengaduan::with(['kategori', 'bidang'])
            ->whereNotNull('tanggal_pengaduan')
            ->whereRaw(
                "DATE_FORMAT(tanggal_pengaduan, '%Y-%m') = ?",
                [$periode]
            )
            ->orderBy('tanggal_pengaduan')
            ->get();

        if ($pengaduans->isEmpty()) {
            return back()->with(
                'error',
                'Tidak ada data layanan pada periode yang dipilih.'
            );
        }

        $writer = new SimpleXlsxWriter();

        $writer->addRow([
            'Kode Pengaduan',
            'Tanggal',
            'Judul',
            'Isi Pengaduan',
            'Kategori Layanan',
            'Bidang',
            'Status',
            'Cluster',
            'Nama Cluster',
        ]);

        foreach ($pengaduans as $item) {
            $writer->addRow([
                $item->kode_pengaduan,
                optional($item->tanggal_pengaduan)->format('Y-m-d'),
                $item->judul,
                $item->isi_pengaduan,
                optional($item->kategori)->nama_kategori,
                optional($item->bidang)->nama_bidang,
                $item->status,
                $item->cluster,
                $item->nama_cluster,
            ]);
        }

        $fileName = 'dataset_layanan_' . $periode . '.xlsx';

        return $writer->download($fileName);
    }
}
