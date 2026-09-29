<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * Esemény végoldal: bárki megnyithatja, aki ismeri az URL-t. Az adatot a böngésző olvassa a Google-ből
 * API key-jel (az esemény fájl „bárki a linkkel olvashatja” megosztású). A web nem ír semmit.
 */
class EventController extends Controller
{
    public function show(string $spreadsheetId): View
    {
        return view('event', ['spreadsheetId' => $spreadsheetId]);
    }
}
