<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Kalau user buka URL utama, langsung lempar ke halaman login
Route::get('/', function () {
    return redirect()->route('login');
});

// Grup untuk tamu (belum login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Grup untuk user yang sudah berhasil login
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Tangkap semua URL yang nggak terdaftar biar nggak error 404, arahkan kembali ke login
Route::get('/{any}', function () {
    return redirect()->route('login');
})->where('any', '.*');
