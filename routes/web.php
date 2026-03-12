<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\PortalLoginController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [PortalLoginController::class, 'show'])->name('login');
    Route::post('/login', [PortalLoginController::class, 'authenticate'])->name('portal.login');
});

Route::post('/logout', [PortalLoginController::class, 'logout'])->name('portal.logout');
