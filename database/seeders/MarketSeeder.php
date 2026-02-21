<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MarketSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $markets = [
            "EUR/USD OTC", "GBP/USD OTC", "USD/JPY OTC", "AUD/USD OTC", 
            "NZD/USD OTC", "USD/CAD OTC", "USD/CHF OTC", "EUR/JPY OTC", 
            "GBP/JPY OTC", "AUD/JPY OTC", "CAD/JPY OTC", "NZD/JPY OTC", 
            "CHF/JPY OTC", "EUR/GBP OTC", "EUR/AUD OTC", "EUR/CAD OTC", 
            "EUR/CHF OTC", "GBP/AUD OTC", "GBP/CAD OTC", "GBP/CHF OTC", 
            "AUD/CAD OTC", "AUD/CHF OTC", "CAD/CHF OTC", "Asia Composite Index", 
            "Europe Composite Index", "Commodity Composite", "Crypto Composite Index"
        ];

        foreach ($markets as $market) {
            DB::table('markets')->updateOrInsert(
                ['id' => $market], // Cari berdasarkan nama market
                [
                    'is_running' => false,
                    'total_trade' => 0,
                    'total_hijau' => 0,
                    'total_merah' => 0,
                    'tg_active' => false,
                    'tg_target_loss' => 9,
                    'tg_phase' => 'IDLE',
                    'tg_trade_counter' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}