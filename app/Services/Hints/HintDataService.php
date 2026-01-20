<?php

namespace App\Services\Hints;

use App\Services\Steam\SteamAPIClient;

class HintDataService
{
    private SteamAPIClient $steamClient;
    private array $appDetailsCache = [];
    private array $steamSpyDataCache = [];
    private array $currentPlayersCache = [];

    public function __construct(SteamAPIClient $steamClient)
    {
        $this->steamClient = $steamClient;
    }

    /**
     * Dynamically call the appropriate method based on the data key.
     *
     * @param string $key Key of the function that needs to be called
     * @param array $gameData Containing id, name, cover_url, playtime, etc.
     * @return mixed The requested data, or null if appId is required but missing
     */
    public function getDataByKey(string $key, array $gameData): mixed
    {
        $keyToMethod = config('hints.key_to_method');

        // Check if the key is valid
        if (!isset($keyToMethod[$key])) {
            throw new \InvalidArgumentException("Unknown data key: $key");
        }

        $method = $keyToMethod[$key];

        return $this->$method($gameData);
    }

    /**
     * Fetch app details from Steam Store API.
     * Results are cached within the request to avoid redundant API calls.
     * 
     * @param int $appId The Steam App ID
     * @return array|null App details array or null on failure
     */
    private function getAppDetails(int $appId): ?array
    {
        if (!isset($this->appDetailsCache[$appId])) {
            $this->appDetailsCache[$appId] = $this->steamClient->fetchAppDetails($appId);
        }

        return $this->appDetailsCache[$appId];
    }

    /**
     * Fetch SteamSpy data for owner counts and player stats.
     * Results are cached within the request to avoid redundant API calls.
     */
    private function getSteamSpyData(int $appId): ?array
    {
        if (!isset($this->steamSpyDataCache[$appId])) {
            $this->steamSpyDataCache[$appId] = $this->steamClient->fetchSteamSpyData($appId);
        }

        return $this->steamSpyDataCache[$appId];
    }

    /**
     * Get the first letter of the game's name (uppercase).
     * 
     * @param array $gameData The game data array containing 'name'
     * @return string The first letter of the game name in uppercase
     */
    public function getFirstLetterOfGame(array $gameData): ?string
    {
        $name = $gameData['name'] ?? null;
        return $name ? strtoupper(substr($name, 0, 1)) : null;
    }

    /**
     * Get the game's name with letters randomly shuffled.
     */
    public function getScrambledNameOfGame(array $gameData): ?string
    {
        $name = $gameData['name'] ?? null;
        return $name ? str_shuffle($name) : null;
    }

    /**
     * Get the game's banner/cover image URL.
     */
    public function getBannerUrlOfGame(array $gameData): ?string
    {
        return $gameData['cover_url'] ?? null;
    }

    /**
     * Get the game's primary developer.
     */
    public function getDeveloperOfGame(array $gameData): ?string
    {
        $details = $this->getAppDetails($gameData['id']);
        if ($details === null) {
            return null;
        }

        $developers = $details['developers'] ?? [];
        return !empty($developers) ? $developers[0] : null;
    }

    /**
     * Get the game's primary publisher.
     */
    public function getPublisherOfGame(array $gameData): ?string
    {
        $details = $this->getAppDetails($gameData['id']);
        $publishers = $details['publishers'] ?? [];

        return !empty($publishers) ? $publishers[0] : null;
    }

    /**
     * Get the game's genre tags.
     */
    public function getTagsOfGame(array $gameData): array
    {
        $details = $this->getAppDetails($gameData['id']);
        $genres = $details['genres'] ?? [];

        return array_map(fn($genre) => $genre['description'], $genres);
    }

    /**
     * Get the user's total playtime for this game (in minutes).
     */
    public function getPlaytimeOfGame(array $gameData): int
    {
        return $gameData['playtime'] ?? $gameData['playtime_forever'] ?? 0;
    }

    /**
     * Get the game's release date.
     */
    public function getReleaseDateOfGame(array $gameData): ?string
    {
        $details = $this->getAppDetails($gameData['id']);
        return $details['release_date']['date'] ?? null;
    }

    /**
     * Get the game's positive review percentage.
     * Falls back to Metacritic score if SteamSpy data unavailable.
     */
    public function getReviewRatioOfGame(array $gameData): ?string
    {
        $steamSpyData = $this->getSteamSpyData($gameData['id']);

        if ($steamSpyData) {
            $positive = $steamSpyData['positive'] ?? 0;
            $negative = $steamSpyData['negative'] ?? 0;
            $total = $positive + $negative;

            if ($total > 0) {
                return round(($positive / $total) * 100, 1) . '%';
            }
        }

        // Fallback to Metacritic score
        $details = $this->getAppDetails($gameData['id']);
        return isset($details['metacritic']['score']) ? $details['metacritic']['score'] . '%' : null;
    }

    /**
     * Get the game's total number of reviews.
     */
    public function getTotalReviewsOfGame(array $gameData): int
    {
        $steamSpyData = $this->getSteamSpyData($gameData['id']);

        if ($steamSpyData) {
            $positive = $steamSpyData['positive'] ?? 0;
            $negative = $steamSpyData['negative'] ?? 0;
            return $positive + $negative;
        }

        // Fallback to Steam Store API
        $details = $this->getAppDetails($gameData['id']);
        return $details['recommendations']['total'] ?? 0;
    }

    /**
     * Get the game's required disk space from PC requirements.
     */
    public function getRequiredDiskSpaceOfGame(array $gameData): ?string
    {
        $details = $this->getAppDetails($gameData['id']);
        $pcReqs = $details['pc_requirements'] ?? [];

        if (empty($pcReqs) || !is_array($pcReqs)) {
            return null;
        }

        $minimum = $pcReqs['minimum'] ?? '';

        if (!empty($minimum)) {
            $patterns = config('hints.disk_space_patterns');

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $minimum, $matches)) {
                    $space = trim(strip_tags($matches[1]));
                    $space = preg_replace('/\s*(available|of free|free)\s*$/i', '', $space);
                    return trim($space);
                }
            }
        }

        return null;
    }

    /**
     * Get the estimated total owners of the game (from SteamSpy).
     */
    public function getTotalOwnersOfGame(array $gameData): ?string
    {
        $steamSpyData = $this->getSteamSpyData($gameData['id']);

        return $steamSpyData['owners'] ?? null;
    }

    /**
     * Get the current number of players in-game.
     */
    public function getCurrentPlayersOfGame(array $gameData): int
    {
        $appId = $gameData['id'] ?? null;

        if (empty($appId)) {
            return 0;
        }

        if (!isset($this->currentPlayersCache[$appId])) {
            $this->currentPlayersCache[$appId] = $this->steamClient->fetchCurrentPlayers($appId) ?? 0;
        }

        return $this->currentPlayersCache[$appId];
    }
}
