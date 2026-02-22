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

// =========================================================================
// CATATAN PENTING:
// Rute lainnya (start_all, stop_all, data, status_all, toggle_telegram)
// sudah dihapus dari sini karena fungsi aslinya berjalan di server PYTHON
// (Flask) pada PORT 5000.
//
// Pastikan variabel API_BASE di Javascript (styles.blade.php / script)
// diarahkan langsung ke "http://127.0.0.1:5000/api"
// agar tidak nyasar ke Laravel.
// =========================================================================
