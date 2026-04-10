<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Akun Admin (punya akses penuh ke dashboard kontrol)
        User::updateOrCreate(
            ['username' => 'disini'],
            [
                'name' => 'RODIS',
                'email' => 'rodis@localhost.com',
                'password' => Hash::make('disana123'),
                'role' => 'admin',
            ]
        );

        // Akun User (hanya bisa melihat hasil / read-only)
        User::updateOrCreate(
            ['username' => 'user'],
            [
                'name' => 'User Viewer',
                'email' => 'user@localhost.com',
                'password' => Hash::make('user123'),
                'role' => 'user',
            ]
        );
    }
}
