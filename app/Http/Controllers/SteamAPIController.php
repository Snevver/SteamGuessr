<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Steam\SteamAPIClient;
use App\Services\Steam\SteamIdentityService;
use App\Services\Steam\SteamStatsService;
use App\Services\Session\UserSessionService;
use App\Services\Validation\ValidationResponseService;
use Illuminate\Support\Facades\Log;

class SteamAPIController extends Controller
{
    // Response codes
    private const RESPONSE_INVALID = 1;
    private const STEAM_PUBLIC_VISIBILITY = 3;

    /**
     * Get the users basic info and saves it to the session.
     *
     * Returns JSON with:
     * - 1 = invalid user
     * - 2 = private profile
     * - 3 = public profile
     *
     * @param Request $request
     * @param SteamIdentityService $identity
     * @param SteamAPIClient $client
     * @param SteamStatsService $stats
     * @param UserSessionService $userSession
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateUser(Request $request, SteamIdentityService $identity, SteamAPIClient $client, SteamStatsService $stats, UserSessionService $userSession)
    {
        // Validate the request
        $request->validate([
            'userSteamID' => 'required|string',
            'isCustomID' => 'required|boolean'
        ]);

        // Clear session keys except a small preserve list (keeps `_token` so we avoid 419s).
        $userSession->clearExceptPreserve();

        // Fetch player summary from Steam API
        try {
            // Init variables to dodge undefined variable errors
            $isPublicProfile = false;
            $player = null;

            // Get numeric SteamID
            $userSteamID = $identity->sanitizeInput($request->userSteamID, $request->isCustomID);

            // If resolution failed, return invalid immediately and avoid calling Steam with null
            if (empty($userSteamID)) {
                return response()->json(self::RESPONSE_INVALID);
            }

            $response = $client->fetchPlayerSummary($userSteamID);

            if ($response->successful()) {
                $json = $response->json();
                $player = $json['response']['players'][0] ?? null;

                if ($player) {
                    $isPublicProfile = ($player['communityvisibilitystate'] ?? 0) === self::STEAM_PUBLIC_VISIBILITY;

                    if ($userSteamID && $isPublicProfile) {
                        try {
                            $ownedStats = $stats->getOwnedGamesStats($userSteamID, 3);
                            $timeCreated = $stats->getAccountAgeAndCreationDate($player['timecreated'] ?? null);
                            $personaState = $identity->getPersonaStateMeaning($player['personastate'] ?? 0);

                            // Put all relevant user data into the session
                            $userSession->storeUserSession($userSteamID, $player, $ownedStats, $timeCreated, $personaState);
                        } catch (\Throwable $exception) {
                            Log::error('Failed to write session data', [
                                'exception' => $exception->getMessage(),
                            ]);
                        }
                    }
                }
            }

            return response()->json(ValidationResponseService::determine($userSteamID, $isPublicProfile));
        } catch (\Throwable $e) {
            Log::error('validateUser error: ' . $e->getMessage());
            return response()->json(ValidationResponseService::INVALID);
        }
    }
}