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
// Rute Jembatan API ke Database (Untuk Worker Python & Web Dashboard)
// -----------------------------------------------------------

Route::get('/get_settings', [ApiController::class, 'getSettings']);
Route::get('/status_all', [ApiController::class, 'statusAll']);
Route::get('/data', [ApiController::class, 'getMarketData']);
Route::get('/trade_history', [ApiController::class, 'getTradeHistory']);

Route::post('/check_accounts', [ApiController::class, 'checkAccounts']);
Route::post('/start', [ApiController::class, 'startBot']);
Route::post('/start_all', [ApiController::class, 'startAll']);
Route::post('/stop', [ApiController::class, 'stopBot']);
Route::post('/reset_market', [ApiController::class, 'resetMarket']);
Route::post('/reset_all', [ApiController::class, 'resetAll']);
Route::post('/toggle_telegram', [ApiController::class, 'toggleTelegram']);
Route::post('/telegram_all', [ApiController::class, 'telegramAll']);
Route::post('/manual_trade', [ApiController::class, 'manualTrade']);