<?php

namespace App\Http\Controllers;

use App\Exceptions\GoogleReauthRequired;
use App\Models\User;
use App\Services\GoogleDrive;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function home(Request $request)
    {
        return $request->user() ? redirect()->route('dashboard') : view('home');
    }

    public function index(Request $request): View
    {
        $me = $request->user();

        return view('dashboard', [
            'owners' => $me->visibleOwners()->map(fn (User $owner) => [
                'id' => $owner->id,
                'name' => $owner->name ?: $owner->email,
                'email' => $owner->email,
                'is_me' => $owner->is($me),
            ])->values()->all(),
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
        ])->header('Cache-Control', 'no-store');
    }
}
