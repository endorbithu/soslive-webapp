<?php

use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;

/*
 * Statikus oldalak: session, cookie és CSRF nélkül, mindenkinek ugyanaz a HTML, így reverse proxy
 * (Varnish, Cloudflare) cache-elheti őket. A userfüggő adatot a JS kéri le az /app/... végpontokról.
 * Ide csak olyan oldal kerülhet, ami semmilyen user- vagy session-adatot nem renderel!
 */

$cacheHeaders = sprintf(
    'cache.headers:public;max_age=%d;s_maxage=%d;etag',
    config('soslive.page_cache.max_age'),
    config('soslive.page_cache.s_maxage'),
);

Route::middleware($cacheHeaders)->group(function () {
    Route::view('/', 'home')->name('home');
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::view('/settings', 'settings')->name('settings');

    // Publikus esemény végoldal – aki ismeri az URL-t, láthatja.
    Route::get('/e/{fileId}', [EventController::class, 'show'])
        ->where('fileId', '[A-Za-z0-9_-]{20,100}')
        ->name('event');
});
