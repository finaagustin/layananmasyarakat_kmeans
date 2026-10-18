<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Membuat akun administrator.
     */
    public function run(): void
    {
        User::updateOrCreate(
            [
                'email' => 'admin@diciptabintar.go.id',
            ],
            [
                'name' => 'Administrator',
                'password' => 'admin123',
                'role' => 'admin',
            ]
        );
    }
}