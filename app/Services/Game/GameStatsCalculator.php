<?php

namespace App\Services\Game;

class GameStatsCalculator
{
    /**
     * Compute all stats in one call.
     */
    public function computeAll(array $games, int $topN = 3): array
    {
        return [
            'game_count' => $this->getGameCount($games),
            'total_playtime_minutes' => $this->getTotalPlaytimeMinutes($games),
            'average_playtime_minutes' => $this->getAveragePlaytimeMinutes($games),
            'top_games' => $this->getTopGames($games, $topN),
            'played_percentage' => $this->getPlayedPercentage($games),
            'all_games' => $this->getAllGamesWithNames($games),
        ];
    }

    /**
     * Return number of games.
     */
    public function getGameCount(array $games): int
    {
        return count($games);
    }

    /**
     * Sum playtime_forever for all games (minutes).
     */
    public function getTotalPlaytimeMinutes(array $games): int
    {
        $total = 0;

        foreach ($games as $game) {
            $total += isset($game['playtime_forever']) ? (int)$game['playtime_forever'] : 0;
        }

        return $total;
    }


    /**
     * Average playtime per owned game.
     */
    public function getAveragePlaytimeMinutes(array $games): int
    {
        $count = $this->getGameCount($games);

        return $count > 0 ? (int) ($this->getTotalPlaytimeMinutes($games) / $count) : 0;
    }

    /**
     * Return top N games by playtime_forever.
     */
    public function getTopGames(array $games, int $topN = 3): array
    {
        usort($games, fn($a, $b) => ($b['playtime_forever'] ?? 0) <=> ($a['playtime_forever'] ?? 0));

        return array_map(function (array $game): array {
            $appid = $game['appid'] ?? null;

            return [
                'appid' => $appid,
                'name' => $game['name'] ?? null,
                'playtime_forever' => (int) ($game['playtime_forever'] ?? 0),
                'cover_url' => $appid ? "https://steamcdn-a.akamaihd.net/steam/apps/{$appid}/capsule_616x353.jpg" : null,
            ];
        }, array_slice($games, 0, $topN));
    }

    /**
     * Percentage of games that have been played (0-100).
     */
    public function getPlayedPercentage(array $games): float
    {
        $total = $this->getGameCount($games);

        if ($total === 0) {
            return 0.0;
        }

        $played = count(array_filter($games, fn($game) => ($game['playtime_forever'] ?? 0) > 0));

        return round(($played / $total) * 100, 2);
    }

    /**
     * Extract all games with their IDs, names, and metadata.
     */
    public function getAllGamesWithNames(array $games): array
    {
        return array_map(function (array $game): array {
            $appid = $game['appid'] ?? null;

            return [
                'id' => $appid,
                'name' => $game['name'] ?? null,
                'cover_url' => $appid ? "https://steamcdn-a.akamaihd.net/steam/apps/{$appid}/capsule_616x353.jpg" : null,
                'playtime' => $game['playtime_forever'] ?? 0,
            ];
        }, $games);
    }
}
