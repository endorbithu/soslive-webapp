<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\DevLoginController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
 * Dinamikus zóna: minden session-t használó route az /app alatt van. A session cookie útvonala is /app
 * (SESSION_PATH), így a böngésző a statikus oldalakhoz (routes/static.php) nem küld cookie-t.
 */
Route::prefix('app')->middleware('cache.headers:no_store;private')->group(function () {
    Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');
    Route::post('/logout', [GoogleController::class, 'logout'])->name('logout');

    // Teszt belépés Google nélkül – production alatt 404 (DevLoginController).
    Route::get('/auth/dev', [DevLoginController::class, 'create'])->name('auth.dev');
    Route::post('/auth/dev', [DevLoginController::class, 'store'])->middleware('throttle:10,1');

    // A statikus oldalak ebből tudják meg, ki van belépve (vendégnek user: null). Google tokent a backend nem kezel:
    // a Drive-hoz a böngésző maga kér hozzáférést (Google Identity Services).
    Route::get('/me', [DashboardController::class, 'me'])->name('me');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::middleware('guest:admin')->group(function () {
            Route::get('/login', [Admin\AuthController::class, 'create'])->name('login');
            Route::post('/login', [Admin\AuthController::class, 'store'])->middleware('throttle:5,1');
        });

        Route::middleware('auth:admin')->group(function () {
            Route::post('/logout', [Admin\AuthController::class, 'destroy'])->name('logout');
            Route::get('/', fn () => redirect()->route('admin.users.index'));
            Route::resource('users', Admin\UserController::class)->only(['index', 'destroy']);
        });
    });
});
