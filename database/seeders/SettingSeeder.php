<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('settings')->updateOrInsert(
            ['id' => 1], // Cari data dengan ID 1
            [
                'token' => '',
                'account_id' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}