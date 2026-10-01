<?php

namespace App\Http\Controllers;

use App\Support\DemoEvents;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * „Ál-Drive” a demó eseményekhez – csak nem production környezetben. A végoldal JS-e a `demo-` kezdetű
 * azonosítóknál ide fordul a Google Drive API helyett, ugyanazzal a kérés- és válaszformával:
 * `?fields=…` → metaadat, `?alt=media` → az esemény JSON tartalma, ismeretlen / törölt → 404.
 */
class DemoDriveController extends Controller
{
    public function show(Request $request, string $fileId): JsonResponse
    {
        abort_if(app()->isProduction(), 404);

        $event = DemoEvents::find($fileId);
        if (! $event) {
            return response()->json(['error' => ['code' => 404, 'message' => 'File not found: '.$fileId]], 404);
        }

        if ($request->query('alt') === 'media') {
            return response()->json($event['content']);
        }

        return response()->json([
            'name' => $event['name'],
            'modifiedTime' => $event['modifiedTime'],
            'trashed' => $event['trashed'],
        ]);
    }
}
