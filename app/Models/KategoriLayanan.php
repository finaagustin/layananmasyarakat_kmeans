<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriLayanan extends Model
{
    use HasFactory;

    protected $table = 'kategori_layanans';

    protected $primaryKey = 'kategori_layanan_id';

    public $incrementing = true;

    protected $keyType = 'int';

    // Mass Assignment
    protected $fillable = [
        'nama_kategori',
        'deskripsi',
        'bidang_id',
    ];

    // Relasi ke tabel Pengaduan
    public function pengaduans(): HasMany
    {
        return $this->hasMany(
            Pengaduan::class,
            'kategori_layanan_id',
            'kategori_layanan_id'
        );
    }

    // Relasi ke Bidang penanggung jawab kategori ini.
    // Dipakai untuk penentuan bidang secara otomatis saat
    // masyarakat mengajukan pengaduan baru.
    public function bidang(): BelongsTo
    {
        return $this->belongsTo(
            Bidang::class,
            'bidang_id',
            'bidang_id'
        );
    }
}