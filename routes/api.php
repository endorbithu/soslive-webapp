<?php

use App\Http\Controllers\Api\MobileController;
use Illuminate\Support\Facades\Route;

// Mobil API – hitelesítés: Authorization: Bearer <Google ID token> (lásd docs/MOBILE_API.md)
Route::middleware(['google.id-token', 'throttle:60,1'])->group(function () {
    Route::post('/session', [MobileController::class, 'session'])->name('api.session');
    Route::get('/config', [MobileController::class, 'show'])->name('api.config');
    Route::put('/config', [MobileController::class, 'update'])->name('api.config.update');
});
