<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLoginForm(Request $request)
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required',
            'g-recaptcha-response' => 'required'
        ], [
            'g-recaptcha-response.required' => 'Silakan centang kotak "I\'m not a robot".'
        ]);

        $throttleKey = strtolower($request->input('username')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'username' => ["Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik."],
            ]);
        }

        $recaptchaResponse = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => env('RECAPTCHA_SECRET_KEY'),
            'response' => $request->input('g-recaptcha-response'),
            'remoteip' => $request->ip()
        ]);

        if (!$recaptchaResponse->json('success')) {
            RateLimiter::hit($throttleKey);
            throw ValidationException::withMessages([
                'g-recaptcha-response' => ['Validasi reCAPTCHA gagal, terdeteksi aktivitas bot.']
            ]);
        }

        // ============================================================
        // CEK KREDENSIAL SECARA MANUAL (bukan Auth::attempt)
        // agar bisa memisahkan mekanisme admin vs user viewer
        // ============================================================
        $user = User::where('username', $request->input('username'))->first();

        if ($user && Hash::check($request->input('password'), $user->password)) {
            RateLimiter::clear($throttleKey);

            if ($user->role === 'admin') {
                // ====================================================
                // ADMIN: Login via session standar Laravel
                // Cookie: laravel_session (default)
                // ====================================================
                Auth::login($user, $request->boolean('remember'));
                $request->session()->regenerate();
                return redirect()->intended('/dashboard');

            } else {
                // ====================================================
                // USER VIEWER: Login via encrypted cookie TERPISAH
                // Cookie: rodis_viewer (tidak menyentuh session auth)
                // Jadi TIDAK bentrok dengan session admin!
                // ====================================================
                return redirect('/user/dashboard')->withCookie(
                    cookie('rodis_viewer', $user->id, 120, '/', null, false, true)
                );
            }
        }

        RateLimiter::hit($throttleKey);
        throw ValidationException::withMessages([
            'username' => ['Username atau password salah.'],
        ]);
    }

    public function logout(Request $request)
    {
        // Cek apakah ada session admin aktif → logout session
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        // Selalu hapus juga viewer cookie (biar bersih)
        return redirect('/login')->withCookie(
            cookie()->forget('rodis_viewer')
        );
    }
}
