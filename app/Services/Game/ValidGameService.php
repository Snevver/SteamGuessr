<?php

namespace App\Services\Game;

class ValidGameService
{
    /**
     * Retrieve valid games from the session.
     *
     * Returns an array of games where each item contains at least `id` and `name`.
     * Only includes games that have been played (playtime > 0).
     * If there are no games in session this returns an empty array.
     *
     * @return array<int, array>
     */
    public function getValidGamesFromSession(): array
    {
        $allGames = $this->getAllGamesFromSession();

        if (!is_array($allGames) || empty($allGames)) return [];

        // Keep only entries with a non-empty id and name, and that have been played
        $validGames = array_values(array_filter($allGames, function ($game) {
            $playtime = $game['playtime'] ?? 0;
            return is_array($game)
                && isset($game['id']) && $game['id'] !== ''
                && isset($game['name']) && $game['name'] !== ''
                && $playtime > 0; // Only include games that have been played
        }));

        return $validGames;
    }

    /**
     * Wrapper for fetching games from session so it can be overridden in tests.
     *
     * @return array
     */
    protected function getAllGamesFromSession(): array
    {
        $games = session('allGames', []);
        return is_array($games) ? $games : [];
    }
}