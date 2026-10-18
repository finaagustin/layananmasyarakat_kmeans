<?php

namespace Database\Seeders;

use App\Models\Bidang;
use App\Models\KategoriLayanan;
use Illuminate\Database\Seeder;

class KategoriLayananSeeder extends Seeder
{
    /**
     * Mengisi data awal kategori layanan sekaligus memetakan
     * setiap kategori ke bidang penanganannya (dipakai untuk
     * penentuan bidang secara otomatis pada pengaduan baru).
     *
     * Pemetaan ini mengikuti kriteria yang sama dengan
     * python/engine/kategori_bidang.py yang sudah dipakai pada
     * fitur "Analisis per Bidang" di Dashboard.
     */
    public function run(): void
    {
        $data = [
            [
                'nama_kategori' => 'KRK',
                'deskripsi' => 'Keterangan Rencana Kota — untuk mengetahui peruntukan dan ketentuan tata ruang suatu lahan/lokasi (misalnya sebelum membangun atau mengurus izin lain).',
                'nama_bidang' => 'Tata Ruang',
            ],
            [
                'nama_kategori' => 'PBG',
                'deskripsi' => 'Persetujuan Bangunan Gedung — untuk mengajukan izin sebelum mendirikan, mengubah, atau merenovasi bangunan gedung.',
                'nama_bidang' => 'Bangunan Gedung',
            ],
            [
                'nama_kategori' => 'IRK',
                'deskripsi' => 'Informasi Rencana Kota — untuk memperoleh informasi umum mengenai rencana tata ruang suatu kawasan.',
                'nama_bidang' => 'Tata Ruang',
            ],
            [
                'nama_kategori' => 'Pencarian Data Makam',
                'deskripsi' => 'Untuk mencari atau menelusuri data pemakaman seseorang yang sudah tercatat, misalnya lokasi makam keluarga.',
                'nama_bidang' => 'Pemakaman',
            ],
            [
                'nama_kategori' => 'Pemakaman Baru',
                'deskripsi' => 'Untuk mengajukan permohonan lahan pemakaman baru bagi jenazah yang belum dimakamkan.',
                'nama_bidang' => 'Pemakaman',
            ],
        ];

        foreach ($data as $item) {

            $bidangId = Bidang::where(
                'nama_bidang',
                $item['nama_bidang']
            )->value('bidang_id');

            KategoriLayanan::updateOrCreate(
                [
                    'nama_kategori' => $item['nama_kategori'],
                ],
                [
                    'deskripsi' => $item['deskripsi'],
                    'bidang_id' => $bidangId,
                ]
            );
        }
    }
}