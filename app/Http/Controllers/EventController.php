<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

/**
 * Esemény végoldal: bárki megnyithatja, aki ismeri az URL-t. Az adatot a böngésző olvassa a Google-ből
 * API key-jel (az esemény fájl „bárki a linkkel olvashatja” megosztású). A web nem ír semmit.
 * A keresők ne indexeljék: a link csak annak szól, akinek a mobil app kiküldte.
 */
class EventController extends Controller
{
    public function show(string $spreadsheetId): Response
    {
        return response()
            ->view('event', ['spreadsheetId' => $spreadsheetId])
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
