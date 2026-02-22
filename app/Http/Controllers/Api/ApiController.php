<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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
}
