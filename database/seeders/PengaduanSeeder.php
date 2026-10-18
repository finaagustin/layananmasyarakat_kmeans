<?php

namespace Database\Seeders;

use App\Models\KategoriLayanan;
use App\Models\Pengaduan;
use App\Models\StatusLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PengaduanSeeder extends Seeder
{
    /**
     * Jumlah pengaduan yang ingin dibuat.
     * Bisa diubah sesuai kebutuhan (misal 150, 200, dst).
     */
    private int $jumlahPengaduan = 130;

    /**
     * Nama-nama depan & belakang untuk membuat akun masyarakat acak.
     */
    private array $namaDepan = [
        'Ahmad', 'Budi', 'Citra', 'Dewi', 'Eka', 'Fajar', 'Gita', 'Hendra',
        'Indra', 'Joko', 'Kiki', 'Lina', 'Made', 'Nia', 'Oki', 'Putri',
        'Rian', 'Sari', 'Tono', 'Umi', 'Vina', 'Wawan', 'Yani', 'Zaki',
        'Agus', 'Bella', 'Cahyo', 'Dian', 'Erna', 'Fitri',
    ];

    private array $namaBelakang = [
        'Saputra', 'Wijaya', 'Kusuma', 'Pratama', 'Hidayat', 'Santoso',
        'Permana', 'Nugraha', 'Setiawan', 'Wibowo', 'Firmansyah', 'Rahmawati',
        'Gunawan', 'Suryani', 'Maulana', 'Kartika', 'Utami', 'Ramadhan',
    ];

    /**
     * Kelurahan di Kota Bandung, dipakai untuk memvariasikan lokasi
     * pada teks pengaduan supaya lebih realistis dan beragam secara
     * leksikal (penting untuk hasil TF-IDF + K-Means).
     */
    private array $kelurahan = [
        'Dago', 'Cihapit', 'Sukaluyu', 'Cigadung', 'Antapani', 'Buahbatu',
        'Cibeunying Kidul', 'Sukajadi', 'Coblong', 'Astanaanyar',
        'Kiaracondong', 'Ujungberung', 'Bojongloa Kaler', 'Lengkong',
        'Batununggal', 'Cibiru', 'Andir', 'Cicendo', 'Arcamanik', 'Rancasari',
    ];

    /**
     * Template judul & isi pengaduan per kategori layanan.
     * Placeholder {kel} akan diganti nama kelurahan acak,
     * {rw} akan diganti nomor RW acak, saat proses seeding.
     */
    private array $templates = [

        'KRK' => [
            ['Permohonan informasi KRK untuk lahan di {kel}', 'Mohon informasi KRK untuk sebidang tanah milik saya di {kel} RW {rw} karena saya berencana membangun rumah tinggal dalam waktu dekat.'],
            ['Pengajuan KRK untuk tanah warisan keluarga', 'Saya ingin mengurus KRK untuk lahan warisan keluarga di {kel}, sertifikat masih atas nama orang tua, karena tanah tersebut akan dijual.'],
            ['Status pengajuan KRK belum ada kabar', 'Sudah dua minggu saya mengajukan KRK secara online untuk lahan di {kel} tapi belum ada kabar sama sekali, mohon dibantu dicek statusnya.'],
            ['Menanyakan peruntukan lahan di {kel}', 'Apakah lahan saya di {kel} RW {rw} termasuk zona hijau atau boleh dibangun untuk ruko dan usaha kecil?'],
            ['KRK untuk keperluan pengajuan KPR', 'Saya butuh dokumen KRK untuk keperluan pengajuan kredit rumah ke bank, lokasi tanah di {kel}, mohon diproses secepatnya.'],
            ['Lahan kena garis sempadan sungai', 'Petugas menyebut lahan saya di {kel} kena garis sempadan sungai sehingga tidak bisa dibangun penuh, saya ingin penjelasan lebih lanjut soal KRK ini.'],
            ['Peruntukan lahan kosong sebelah rumah', 'Saya ingin tahu peruntukan lahan kosong di sebelah rumah saya di {kel}, apakah memungkinkan untuk dibangun kos-kosan.'],
            ['Dokumen KRK tertukar saat pengambilan', 'Mohon bantuan, dokumen KRK saya sepertinya tertukar dengan pemohon lain saat pengambilan di kantor, tolong segera ditelusuri.'],
            ['Kepastian status tata ruang tanah baru dibeli', 'Saya baru pindah ke {kel} RW {rw} dan ingin memastikan status tata ruang tanah yang baru saya beli sebelum mulai membangun.'],
            ['Menanyakan syarat pengajuan KRK', 'Bagaimana syarat dan alur pengajuan KRK jika sertifikat tanah masih dalam proses balik nama di {kel}?'],
            ['KRK untuk perluasan usaha', 'Saya berencana memperluas usaha bengkel di {kel} dan memerlukan KRK untuk memastikan lahan boleh digunakan untuk usaha.'],
            ['Biaya pengurusan KRK', 'Mohon informasi apakah pengurusan KRK dikenakan biaya resmi, karena ada calo yang menawarkan jasa dengan harga cukup tinggi di {kel}.'],
            ['KRK hilang perlu cetak ulang', 'Dokumen KRK milik saya untuk tanah di {kel} hilang saat pindah rumah, apakah bisa diajukan permohonan cetak ulang?'],
            ['Ketidaksesuaian data KRK dengan lapangan', 'Data KRK yang saya terima untuk lahan di {kel} sepertinya tidak sesuai dengan kondisi batas lahan di lapangan, mohon dicek ulang.'],
            ['KRK untuk pemecahan sertifikat', 'Saya ingin memecah sertifikat tanah warisan menjadi tiga bagian di {kel}, apakah masing-masing bagian perlu KRK terpisah?'],
        ],

        'PBG' => [
            ['Pengajuan PBG untuk renovasi rumah', 'Saya berencana merenovasi rumah di {kel} RW {rw} dan ingin mengurus PBG terlebih dahulu sesuai prosedur yang berlaku.'],
            ['Cara mengurus PBG bangunan ruko', 'Bagaimana cara mengurus PBG untuk pembangunan ruko dua lantai di {kel}? Mohon dijelaskan dokumen apa saja yang diperlukan.'],
            ['Pengajuan PBG ditolak tanpa penjelasan', 'Pengajuan PBG saya untuk rumah di {kel} ditolak namun saya tidak mengerti alasannya, mohon penjelasan lebih lanjut dari petugas terkait.'],
            ['PBG untuk penambahan lantai', 'Saya ingin menambah lantai dua pada rumah di {kel}, apakah wajib mengurus PBG baru atau cukup dengan PBG yang lama?'],
            ['Proses PBG terlalu lama', 'Proses pengajuan PBG saya di {kel} sudah berjalan lebih dari sebulan tanpa kejelasan, apakah memang wajar memakan waktu selama itu?'],
            ['PBG untuk pembangunan pagar tembok', 'Saya mau membangun pagar tembok permanen di sekeliling rumah di {kel} RW {rw}, apakah pekerjaan ini tetap memerlukan PBG?'],
            ['Dokumen PBG hilang', 'Dokumen PBG rumah saya di {kel} hilang saat pindah rumah tahun lalu, apakah bisa mengajukan permintaan cetak ulang dokumen tersebut?'],
            ['Laporan bangunan tanpa PBG', 'Tetangga saya di {kel} sedang membangun ruko tiga lantai tanpa mengurus PBG terlebih dahulu, bagaimana cara melaporkan hal ini?'],
            ['PBG untuk renovasi dapur dan kamar mandi', 'Apakah renovasi kecil seperti perluasan dapur dan kamar mandi di rumah saya di {kel} tetap memerlukan pengurusan PBG?'],
            ['Menanyakan biaya retribusi PBG', 'Mohon informasi mengenai perhitungan biaya retribusi PBG untuk bangunan rumah tinggal dua lantai di {kel} RW {rw}.'],
            ['PBG untuk pembangunan gudang usaha', 'Saya membutuhkan PBG untuk membangun gudang penyimpanan barang usaha kecil di {kel}, mohon informasi persyaratannya.'],
            ['Revisi PBG karena perubahan desain', 'Saya sudah memiliki PBG namun ingin mengubah sedikit desain bangunan di {kel}, apakah perlu mengajukan revisi PBG?'],
            ['Konsultasi sebelum pengajuan PBG', 'Saya ingin berkonsultasi terlebih dahulu mengenai kelayakan desain bangunan sebelum resmi mengajukan PBG untuk lahan di {kel}.'],
            ['PBG bangunan rumah ibadah', 'Warga di {kel} RW {rw} berencana membangun mushola kecil dan ingin menanyakan alur pengurusan PBG untuk bangunan tersebut.'],
            ['Status verifikasi PBG online', 'Saya sudah mengunggah seluruh berkas PBG secara online untuk bangunan di {kel} namun status masih menunjukkan dalam verifikasi sejak dua minggu lalu.'],
        ],

        'IRK' => [
            ['Rencana pembangunan jalan di {kel}', 'Saya ingin mengetahui rencana pembangunan atau pelebaran jalan di sekitar {kel} untuk lima tahun ke depan.'],
            ['Informasi rencana revitalisasi kawasan', 'Mohon informasi apakah kawasan {kel} RW {rw} termasuk dalam rencana revitalisasi atau penataan kota dalam waktu dekat.'],
            ['IRK untuk keperluan investasi usaha kuliner', 'Saya calon investor dan membutuhkan informasi rencana tata ruang kawasan {kel} sebelum membuka usaha kuliner di sana.'],
            ['Menanyakan rencana ruang terbuka hijau', 'Apakah ada rencana pembangunan taman atau ruang terbuka hijau baru di kawasan {kel} sesuai RTRW Kota Bandung?'],
            ['Informasi rencana jalur transportasi umum', 'Saya ingin mengetahui apakah kawasan {kel} termasuk dalam rencana pengembangan jalur transportasi umum baru.'],
            ['IRK sebelum membeli tanah', 'Sebelum membeli tanah di {kel} RW {rw}, saya ingin memastikan terlebih dahulu rencana tata ruang kawasan tersebut ke depannya.'],
            ['Kejelasan rencana zonasi kawasan campuran', 'Mohon penjelasan mengenai rencana zonasi di {kel}, apakah kawasan tersebut termasuk zona permukiman atau zona campuran dengan komersial.'],
            ['Informasi rencana normalisasi sungai', 'Saya ingin mengetahui rencana normalisasi sungai yang melewati kawasan {kel} karena sering terjadi banjir di musim hujan.'],
            ['IRK untuk perencanaan pembangunan sekolah', 'Yayasan kami berencana membangun sekolah di {kel} dan membutuhkan informasi rencana tata ruang kawasan tersebut.'],
            ['Menanyakan rencana pelebaran trotoar', 'Apakah ada rencana pelebaran trotoar dan penataan pedestrian di sepanjang jalan utama {kel} RW {rw}?'],
            ['Kejelasan batas kawasan konservasi', 'Saya ingin mengetahui apakah sebagian lahan di {kel} termasuk kawasan konservasi yang dibatasi untuk pembangunan.'],
            ['IRK untuk proposal kerja sama pemerintah', 'Perusahaan kami membutuhkan informasi rencana tata ruang kawasan {kel} untuk keperluan proposal kerja sama dengan pemerintah kota.'],
            ['Informasi rencana penataan kawasan kumuh', 'Mohon informasi mengenai rencana penataan kawasan padat penduduk di {kel} yang sudah lama diusulkan warga.'],
            ['IRK untuk keperluan skripsi', 'Saya mahasiswa yang sedang menyusun tugas akhir mengenai tata ruang kota dan membutuhkan data IRK kawasan {kel}.'],
            ['Rencana pembangunan drainase kawasan', 'Kawasan {kel} RW {rw} sering tergenang saat hujan deras, saya ingin tahu apakah ada rencana perbaikan drainase dalam RTRW terbaru.'],
        ],

        'Pencarian Data Makam' => [
            ['Mencari data makam kakek di TPU {kel}', 'Saya ingin mencari lokasi makam kakek saya yang dimakamkan di TPU {kel} sekitar tahun 1998, mohon bantuan penelusuran datanya.'],
            ['Data makam orang tua tidak tercatat', 'Data makam orang tua saya di TPU {kel} sepertinya sudah tidak tercatat dengan baik, mohon bantuan untuk menelusuri kembali.'],
            ['Lupa blok dan nomor makam nenek', 'Saya ingin berziarah namun lupa blok dan nomor makam nenek saya di TPU {kel}, apakah bisa dibantu mencarikan datanya?'],
            ['Permohonan salinan data pemakaman', 'Saya membutuhkan salinan data pemakaman ayah saya di TPU {kel} untuk keperluan administrasi keluarga.'],
            ['Makam keluarga hilang setelah renovasi TPU', 'Setelah TPU {kel} direnovasi, saya kesulitan menemukan makam keluarga, apakah datanya masih tersimpan di sistem?'],
            ['Menelusuri makam saudara dari luar kota', 'Saya tinggal di luar kota dan ingin menelusuri lokasi makam saudara yang dimakamkan di TPU {kel} RW {rw} beberapa tahun lalu.'],
            ['Verifikasi nama pada data makam', 'Saya menemukan ketidaksesuaian nama pada data makam ibu saya di TPU {kel}, mohon dibantu untuk verifikasi dan perbaikan data.'],
            ['Mencari data makam untuk keperluan waris', 'Saya membutuhkan data lengkap lokasi makam kakek nenek di TPU {kel} untuk kelengkapan berkas pengurusan waris keluarga.'],
            ['Permintaan informasi denah TPU', 'Mohon informasi denah atau peta blok pemakaman di TPU {kel} agar memudahkan pencarian makam keluarga saat ziarah.'],
            ['Data makam tidak ditemukan di sistem online', 'Saya sudah mencoba mencari data makam ayah saya melalui sistem online namun tidak ditemukan, padahal dimakamkan di TPU {kel}.'],
            ['Mencari makam yang dipindahkan', 'Makam kakek saya di TPU {kel} sepertinya pernah dipindahkan, saya ingin mengetahui lokasi terbaru dan datanya.'],
            ['Bantuan pencarian makam untuk acara haul', 'Keluarga kami akan mengadakan acara haul dan membutuhkan kepastian lokasi makam di TPU {kel} RW {rw} terlebih dahulu.'],
        ],

        'Pemakaman Baru' => [
            ['Permohonan lahan makam baru mendesak', 'Keluarga kami membutuhkan lahan pemakaman baru segera di TPU {kel} karena ada anggota keluarga yang baru saja meninggal dunia.'],
            ['Prosedur dan syarat lahan makam baru', 'Bagaimana prosedur dan syarat mengajukan permohonan lahan makam baru di TPU {kel} bagi warga ber-KTP luar kota?'],
            ['Menanyakan biaya dan ketersediaan lahan', 'Saya ingin menanyakan biaya resmi dan ketersediaan lahan makam yang masih kosong di TPU {kel} RW {rw}.'],
            ['Lahan makam di TPU sudah penuh', 'Petugas menyampaikan bahwa lahan di TPU {kel} sudah penuh, saya ingin tahu alternatif TPU terdekat yang masih tersedia.'],
            ['Permohonan lahan makam untuk warga tidak mampu', 'Kami mengajukan permohonan lahan makam baru di TPU {kel} untuk keluarga yang kurang mampu, mohon informasi keringanan biaya.'],
            ['Pengajuan lahan makam keluarga berdekatan', 'Kami ingin mengajukan lahan makam baru yang berdekatan dengan makam keluarga yang sudah ada di TPU {kel}.'],
            ['Antrean pemakaman terlalu lama', 'Proses administrasi pemakaman baru di TPU {kel} terasa lambat padahal jenazah harus segera dimakamkan, mohon dipercepat.'],
            ['Menanyakan syarat dokumen pemakaman baru', 'Dokumen apa saja yang perlu disiapkan untuk mengajukan pemakaman baru di TPU {kel} RW {rw} bagi jenazah dari luar kota?'],
            ['Permohonan lahan makam agama tertentu', 'Kami ingin menanyakan ketersediaan blok pemakaman khusus sesuai agama di TPU {kel} untuk pemakaman baru keluarga kami.'],
            ['Konfirmasi jadwal pemakaman baru', 'Kami sudah mengajukan permohonan pemakaman baru di TPU {kel} kemarin, mohon konfirmasi jadwal dan petugas yang akan bertugas.'],
            ['Permohonan lahan makam darurat malam hari', 'Terjadi kedukaan mendadak dan kami membutuhkan lahan pemakaman baru di TPU {kel} pada malam hari ini, mohon bantuan segera.'],
            ['Menanyakan lokasi TPU terdekat', 'Kami tinggal di {kel} dan ingin menanyakan TPU terdekat yang masih menerima permohonan pemakaman baru saat ini.'],
        ],
    ];

    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Pastikan tersedia akun masyarakat untuk relasi user_id
        |--------------------------------------------------------------------------
        */

        $masyarakats = $this->pastikanMasyarakat(25);

        /*
        |--------------------------------------------------------------------------
        | 2. Ambil kategori layanan beserta bidang terkaitnya
        |--------------------------------------------------------------------------
        */

        $kategoris = KategoriLayanan::all()->keyBy('nama_kategori');

        if ($kategoris->isEmpty()) {
            $this->command?->warn(
                'Tabel kategori_layanans kosong. Jalankan KategoriLayananSeeder terlebih dahulu.'
            );
            return;
        }

        $namaKategoriTersedia = array_values(
            array_intersect(
                array_keys($this->templates),
                $kategoris->keys()->all()
            )
        );

        $dibuat = 0;

        for ($i = 0; $i < $this->jumlahPengaduan; $i++) {

            $namaKategori = $namaKategoriTersedia[array_rand($namaKategoriTersedia)];
            $kategori     = $kategoris[$namaKategori];
            $template     = $this->templates[$namaKategori][array_rand($this->templates[$namaKategori])];

            $kel = $this->kelurahan[array_rand($this->kelurahan)];
            $rw  = str_pad((string) random_int(1, 18), 2, '0', STR_PAD_LEFT);

            $judul = str_replace(
                ['{kel}', '{rw}'],
                [$kel, $rw],
                $template[0]
            );

            $isi = str_replace(
                ['{kel}', '{rw}'],
                [$kel, $rw],
                $template[1]
            );

            $user = $masyarakats->random();

            // Sebaran tanggal pengaduan: 4 bulan terakhir, acak.
            $tanggal = Carbon::now()
                ->subDays(random_int(0, 120))
                ->subHours(random_int(0, 23));

            // Sebaran status: 45% Diajukan, 35% Diproses, 20% Selesai.
            $roll = random_int(1, 100);

            $status = match (true) {
                $roll <= 45 => 'Diajukan',
                $roll <= 80 => 'Diproses',
                default     => 'Selesai',
            };

            $pengaduan = new Pengaduan([
                'kode_pengaduan' => $this->generateKodePengaduan($tanggal),
                'user_id' => $user->user_id,
                'kategori_layanan_id' => $kategori->kategori_layanan_id,
                'bidang_id' => $kategori->bidang_id,
                'judul' => $judul,
                'isi_pengaduan' => $isi,
                // hasil_preprocessing / cluster / nama_cluster sengaja
                // dibiarkan kosong -- diisi lewat model TF-IDF + K-Means
                // yang sudah dilatih, dengan menjalankan:
                //   php artisan pengaduan:re-predict
                'hasil_preprocessing' => null,
                'cluster' => null,
                'nama_cluster' => null,
                'lampiran' => null,
                'status' => $status,
                'tanggal_pengaduan' => $tanggal,
            ]);

            // created_at/updated_at bukan bagian dari $fillable, jadi
            // di-set manual dan timestamps otomatis dimatikan sementara
            // supaya tanggal riwayat data tetap tersebar realistis
            // (bukan semuanya "sekarang").
            $pengaduan->timestamps = false;
            $pengaduan->created_at = $tanggal;
            $pengaduan->updated_at = $tanggal;
            $pengaduan->save();

            $this->buatRiwayatStatus($pengaduan, $status, $tanggal);

            $dibuat++;
        }

        $this->command?->info(
            "Berhasil membuat {$dibuat} data pengaduan operasional (masyarakat)."
        );

        $this->command?->comment(
            'Cluster masih kosong -- jalankan "php artisan pengaduan:re-predict" '
            . 'setelah model TF-IDF + K-Means dilatih (menu "Jalankan Analisis" pada dataset historis) '
            . 'agar data ini siap diekspor dan dianalisis.'
        );
    }

    /**
     * Memastikan tersedia sejumlah akun masyarakat untuk dipakai
     * sebagai pemilik pengaduan. Membuat akun baru bila belum cukup.
     */
    private function pastikanMasyarakat(int $minimal)
    {
        $existing = User::where('role', 'masyarakat')->get();

        $kurang = $minimal - $existing->count();

        if ($kurang > 0) {

            for ($i = 0; $i < $kurang; $i++) {

                $depan  = $this->namaDepan[array_rand($this->namaDepan)];
                $belakang = $this->namaBelakang[array_rand($this->namaBelakang)];
                $nama = "{$depan} {$belakang}";

                $slug = Str::slug($depan) . random_int(100, 999);

                User::create([
                    'name' => $nama,
                    // dibatasi agar tetap di bawah 30 karakter (kolom email varchar(30))
                    'email' => Str::limit($slug, 15, '') . '@mail.test',
                    'role' => 'masyarakat',
                    // password disimpan plaintext sesuai desain aplikasi ini
                    'password' => 'warga12345',
                ]);
            }
        }

        return User::where('role', 'masyarakat')->get();
    }

    /**
     * Membuat riwayat status awal, dan riwayat lanjutan apabila
     * status pengaduan sudah Diproses atau Selesai, supaya timeline
     * "Riwayat Status" pada halaman detail tetap realistis.
     */
    private function buatRiwayatStatus(Pengaduan $pengaduan, string $status, Carbon $tanggalAwal): void
    {
        $this->simpanStatusLog(
            $pengaduan->pengaduan_id,
            'Diajukan',
            'Pengaduan berhasil dikirim oleh masyarakat.',
            $tanggalAwal
        );

        if ($status === 'Diproses' || $status === 'Selesai') {

            $tanggalProses = (clone $tanggalAwal)->addDays(random_int(1, 3));

            $this->simpanStatusLog(
                $pengaduan->pengaduan_id,
                'Diproses',
                'Pengaduan sedang ditindaklanjuti oleh petugas terkait.',
                $tanggalProses
            );
        }

        if ($status === 'Selesai') {

            $tanggalSelesai = (clone $tanggalAwal)->addDays(random_int(4, 10));

            $this->simpanStatusLog(
                $pengaduan->pengaduan_id,
                'Selesai',
                'Pengaduan telah selesai ditangani.',
                $tanggalSelesai
            );
        }
    }

    /**
     * Helper untuk menyimpan satu baris StatusLog dengan
     * created_at/updated_at kustom (bukan bagian dari $fillable,
     * jadi timestamps otomatis dimatikan sementara).
     */
    private function simpanStatusLog(int $pengaduanId, string $status, string $keterangan, Carbon $tanggal): void
    {
        $log = new StatusLog([
            'pengaduan_id' => $pengaduanId,
            'status' => $status,
            'keterangan' => $keterangan,
        ]);

        $log->timestamps = false;
        $log->created_at = $tanggal;
        $log->updated_at = $tanggal;
        $log->save();
    }

    /**
     * Membuat kode pengaduan unik, mengikuti format yang sama
     * dengan User\PengaduanController::generateKodePengaduan().
     */
    private function generateKodePengaduan(Carbon $tanggal): string
    {
        do {
            $kode = 'PGD' . $tanggal->format('YmdHis') . random_int(100, 999);
        } while (Pengaduan::where('kode_pengaduan', $kode)->exists());

        return $kode;
    }
}
