<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Analisis extends Model
{
    use HasFactory;

    protected $table = 'analisis';

    protected $primaryKey = 'analisis_id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = true;

    // Mass Assignment
    protected $fillable = [
        'dataset_id',
        'dataset_ids_terpakai',
        'jumlah_data',
        'jumlah_data_clustering',
        'jumlah_cluster',
        'k_optimal',
        'k_optimal_detail',
        'jumlah_cluster_detail',
        'per_cluster',
        'per_bidang',
        'top_terms',
        'grafik',
        'silhouette_score',
        'interpretasi',
        'durasi_proses',
        'status_analisis',
    ];

    // Casting Attribute
    protected $casts = [
        'dataset_id'             => 'integer',
        'dataset_ids_terpakai'   => 'array',
        'jumlah_data'            => 'integer',
        'jumlah_data_clustering' => 'integer',
        'jumlah_cluster'         => 'integer',
        'k_optimal'              => 'integer',
        'k_optimal_detail'       => 'array',
        'jumlah_cluster_detail'  => 'array',
        'per_cluster'            => 'array',
        'per_bidang'             => 'array',
        'top_terms'              => 'array',
        'grafik'                 => 'array',
        'silhouette_score'       => 'float',
        'durasi_proses'          => 'float',
    ];

    // Relasi ke tabel Dataset
    public function dataset(): BelongsTo
    {
        return $this->belongsTo(
            Dataset::class,
            'dataset_id',
            'dataset_id'
        );
    }
}