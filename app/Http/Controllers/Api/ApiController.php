<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{
    public function manualTrade(Request $request)
    {
        // Validasi data masuk dari frontend
        $request->validate([
            'market' => 'required',
            'amount' => 'required|numeric|min:1',
            'duration' => 'required|numeric',
            'direction' => 'required|in:up,down'
        ]);

        try {
            // Tembak ke API Python (sesuaikan port jika berbeda)
            $response = Http::post('http://127.0.0.1:5000/api/manual_trade', [
                'market' => $request->market,
                'amount' => (float) $request->amount,
                'duration' => (int) $request->duration,
                'direction' => $request->direction
            ]);

            if ($response->successful()) {
                return response()->json(['success' => true, 'message' => 'Order berhasil dikirim']);
            }

            return response()->json(['success' => false, 'message' => 'Gagal kontak server Python'], 500);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Pastikan fungsi pendukung lainnya tetap ada jika dibutuhkan oleh routes/api.php
    public function getSettings()
    {
        return response()->json(\App\Models\Setting::first());
    }

    public function getTradeHistory()
    {
        try {
            Log::info("=== getTradeHistory START ===");
            $today = date('Y-m-d');
            $monthStart = date('Y-m-01');

            // Query dari market_states untuk list semua market
            Log::info("Querying MarketState::all()");
            $states = \App\Models\MarketState::all();
            Log::info("MarketState count: " . ($states ? count($states) : 0));

            if (!$states || count($states) === 0) {
                Log::info("No market states found, returning empty");
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'date' => $today,
                    'summary' => [
                        'active_markets_today' => 0,
                        'total_triggers_today' => 0,
                        'total_triggers_month' => 0
                    ]
                ]);
            }

            $result = [];
            $totalTodayCount = 0;
            $totalMonthCount = 0;

            foreach ($states as $state) {
                $market = $state->market;
                Log::info("Processing market: {$market}");

                // ✅ HARI INI: Calculate actual triggers
                $countToday = 0;
                try {
                    Log::info("Calling countDailyTriggersForMarket({$market}, {$today})");
                    $countToday = $this->countDailyTriggersForMarket($market, $today);
                    Log::info("Result: {$countToday} triggers");
                } catch (\Exception $e) {
                    Log::error("Error counting daily triggers for $market: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine());
                    $countToday = 0;
                }

                // ✅ BULAN INI: Calculate actual triggers
                $countMonth = 0;
                try {
                    Log::info("Calling countMonthlyTriggersForMarket({$market}, {$monthStart})");
                    $countMonth = $this->countMonthlyTriggersForMarket($market, $monthStart);
                    Log::info("Result: {$countMonth} triggers");
                } catch (\Exception $e) {
                    Log::error("Error counting monthly triggers for $market: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine());
                    $countMonth = 0;
                }

                $totalTodayCount += $countToday;
                $totalMonthCount += $countMonth;

                if ($countToday > 0 || $countMonth > 0) {
                    $result[] = [
                        'market' => $market,
                        'today' => $countToday,
                        'month' => $countMonth
                    ];
                }
            }

            Log::info("=== getTradeHistory SUCCESS - Found " . count($result) . " markets ===");
            return response()->json([
                'success' => true,
                'data' => $result,
                'date' => $today,
                'summary' => [
                    'active_markets_today' => count(array_filter($result, fn($r) => $r['today'] > 0)),
                    'total_triggers_today' => $totalTodayCount,
                    'total_triggers_month' => $totalMonthCount
                ]
            ]);
        } catch (\Exception $e) {
            Log::error("=== FATAL getTradeHistory error ===");
            Log::error("Message: " . $e->getMessage());
            Log::error("File: " . $e->getFile());
            Log::error("Line: " . $e->getLine());
            Log::error("Trace: " . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    private function countDailyTriggersForMarket($market, $date)
    {
        // Hitung actual triggers untuk hari spesifik dengan full data (tanggal, waktu, warna)
        Log::info("countDailyTriggersForMarket: market={$market}, date={$date}");

        try {
            $candles = \App\Models\MarketHistory::where('market', $market)
                ->where('tanggal', $date)
                ->orderBy('waktu', 'asc')
                ->get(['tanggal', 'waktu', 'warna'])
                ->toArray();

            Log::info("Retrieved " . count($candles) . " candles for {$date}");

            $result = $this->countFalseStreakTriggers($candles);
            Log::info("Triggers: {$result}");

            return $result;
        } catch (\Exception $e) {
            Log::error("Exception in countDailyTriggersForMarket: " . $e->getMessage());
            throw $e;
        }
    }

    private function countMonthlyTriggersForMarket($market, $monthStart)
    {
        Log::info("countMonthlyTriggersForMarket: market={$market}, monthStart={$monthStart}");

        try {
            $monthEnd = date('Y-m-d', strtotime('last day of ' . date('Y-m', strtotime($monthStart))));
            Log::info("Month range: {$monthStart} to {$monthEnd}");

            // Ambil semua candles bulan ini
            $allCandles = \App\Models\MarketHistory::where('market', $market)
                ->where('tanggal', '>=', $monthStart)
                ->where('tanggal', '<=', $monthEnd)
                ->orderBy('tanggal', 'asc')
                ->orderBy('waktu', 'asc')
                ->get(['tanggal', 'waktu', 'warna']);

            Log::info("Retrieved " . ($allCandles ? count($allCandles) : 0) . " candles");

            if (!$allCandles || count($allCandles) === 0) {
                Log::info("No candles found, returning 0");
                return 0;
            }

            // Group by tanggal dan calculate per hari
            $groupedByDate = $allCandles->groupBy('tanggal');
            Log::info("Grouped into " . count($groupedByDate) . " dates");

            $totalTriggers = 0;

            foreach ($groupedByDate as $date => $candles) {
                try {
                    $candleArray = $candles->toArray();
                    $triggers = $this->countFalseStreakTriggers($candleArray);
                    Log::info("Date {$date}: {$triggers} triggers");
                    $totalTriggers += $triggers;
                } catch (\Exception $e) {
                    Log::error("Error processing date {$date}: " . $e->getMessage());
                }
            }

            Log::info("Total monthly triggers: {$totalTriggers}");
            return $totalTriggers;
        } catch (\Exception $e) {
            Log::error("Exception in countMonthlyTriggersForMarket: " . $e->getMessage());
            throw $e;
        }
    }

    private function countFalseStreakTriggers($candles)
    {
        // Implement same logic as Python calc_sig_loss()
        // Group candles by 5-minute blocks, check for FALSE pattern

        if (!is_array($candles) || count($candles) < 5) {
            Log::debug("countFalseStreakTriggers: Not enough candles, returning 0");
            return 0;
        }

        $sig_loss = 0;
        $blocks = [];

        // Group candles by 5-minute blocks
        foreach ($candles as $candle) {
            try {
                $warna = $candle['warna'] ?? '';
                $waktu = $candle['waktu'] ?? '';

                // Jika ada waktu, group by 5-minute blocks
                if (!empty($waktu) && strpos($waktu, ':') !== false) {
                    $parts = explode(':', $waktu);
                    if (count($parts) < 2) {
                        Log::debug("Invalid time format: {$waktu}");
                        continue;
                    }

                    $hh = $parts[0];
                    $mm = (int)$parts[1];

                    if ($mm < 0 || $mm > 59) {
                        Log::debug("Invalid minute: {$mm}");
                        continue;
                    }

                    $base_mm = intdiv($mm, 5) * 5;
                    $key = $hh . ':' . str_pad($base_mm, 2, '0', STR_PAD_LEFT);

                    if (!isset($blocks[$key])) {
                        $blocks[$key] = [];
                    }

                    $offset = $mm % 5;
                    $base_color = strpos($warna, 'Hijau') !== false ? 'Hijau' : 'Merah';
                    $blocks[$key]["c{$offset}"] = $base_color;
                }
            } catch (\Exception $e) {
                Log::debug("Error processing candle: " . $e->getMessage());
                continue;
            }
        }

        if (count($blocks) === 0) {
            Log::debug("No valid blocks found");
            return 0;
        }

        // Sort blocks dari yang terbaru (reverse order)
        $sorted_keys = array_keys($blocks);
        rsort($sorted_keys);

        foreach ($sorted_keys as $k) {
            try {
                $b = $blocks[$k];
                if (isset($b['c0'])) {
                    $c0 = $b['c0'];

                    // Kondisi TRUE: salah satu dari c2, c3, atau c4 SAMA dengan c0
                    $is_true = false;
                    if (isset($b['c2']) && $b['c2'] === $c0) $is_true = true;
                    if (isset($b['c3']) && $b['c3'] === $c0) $is_true = true;
                    if (isset($b['c4']) && $b['c4'] === $c0) $is_true = true;

                    if ($is_true) {
                        break; // Reset ke 0 jika mendeteksi ada 1 TRUE (Win)
                    }
                    // Kondisi FALSE: Siklus lengkap (0,2,3,4) tapi tidak ada yang sama
                    elseif (isset($b['c2']) && isset($b['c3']) && isset($b['c4'])) {
                        $sig_loss++;
                    }
                }
            } catch (\Exception $e) {
                Log::debug("Error processing block {$k}: " . $e->getMessage());
                continue;
            }
        }

        return $sig_loss;
    }

    public function debugTradeHistory()
    {
        try {
            // Ambil beberapa sample data terakhir dari market_histories
            $samples = \App\Models\MarketHistory::latest('id')->take(10)->get();

            // Hitung total data
            $totalCount = \App\Models\MarketHistory::count();

            // Hitung distinct market
            $markets = \App\Models\MarketHistory::distinct('market')->count();

            // Cek range tanggal
            $lastRecord = \App\Models\MarketHistory::latest('created_at')->first();
            $firstRecord = \App\Models\MarketHistory::oldest('created_at')->first();

            return response()->json([
                'success' => true,
                'debug' => [
                    'total_records' => $totalCount,
                    'distinct_markets' => $markets,
                    'latest_record_created_at' => $lastRecord?->created_at,
                    'oldest_record_created_at' => $firstRecord?->created_at,
                    'sample_data' => $samples->toArray()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
