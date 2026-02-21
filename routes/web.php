<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route utama yang memuat seluruh interface Robot Trading
Route::get('/', function () {
    return view('dashboard');
});

// (Opsional) Jika Anda ingin saat user mengetik /monitor, /trade, dsb 
// tetap diarahkan ke halaman utama agar tidak error 404 Not Found.
Route::get('/{any}', function () {
    return redirect('/');
})->where('any', '.*');