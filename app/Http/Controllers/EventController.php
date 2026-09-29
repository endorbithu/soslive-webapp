<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Esemény végoldal: bárki megnyithatja, aki ismeri az URL-t. Az adatot a böngésző olvassa a Google-ből
 * (vendégként API key-jel, bejelentkezve a tulaj tokenjével, ami a chat írást is engedi).
 */
class EventController extends Controller
{
    public function show(Request $request, string $spreadsheetId): View
    {
        return view('event', [
            'spreadsheetId' => $spreadsheetId,
            'owners' => $request->user() ? DashboardController::ownersFor($request->user()) : [],
        ]);
    }
}
