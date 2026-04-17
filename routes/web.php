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

    Route::get('/admin/history', function () {
        $user = auth()->user();
        if ($user->role !== 'admin') {
            return redirect('/user/dashboard');
        }
        return view('admin.history', ['user' => $user]);
    })->name('admin.history');
});

// ============================================================
// USER VIEWER DASHBOARD — Pakai cookie 'rodis_viewer'
// Tidak pakai session auth supaya TIDAK bentrok dengan admin
// ============================================================
Route::get('/user/dashboard', function (\Illuminate\Http\Request $request) {
    $viewerId = $request->cookie('rodis_viewer');
    $viewerToken = $request->cookie('rodis_viewer_token');

    if (!$viewerId || !$viewerToken) {
        return redirect('/login');
    }

    try {
        $user = \App\Models\User::find($viewerId);

        if (!$user || $user->role !== 'user') {
            return redirect('/login')
                ->withCookie(cookie()->forget('rodis_viewer'))
                ->withCookie(cookie()->forget('rodis_viewer_token'));
        }

        // SINGLE SESSION CHECK: Bandingkan token di cookie vs token di DB
        // Jika tidak cocok, berarti ada login baru di tempat lain
        if ($user->viewer_token !== $viewerToken) {
            return redirect('/login')
                ->withCookie(cookie()->forget('rodis_viewer'))
                ->withCookie(cookie()->forget('rodis_viewer_token'))
                ->withErrors(['username' => 'Sesi Anda telah berakhir karena akun ini login di perangkat lain.']);
        }

        return view('user.dashboard', ['user' => $user]);
    } catch (\Exception $e) {
        return redirect('/login')
            ->withCookie(cookie()->forget('rodis_viewer'))
            ->withCookie(cookie()->forget('rodis_viewer_token'));
    }
})->name('user.dashboard');

// ============================================================
// USER SESSION CHECK — Dipanggil secara periodik oleh JS
// untuk mendeteksi apakah sesi masih valid (single session)
// ============================================================
Route::get('/user/check-session', function (\Illuminate\Http\Request $request) {
    $viewerId = $request->cookie('rodis_viewer');
    $viewerToken = $request->cookie('rodis_viewer_token');

    if (!$viewerId || !$viewerToken) {
        return response()->json(['valid' => false, 'reason' => 'no_cookie']);
    }

    $user = \App\Models\User::find($viewerId);

    if (!$user || $user->role !== 'user') {
        return response()->json(['valid' => false, 'reason' => 'invalid_user']);
    }

    if ($user->viewer_token !== $viewerToken) {
        return response()->json(['valid' => false, 'reason' => 'session_replaced']);
    }

    return response()->json(['valid' => true]);
});

// ============================================================
// USER HISTORY PAGE — Menampilkan history open posisi per market
// ============================================================
Route::get('/user/history', function (\Illuminate\Http\Request $request) {
    $viewerId = $request->cookie('rodis_viewer');
    $viewerToken = $request->cookie('rodis_viewer_token');

    if (!$viewerId || !$viewerToken) {
        return redirect('/login');
    }

    try {
        $user = \App\Models\User::find($viewerId);

        if (!$user || $user->role !== 'user') {
            return redirect('/login')
                ->withCookie(cookie()->forget('rodis_viewer'))
                ->withCookie(cookie()->forget('rodis_viewer_token'));
        }

        if ($user->viewer_token !== $viewerToken) {
            return redirect('/login')
                ->withCookie(cookie()->forget('rodis_viewer'))
                ->withCookie(cookie()->forget('rodis_viewer_token'));
        }

        return view('user.history', ['user' => $user]);
    } catch (\Exception $e) {
        return redirect('/login')
            ->withCookie(cookie()->forget('rodis_viewer'))
            ->withCookie(cookie()->forget('rodis_viewer_token'));
    }
})->name('user.history');

// ============================================================
// USER2 PATTERN SCANNER — Pakai cookie 'rodis_viewer2'
// Akun khusus dengan logika C1-C5 pola candle
// ============================================================
Route::get('/user2/dashboard', function (\Illuminate\Http\Request $request) {
    $viewerId    = $request->cookie('rodis_viewer2');
    $viewerToken = $request->cookie('rodis_viewer2_token');

    if (!$viewerId || !$viewerToken) {
        return redirect('/login');
    }

    try {
        $user = \App\Models\User::find($viewerId);

        if (!$user || $user->role !== 'user2') {
            return redirect('/login')
                ->withCookie(cookie()->forget('rodis_viewer2'))
                ->withCookie(cookie()->forget('rodis_viewer2_token'));
        }

        if ($user->viewer_token !== $viewerToken) {
            return redirect('/login')
                ->withCookie(cookie()->forget('rodis_viewer2'))
                ->withCookie(cookie()->forget('rodis_viewer2_token'))
                ->withErrors(['username' => 'Sesi Anda telah berakhir karena akun ini login di perangkat lain.']);
        }

        return view('user.dashboard2', ['user' => $user]);
    } catch (\Exception $e) {
        return redirect('/login')
            ->withCookie(cookie()->forget('rodis_viewer2'))
            ->withCookie(cookie()->forget('rodis_viewer2_token'));
    }
})->name('user2.dashboard');

// USER2 SESSION CHECK — Cek sesi user2 setiap beberapa detik
Route::get('/user2/check-session', function (\Illuminate\Http\Request $request) {
    $viewerId    = $request->cookie('rodis_viewer2');
    $viewerToken = $request->cookie('rodis_viewer2_token');

    if (!$viewerId || !$viewerToken) {
        return response()->json(['valid' => false, 'reason' => 'no_cookie']);
    }

    $user = \App\Models\User::find($viewerId);

    if (!$user || $user->role !== 'user2') {
        return response()->json(['valid' => false, 'reason' => 'invalid_user']);
    }

    if ($user->viewer_token !== $viewerToken) {
        return response()->json(['valid' => false, 'reason' => 'session_replaced']);
    }

    return response()->json(['valid' => true]);
});

// Tangkap semua URL yang nggak terdaftar biar nggak error 404, arahkan kembali ke login
Route::get('/{any}', function () {
    return redirect()->route('login');
})->where('any', '.*');
