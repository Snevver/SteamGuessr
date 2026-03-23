<?php

namespace App\Services\Game;

class GameStatsCalculator
{
    /**
     * Compute all stats in one call.
     * 
     * @param array $games List of games from Steam API
     * @param int $topN Number of top games to return
     * @return array{
     *   game_count:int,
     *   total_playtime_minutes:int,
     *   average_playtime_minutes:int,
     *   top_games:array,
     *   played_percentage:float,
     *   all_games:array
     * }
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
     *
     * @param array $games List of games from the Steam API
     * @return int Total number of games
     */
    public function getGameCount(array $games): int
    {
        return count($games);
    }

    /**
     * Sum playtime_forever for all games (minutes).
     *
     * Each game's playtime is read from the `playtime_forever` field
     * (in minutes). Missing values are treated as 0.
     *
     * @param array $games List of games from the Steam API
     * @return int Total playtime in minutes across all games
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
     *
     * Uses the total playtime divided by the number of games.
     * If there are no games, this returns 0.
     *
     * @param array $games List of games from the Steam API
     * @return int Average playtime in minutes per game
     */
    public function getAveragePlaytimeMinutes(array $games): int
    {
        $count = $this->getGameCount($games);

        return $count > 0 ? (int) ($this->getTotalPlaytimeMinutes($games) / $count) : 0;
    }

    /**
     * Return top N games by playtime_forever.
     *
     * Sorts the games so that the highest total playtime comes first
     * and returns only the first N games with a simplified structure.
     *
     * @param array $games List of games from the Steam API
     * @param int $topN Number of top games to return
     * @return array List of top games with appid, name, playtime, and cover URL
     */
    public function getTopGames(array $games, int $topN = 3): array
    {
        // Sort the games array so that the game with the highest
        // total playtime (playtime_forever) comes first, and the
        // one with the lowest playtime comes last.
        usort($games, function (array $firstGame, array $secondGame): int {
            $firstPlaytime = isset($firstGame['playtime_forever']) ? (int) $firstGame['playtime_forever'] : 0;
            $secondPlaytime = isset($secondGame['playtime_forever']) ? (int) $secondGame['playtime_forever'] : 0;

            return $secondPlaytime <=> $firstPlaytime;
        });

        $topGames = array_slice($games, 0, $topN);

        $mappedGames = [];

        foreach ($topGames as $game) {
            $appid = isset($game['appid']) ? $game['appid'] : null;
            $playtime = isset($game['playtime_forever']) ? (int) $game['playtime_forever'] : 0;

            $mappedGames[] = [
                'appid' => $appid,
                'name' => isset($game['name']) ? $game['name'] : null,
                'playtime_forever' => $playtime,
                'cover_url' => $appid
                    ? "https://steamcdn-a.akamaihd.net/steam/apps/{$appid}/capsule_616x353.jpg"
                    : null,
            ];
        }

        return $mappedGames;
    }

    /**
     * Percentage of games that have been played (0-100).
     *
     * A game counts as played if `playtime_forever` is greater than 0.
     * If there are no games, this returns 0.0.
     *
     * @param array $games List of games from the Steam API
     * @return float Percentage of games that have been played
     */
    public function getPlayedPercentage(array $games): float
    {
        $total = $this->getGameCount($games);

        if ($total === 0) {
            return 0.0;
        }

        $played = 0;

        foreach ($games as $game) {
            $playtime = isset($game['playtime_forever']) ? (int) $game['playtime_forever'] : 0;

            if ($playtime > 0) {
                $played++;
            }
        }

        return round(($played / $total) * 100, 2);
    }

    /**
     * Extract all games with their IDs, names, and metadata.
     *
     * Returns a simplified list of games with a consistent structure
     * for use on the frontend.
     *
     * @param array $games List of games from the Steam API
     * @return array List of games with id, name, cover URL, and playtime
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