<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        // Cookie- és session-mentes, reverse proxyval cache-elhető oldalak.
        then: fn () => Route::middleware('static')->group(base_path('routes/static.php')),
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->group('static', [SubstituteBindings::class]);
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('app/admin', 'app/admin/*')
            ? route('admin.login')
            : route('home'));
        $middleware->redirectUsersTo(fn (Request $request) => $request->is('app/admin', 'app/admin/*')
            ? route('admin.users.index')
            : route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
