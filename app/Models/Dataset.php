<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dataset extends Model
{
    use HasFactory;

    protected $table = 'datasets';

    protected $primaryKey = 'dataset_id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = true;

    // Mass Assignment
    protected $fillable = [
        'periode',
        'file_excel',
    ];

    // Relasi ke tabel analisis
    public function analisis(): HasMany
    {
        return $this->hasMany(
            Analisis::class,
            'dataset_id',
            'dataset_id'
        );
    }
}