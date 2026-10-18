<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bidang extends Model
{
    use HasFactory;

    protected $table = 'bidang_layanans';

    protected $primaryKey = 'bidang_id';

    public $incrementing = true;

    protected $keyType = 'int';

    // Mass Assignment
    protected $fillable = [
        'nama_bidang',
        'deskripsi',
    ];

    // Relasi ke tabel Pengaduan
    public function pengaduans(): HasMany
    {
        return $this->hasMany(
            Pengaduan::class,
            'bidang_id',
            'bidang_id'
        );
    }

    // Relasi ke Kategori Layanan yang termasuk bidang ini.
    // Dipakai untuk menampilkan kriteria/cakupan tiap bidang
    // pada halaman Monitoring.
    public function kategoriLayanans(): HasMany
    {
        return $this->hasMany(
            KategoriLayanan::class,
            'bidang_id',
            'bidang_id'
        );
    }
}