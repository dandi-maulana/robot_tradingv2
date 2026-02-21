<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Bikin 1 akun admin default
        User::create([
            'name' => 'RODIS',
            'email' => 'rodis@localhost.com',
            'username' => 'disini',
            'password' => Hash::make('disana123'),
        ]);
    }
}
