<?php

namespace App\Http\Controllers;

use App\Exceptions\GoogleReauthRequired;
use App\Models\User;
use App\Services\GoogleDrive;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Rövid életű Google access tokent ad a böngészőnek a tulaj nevében, hogy az közvetlenül
 * a Drive / Sheets API-val olvassa és írja az eseményeket. Az adat a backenden nem megy át.
 */
class TokenController extends Controller
{
    public function show(Request $request, User $owner, GoogleDrive $drive): JsonResponse
    {
        $me = $request->user();
        abort_unless($owner->isVisibleTo($me), 403);

        try {
            $token = $drive->accessTokenFor($owner);
        } catch (GoogleReauthRequired) {
            return response()->json([
                'error' => $me->is($owner) ? 'reauth' : 'owner_reauth',
            ], $me->is($owner) ? 401 : 409);
        }

        return response()->json([
            'access_token' => $token['access_token'],
            'expires_at' => $token['expires_at'],
            'folder_id' => $owner->drive_folder_id,
            'max_events' => $owner->max_events,
            'is_owner' => $me->is($owner),
        ])->header('Cache-Control', 'no-store');
    }
}
