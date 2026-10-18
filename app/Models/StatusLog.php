<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatusLog extends Model
{
    use HasFactory;

    /**
     * Nama tabel
     */
    protected $table = 'status_logs';

    /**
     * Primary Key
     */
    protected $primaryKey = 'status_log_id';

    /**
     * Auto Increment
     */
    public $incrementing = true;

    /**
     * Tipe Primary Key
     */
    protected $keyType = 'int';

    /**
     * Mass Assignment
     */
    protected $fillable = [
        'pengaduan_id',
        'status',
        'keterangan',
    ];

    /**
     * Relasi ke tabel Pengaduan
     */
    public function pengaduan(): BelongsTo
    {
        return $this->belongsTo(
            Pengaduan::class,
            'pengaduan_id',
            'pengaduan_id'
        );
    }
}