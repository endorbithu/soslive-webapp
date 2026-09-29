<?php

namespace App\Http\Controllers;

use App\Exceptions\GoogleReauthRequired;
use App\Models\User;
use App\Services\GoogleDrive;
use App\Support\UserConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * A statikus (cache-elhető) oldalak dinamikus adatai: ki van belépve, kiknek az eseményeit láthatja, eseménylista.
 */
class DashboardController extends Controller
{
    /**
     * Minden, amit a statikus oldalak a belépett userről tudni akarnak; vendégnek `user: null`.
     */
    public function me(Request $request): JsonResponse
    {
        $me = $request->user();
        if (! $me) {
            return response()->json(['user' => null]);
        }

        return response()->json([
            'user' => ['name' => $me->name ?: $me->email, 'email' => $me->email],
            'csrf' => csrf_token(),
            'owners' => $me->visibleOwners()->map(fn (User $owner) => [
                'id' => $owner->id,
                'name' => $owner->name ?: $owner->email,
                'email' => $owner->email,
                'is_me' => $owner->is($me),
            ])->values()->all(),
            'config' => UserConfig::toArray($me),
        ]);
    }

    /**
     * Egy tulaj eseménylistája (csak ID, cím, idő) – a backend a tulaj tokenjével kéri le a Drive-ból,
     * a token nem kerül a böngészőhöz. Nem cache-eljük: ami törölve van a Drive-ból, itt sem látszik.
     */
    public function events(Request $request, User $owner, GoogleDrive $drive): JsonResponse
    {
        $me = $request->user();
        abort_unless($owner->isVisibleTo($me), 403);
        $isOwner = $me->is($owner);

        try {
            $token = $drive->accessTokenFor($owner)['access_token'];
        } catch (GoogleReauthRequired) {
            return response()->json(['error' => $isOwner ? 'reauth' : 'owner_reauth'], $isOwner ? 401 : 409);
        }

        $limit = min(config('soslive.list_limit'), $owner->max_events);
        $events = $owner->drive_folder_id ? $drive->listEvents($token, $owner->drive_folder_id, $limit) : [];

        return response()->json([
            'events' => $events,
            // Üres listánál megnézzük, él-e még a mappa (a törölt mappa gyerekei is eltűnnek a listából).
            'folder_missing' => ! $events && ! $drive->folderAlive($token, $owner->drive_folder_id),
            'is_owner' => $isOwner,
        ]);
    }
}
