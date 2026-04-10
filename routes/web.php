<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Kalau user buka URL utama, langsung lempar ke halaman login
Route::get('/', function () {
    return redirect()->route('login');
});

// ============================================================
// LOGIN — Tidak pakai middleware 'guest' agar bisa diakses
// meskipun admin sudah login di tab lain
// ============================================================
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// ============================================================
// LOGOUT — Tanpa middleware 'auth' agar user viewer (yang
// tidak pakai session auth) juga bisa logout
// ============================================================
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ============================================================
// ADMIN DASHBOARD — Hanya bisa diakses lewat session auth
// ============================================================
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();
        // Kalau yang login bukan admin, tendang ke user dashboard
        if ($user->role !== 'admin') {
            return redirect('/user/dashboard');
        }
        return view('dashboard');
    })->name('dashboard');
});

// ============================================================
// USER VIEWER DASHBOARD — Pakai cookie 'rodis_viewer'
// Tidak pakai session auth supaya TIDAK bentrok dengan admin
// ============================================================
Route::get('/user/dashboard', function (\Illuminate\Http\Request $request) {
    $viewerId = $request->cookie('rodis_viewer');

    if (!$viewerId) {
        return redirect('/login');
    }

    try {
        $user = \App\Models\User::find($viewerId);

        if (!$user || $user->role !== 'user') {
            return redirect('/login')->withCookie(
                cookie()->forget('rodis_viewer')
            );
        }

        return view('user.dashboard', ['user' => $user]);
    } catch (\Exception $e) {
        return redirect('/login')->withCookie(
            cookie()->forget('rodis_viewer')
        );
    }
})->name('user.dashboard');

// Tangkap semua URL yang nggak terdaftar biar nggak error 404, arahkan kembali ke login
Route::get('/{any}', function () {
    return redirect()->route('login');
})->where('any', '.*');
