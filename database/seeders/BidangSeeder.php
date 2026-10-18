<?php

namespace Database\Seeders;

use App\Models\Bidang;
use Illuminate\Database\Seeder;

class BidangSeeder extends Seeder
{
    /**
     * Mengisi data awal bidang.
     */
    public function run(): void
    {
        $data = [
            [
                'nama_bidang' => 'Tata Ruang',
                'deskripsi' => 'Menangani layanan terkait rencana dan informasi tata ruang kota, seperti Keterangan Rencana Kota (KRK) dan Informasi Rencana Kota (IRK).',
            ],
            [
                'nama_bidang' => 'Bangunan Gedung',
                'deskripsi' => 'Menangani layanan terkait perizinan dan persetujuan bangunan gedung, seperti Persetujuan Bangunan Gedung (PBG).',
            ],
            [
                'nama_bidang' => 'Pemakaman',
                'deskripsi' => 'Menangani layanan seputar pemakaman, termasuk permohonan pemakaman baru dan pencarian data makam.',
            ],
        ];

        foreach ($data as $item) {

            Bidang::updateOrCreate(
                [
                    'nama_bidang' => $item['nama_bidang'],
                ],
                [
                    'deskripsi' => $item['deskripsi'],
                ]
            );
        }
    }
}