<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bidang;
use App\Models\Pengaduan;

class MonitoringController extends Controller
{
    /**
     * Menampilkan papan pemantauan (monitoring) status layanan
     * masyarakat dalam tampilan tiga kolom (Diajukan, Diproses,
     * Selesai) agar Admin dapat memantau sebaran status secara
     * cepat, sebelum masuk ke halaman Kelola Pengaduan untuk
     * mengubah status dan bidang penanganannya.
     *
     * Juga menampilkan kriteria/cakupan tiap Bidang (beserta
     * kategori layanan di dalamnya) agar Admin maupun orang awam
     * memahami dasar sistem mengarahkan sebuah pengaduan ke
     * bidang tertentu secara otomatis.
     */
    public function index()
    {
        $diajukan = Pengaduan::with(['kategori', 'bidang'])
            ->where('status', 'Diajukan')
            ->latest('tanggal_pengaduan')
            ->get();

        $diproses = Pengaduan::with(['kategori', 'bidang'])
            ->where('status', 'Diproses')
            ->latest('tanggal_pengaduan')
            ->get();

        $selesai = Pengaduan::with(['kategori', 'bidang'])
            ->where('status', 'Selesai')
            ->latest('tanggal_pengaduan')
            ->limit(10)
            ->get();

        $kriteriaBidang = Bidang::with('kategoriLayanans')
            ->orderBy('nama_bidang', 'asc')
            ->get();

        return view('admin.monitoring.index', compact(
            'diajukan',
            'diproses',
            'selesai',
            'kriteriaBidang'
        ));
    }
}
