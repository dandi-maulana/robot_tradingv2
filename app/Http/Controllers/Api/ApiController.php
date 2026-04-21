<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{
    public function manualTrade(Request $request)
    {
        $request->validate([
            'market' => 'required',
            'amount' => 'required|numeric|min:1',
            'duration' => 'required|numeric',
            'direction' => 'required|in:up,down',
        ]);

        try {
            $response = Http::post('http://127.0.0.1:5000/api/manual_trade', [
                'market' => $request->market,
                'amount' => (float) $request->amount,
                'duration' => (int) $request->duration,
                'direction' => $request->direction,
            ]);

            if ($response->successful()) {
                return response()->json(['success' => true, 'message' => 'Order berhasil dikirim']);
            }

            return response()->json(['success' => false, 'message' => 'Gagal kontak server Python'], 500);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getSettings()
    {
        return response()->json(\App\Models\Setting::first());
    }

    public function getUser2Data(Request $request)
    {
        try {
            $response = Http::get('http://127.0.0.1:5000/api/user2_data');
            return response($response->body(), $response->status())->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal kontak server Python: '.$e->getMessage()]);
        }
    }

    public function getStatusAll(Request $request)
    {
        try {
            $response = Http::get('http://127.0.0.1:5000/api/status_all');
            return response($response->body(), $response->status())->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal kontak server Python: '.$e->getMessage()]);
        }
    }

    public function getData(Request $request)
    {
        try {
            $market = $request->query('market');
            $response = Http::get("http://127.0.0.1:5000/api/data?market=".urlencode($market));
            return response($response->body(), $response->status())->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal kontak server Python: '.$e->getMessage()]);
        }
    }

    public function toggleTelegramAll(Request $request)
    {
        try {
            $response = Http::post('http://127.0.0.1:5000/api/toggle_telegram_all', $request->all());
            return response($response->body(), $response->status())->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal kontak server Python: '.$e->getMessage()]);
        }
    }

    public function toggleTelegram(Request $request)
    {
        try {
            $response = Http::post('http://127.0.0.1:5000/api/toggle_telegram', $request->all());
            return response($response->body(), $response->status())->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal kontak server Python: '.$e->getMessage()]);
        }
    }

    public function getTradeHistory(Request $request)
    {
        try {
            $today = date('Y-m-d');
            $currentMonth = date('Y-m');
            $requestedTargetLoss = (int) $request->query('target_loss', 0);
            if ($requestedTargetLoss >= 1 && $requestedTargetLoss <= 6) {
                $targetLoss = $requestedTargetLoss;
            } else {
                $activeMassTarget = DB::table('market_states')
                    ->where('is_running', 1)
                    ->where('tg_active', 1)
                    ->whereBetween('tg_target_loss', [1, 6])
                    ->orderByDesc('updated_at')
                    ->value('tg_target_loss');

                $targetLoss = ($activeMassTarget !== null) ? (int) $activeMassTarget : 2;
                if ($targetLoss < 1 || $targetLoss > 6) {
                    $targetLoss = 2;
                }
            }
            $rows = DB::table('phase_histories')
                ->select([
                    'market as ticker',
                    'target_loss',
                    'tanggal',
                    'waktu',
                    'phase_1',
                    'phase_2',
                    'phase_3',
                    'phase_4',
                    'phase_5',
                    'phase_6',
                    'phase_7',
                    'resolved_result',
                    'resolved_phase',
                    'resolved_at',
                    'trigger_at',
                ])
                ->where('target_loss', $targetLoss)
                ->orderBy('trigger_at', 'desc')
                ->get()
                ->map(fn ($row) => (array) $row)
                ->all();

            $monthRows = array_values(array_filter($rows, function ($row) use ($currentMonth) {
                return str_starts_with((string) ($row['tanggal'] ?? ''), $currentMonth);
            }));

            $todaySummary = $this->calculateAccuracySummary($rows, function ($row) use ($today) {
                return $this->extractResolvedDate($row) === $today;
            });

            $monthSummary = $this->calculateAccuracySummary($rows, function ($row) use ($currentMonth) {
                return str_starts_with($this->extractResolvedDate($row), $currentMonth);
            });

            return response()->json([
                'success' => true,
                'date' => $today,
                'generated_at' => date('Y-m-d H:i:s'),
                'target_loss' => $targetLoss,
                'data' => $monthRows,
                'summary' => [
                    'today' => $todaySummary,
                    'month' => $monthSummary,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('getTradeHistory failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function calculateAccuracySummary(array $rows, callable $condition): array
    {
        $wins = 0;
        $losses = 0;

        foreach ($rows as $row) {
            if (!$condition($row)) {
                continue;
            }

            $result = (string) ($row['resolved_result'] ?? '');
            if ($result === 'TRUE') {
                $wins++;
            } elseif ($result === 'FALSE') {
                $losses++;
            }
        }

        $total = $wins + $losses;
        $accuracy = $total > 0 ? round(($wins / $total) * 100, 2) : 0;

        return [
            'wins' => $wins,
            'losses' => $losses,
            'total_signals' => $total,
            'accuracy_percent' => $accuracy,
            'accuracy_label' => number_format($accuracy, 2) . '%',
        ];
    }

    private function extractResolvedDate(array $row): string
    {
        $resolvedAt = (string) ($row['resolved_at'] ?? '');
        if ($resolvedAt === '') {
            return '';
        }

        return substr($resolvedAt, 0, 10);
    }

    public function debugTradeHistory()
    {
        try {
            $samples = \App\Models\MarketHistory::latest('id')->take(10)->get();
            $totalCount = \App\Models\MarketHistory::count();
            $markets = \App\Models\MarketHistory::distinct('market')->count();
            $lastRecord = \App\Models\MarketHistory::latest('created_at')->first();
            $firstRecord = \App\Models\MarketHistory::oldest('created_at')->first();

            return response()->json([
                'success' => true,
                'debug' => [
                    'total_records' => $totalCount,
                    'distinct_markets' => $markets,
                    'latest_record_created_at' => $lastRecord?->created_at,
                    'oldest_record_created_at' => $firstRecord?->created_at,
                    'sample_data' => $samples->toArray(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
