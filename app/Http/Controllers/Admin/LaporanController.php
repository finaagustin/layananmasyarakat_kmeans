<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Analisis;
use App\Models\Bidang;
use App\Models\KategoriLayanan;
use App\Models\Pengaduan;

class LaporanController extends Controller
{
    /**
     * Menampilkan laporan hasil analisis K-Means dan rekapitulasi
     * layanan masyarakat berdasarkan hasil clustering.
     *
     * Halaman ini bersifat read-only (hanya menampilkan ringkasan
     * yang sudah tersimpan pada tabel analisis dan pengaduans),
     * dan dapat dicetak/disimpan oleh Admin melalui fitur cetak
     * peramban (window.print()).
     */
    public function index()
    {
        // Hasil analisis K-Means terakhir yang sudah dijalankan
        $analisisTerakhir = Analisis::with('dataset')
            ->latest('analisis_id')
            ->first();

        // Rekap jumlah pengaduan per status
        $rekapStatus = Pengaduan::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        // Rekap jumlah pengaduan per kategori layanan
        $rekapKategori = KategoriLayanan::withCount('pengaduans')
            ->orderByDesc('pengaduans_count')
            ->get();

        // Rekap jumlah pengaduan per bidang
        $rekapBidang = Bidang::withCount('pengaduans')
            ->orderByDesc('pengaduans_count')
            ->get();

        $totalPengaduan = Pengaduan::count();

        return view('admin.laporan.index', compact(
            'analisisTerakhir',
            'rekapStatus',
            'rekapKategori',
            'rekapBidang',
            'totalPengaduan'
        ));
    }
}
