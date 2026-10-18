<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\StatusLog;

class Pengaduan extends Model
{
    use HasFactory;

    /**
     * Nama tabel.
     *
     * @var string
     */
    protected $table = 'pengaduans';

    /**
     * Primary Key.
     *
     * @var string
     */
    protected $primaryKey = 'pengaduan_id';

    /**
     * Auto Increment.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * Tipe Primary Key.
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * Mass Assignment.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kode_pengaduan',
        'user_id',
        'kategori_layanan_id',
        'bidang_id',
        'judul',
        'isi_pengaduan',
        'hasil_preprocessing',
        'cluster',
        'nama_cluster',
        'lampiran',
        'status',
        'tanggal_pengaduan',
    ];

    /**
     * Casting Attribute.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_pengaduan' => 'datetime',
    ];

    // Relasi ke User (Masyarakat)
    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id',
            'user_id'
        );
    }

    // Relasi ke Kategori Layanan
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(
            KategoriLayanan::class,
            'kategori_layanan_id',
            'kategori_layanan_id'
        );
    }

    // Relasi ke Bidang
    public function bidang(): BelongsTo
    {
        return $this->belongsTo(
            Bidang::class,
            'bidang_id',
            'bidang_id'
        );
    }

     // Relasi ke Riwayat Status.
     // Digunakan untuk fitur tracking status pengaduan
    public function statusLogs(): HasMany
    {
        return $this->hasMany(
            StatusLog::class,
            'pengaduan_id',
            'pengaduan_id'
        )->latest();
    }

    public function getRouteKeyName(): string
    {
        return 'pengaduan_id';
    }
}