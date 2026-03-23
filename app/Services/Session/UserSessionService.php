<?php

namespace App\Services\Session;

use Illuminate\Session\Store;

class UserSessionService
{
    private Store $session;
    private const PRESERVE_KEYS = ['_token'];

    public function __construct(Store $session)
    {
        $this->session = $session;
    }

    /**
     * Persist user/session data returned from stats/identity lookups.
     *
     * @param string $steamId
     * @param array|null $player
     * @param array $ownedStats
     * @param array|null $timeCreated
     * @return void
     */
    public function storeUserSession(string $steamId, ?array $player, array $ownedStats, ?array $timeCreated, string $personaState): void
    {
        $this->session->put([
            'userSteamID' => $steamId,
            'personaState' => $personaState,
            'publicProfile' => ($player['communityvisibilitystate'] ?? 0) === (int) config('steam.public_visibility_state', 3),
            'steamProfileURL' => $player['profileurl'] ?? null,
            'profilePictureURL' => $player['avatarfull'] ?? null,
            'username' => $player['personaname'] ?? null,
            'timeCreated' => $timeCreated['date'] ?? null,
            'accountAge' => $timeCreated['age'] ?? null,
            'totalGamesOwned' => $ownedStats['game_count'] ?? 0,
            'totalPlaytimeMinutes' => $ownedStats['total_playtime_minutes'] ?? 0,
            'averagePlaytimeMinutes' => round($ownedStats['average_playtime_minutes']) ?? 0,
            'topGames' => $ownedStats['top_games'] ?? [],
            'playedPercentage' => $ownedStats['played_percentage'] ?? 0.00,
            'allGames' => $ownedStats['all_games'] ?? [],
        ]);
    }

    /**
     * Clear all session keys except a small preserve list.
     * Keeps `_token` by default to avoid 419 CSRF errors.
     * 
     * @return void
     */
    public function clearExceptPreserve(): void
    {
        $allKeys = array_keys($this->session->all());
        $keysToForget = array_diff($allKeys, self::PRESERVE_KEYS);

        // forget the filtered keys
        if (!empty($keysToForget)) $this->session->forget($keysToForget);
    }
}