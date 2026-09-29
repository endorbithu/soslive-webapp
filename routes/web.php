<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

/*
 * Dinamikus zóna: minden session-t használó route az /app alatt van. A session cookie útvonala is /app
 * (SESSION_PATH), így a böngésző a statikus oldalakhoz (routes/static.php) nem küld cookie-t.
 */
Route::prefix('app')->middleware('cache.headers:no_store;private')->group(function () {
    Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');
    Route::post('/logout', [GoogleController::class, 'logout'])->name('logout');

    // A statikus oldalak ebből tudják meg, ki van belépve (vendégnek user: null).
    Route::get('/me', [DashboardController::class, 'me'])->name('me');

    Route::middleware('auth')->group(function () {
        Route::post('/settings/folder', [SettingsController::class, 'folder'])->name('settings.folder');
        Route::get('/events/{owner}', [DashboardController::class, 'events'])->middleware('throttle:60,1')->name('events');
    });

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::middleware('guest:admin')->group(function () {
            Route::get('/login', [Admin\AuthController::class, 'create'])->name('login');
            Route::post('/login', [Admin\AuthController::class, 'store'])->middleware('throttle:5,1');
        });

        Route::middleware('auth:admin')->group(function () {
            Route::post('/logout', [Admin\AuthController::class, 'destroy'])->name('logout');
            Route::get('/', fn () => redirect()->route('admin.users.index'));
            Route::resource('users', Admin\UserController::class)->only(['index', 'edit', 'update', 'destroy']);
        });
    });
});
