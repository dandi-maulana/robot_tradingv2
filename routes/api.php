<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// -----------------------------------------------------------
// Rute API Laravel (Jika ada fungsi spesifik di PHP)
// -----------------------------------------------------------

Route::get('/get_settings', [ApiController::class, 'getSettings']);
Route::post('/manual_trade', [ApiController::class, 'manualTrade']);
Route::get('/trade-history', [ApiController::class, 'getTradeHistory']);
Route::get('/debug-trade-history', [ApiController::class, 'debugTradeHistory']);

// =========================================================================
// Rute Proxy ke Python (Menghindari block port 5000 pada VPS)
// =========================================================================
Route::get('/user2_data', [ApiController::class, 'getUser2Data']);
Route::get('/data', [ApiController::class, 'getData']);
Route::get('/status_all', [ApiController::class, 'getStatusAll']);
Route::post('/toggle_telegram_all', [ApiController::class, 'toggleTelegramAll']);
Route::post('/toggle_telegram', [ApiController::class, 'toggleTelegram']);