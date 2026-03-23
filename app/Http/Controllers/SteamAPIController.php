<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValidateUserRequest;
use App\Services\Steam\SteamAPIClient;
use App\Services\Steam\SteamIdentityService;
use App\Services\Steam\SteamStatsService;
use App\Services\Session\UserSessionService;
use App\Services\Validation\ValidationResponseService;
use Illuminate\Support\Facades\Log;

class SteamAPIController extends Controller
{
    private const RESPONSE_INVALID = 1;

    public function __construct(
        private SteamIdentityService $identity,
        private SteamAPIClient $client,
        private SteamStatsService $stats,
        private UserSessionService $userSession
    ) {}

    /**
     * Get the users basic info and saves it to the session.
     *
     * Returns JSON with:
     * - 1 = invalid user
     * - 2 = private profile
     * - 3 = public profile
     *
     * @param ValidateUserRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateUser(ValidateUserRequest $request): \Illuminate\Http\JsonResponse
    {
        // Clear session keys except a small preserve list (keeps `_token` so we avoid 419s).
        $this->userSession->clearExceptPreserve();

        try {
            $isPublicProfile = false;
            $player = null;

            // Get numeric SteamID
            $userSteamID = $this->identity->sanitizeInput($request->userSteamID, $request->isCustomID);

            // If resolution failed, return invalid immediately
            if (empty($userSteamID)) return response()->json(self::RESPONSE_INVALID);

            // Get basic user info from Steam API
            $response = $this->client->fetchPlayerSummary($userSteamID);

            if ($response->successful()) {
                $json = $response->json();
                $player = $json['response']['players'][0] ?? null;

                if ($player) {
                    $visibilityState = $player['communityvisibilitystate'] ?? 0;
                    $publicVisibility = (int) config('steam.public_visibility_state', 3);
                    $isPublicProfile = $visibilityState === $publicVisibility;

                    if ($isPublicProfile) {
                        try {
                            $ownedStats = $this->stats->getOwnedGamesStats($userSteamID, 3);
                            $timeCreated = $this->stats->getAccountAgeAndCreationDate($player['timecreated'] ?? null);
                            $personaState = $this->identity->getPersonaStateMeaning($player['personastate'] ?? 0);

                            // Put all relevant user data into the session
                            $this->userSession->storeUserSession($userSteamID, $player, $ownedStats, $timeCreated, $personaState);
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