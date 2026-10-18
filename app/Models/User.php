<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Primary Key
     */
    protected $primaryKey = 'user_id';

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
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * Hidden Attribute
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Cast Attribute
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }

    /**
     * Relasi User -> Pengaduan
     */
    public function pengaduans(): HasMany
    {
        return $this->hasMany(
            Pengaduan::class,
            'user_id',
            'user_id'
        );
    }
}