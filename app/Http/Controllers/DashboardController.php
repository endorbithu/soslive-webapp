<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * A statikus (cache-elhető) oldalak egyetlen dinamikus adata: ki van belépve. Minden mást (eseménylista, config)
 * a böngésző a user saját Google tokenjével olvas közvetlenül a Drive-ból.
 */
class DashboardController extends Controller
{
    public function me(Request $request): JsonResponse
    {
        $me = $request->user();
        if (! $me) {
            return response()->json(['user' => null]);
        }

        return response()->json([
            'user' => ['name' => $me->name ?: $me->email, 'email' => $me->email],
            'csrf' => csrf_token(),
        ]);
    }
}
