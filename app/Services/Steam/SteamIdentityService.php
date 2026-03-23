<?php

namespace App\Services\Steam;

class SteamIdentityService
{
    private SteamAPIClient $client;

    /**
     * Create a new SteamIdentityService.
     *
     * @param SteamAPIClient $client
     */
    public function __construct(SteamAPIClient $client)
    {
        $this->client = $client;
    }

    /**
     * Sanitize input and resolve vanity names when needed.
     *
     * @param string $input Raw input (URL, numeric ID, or custom ID)
     * @param bool $isCustomID Whether the provided input should be treated as a custom ID
     * @return string|null Numeric SteamID or null if resolution fails
     */
    public function sanitizeInput(string $input, bool $isCustomID): ?string
    {
        // Trim whitespace from the input
        $input = trim($input);
        if ($input === '') return null;

        // If the input looks like a URL, extract the last path segment
        if (filter_var($input, FILTER_VALIDATE_URL)) {
            $path = parse_url($input, PHP_URL_PATH) ?? '';
            $segments = array_values(array_filter(explode('/', $path)));

            if (!empty($segments)) {
                $input = end($segments);
            }
        }

        if ($isCustomID) return $this->client->resolveVanityUrl($input);
        
        // For non-custom IDs, expect a numeric SteamID64 (17 digits).
        // If it does not match this pattern, treat it as invalid.
        if (!preg_match('/^\d{17}$/', $input)) return null;

        return $input;
    }

    /**
     * Get the meaning of a Steam persona state code.
     *
     * @param int $personaState Numeric persona state code
     * @return string Human-readable persona state
     */
    public function getPersonaStateMeaning(int $personaState): string
    {
        return match ($personaState) {
            0 => 'Offline',
            1 => 'Online',
            2 => 'Busy',
            3 => 'Away',
            4 => 'Snooze',
            5 => 'Looking to trade',
            6 => 'Looking to play',
            default => 'Unknown',
        };
    }
}