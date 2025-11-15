<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SteamAPIService
{
    // Define the Steam API endpoints
    private string $numericIDendpoint = 'https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v2/';
    private string $customURLEndpoint = 'https://api.steampowered.com/ISteamUser/ResolveVanityURL/v1/';

    /**
     * Extract Steam ID from various input formats (URL, numeric ID, custom ID)
     * 
     * @param string $input
     * @return array ['id' => string, 'type' => 'numeric'|'custom']
     */
    public function extractSteamID(string $input): array
    {
        // Remove whitespace
        $input = trim($input);

        // Check if it's a Steam profile URL with numeric ID
        if (preg_match('/https?:\/\/steamcommunity\.com\/profiles\/(\d+)\/?/', $input, $matches)) {
            return ['id' => $matches[1], 'type' => 'numeric'];
        }

        // Check if it's a Steam custom URL
        if (preg_match('/https?:\/\/steamcommunity\.com\/id\/([^\/?]+)\/?/', $input, $matches)) {
            return ['id' => $matches[1], 'type' => 'custom'];
        }

        // Check if it's a numeric Steam ID (17 digits starting with 7656119)
        if (preg_match('/^7656119\d{10}$/', $input)) {
            return ['id' => $input, 'type' => 'numeric'];
        }

        // Assume it's a custom ID if it doesn't match the above patterns
        return ['id' => $input, 'type' => 'custom'];
    }

    /**
     * Fetch player summary from Steam API
     * 
     * @param string $input Raw input (URL, numeric ID, or custom ID)
     * @return \Illuminate\Http\Client\Response
     */
    public function fetchPlayerSummary(string $input)
    {
        $steamData = $this->extractSteamID($input);
        $steamID = $steamData['id'];
        $type = $steamData['type'];

        if ($type === 'custom') {
            // Resolve custom URL to numeric SteamID
            $resolveResponse = Http::get($this->customURLEndpoint, [
                'key' => config('steam.key'),
                'vanityurl' => $steamID,
            ]);

            if ($resolveResponse->successful() && $resolveResponse->json('response.success') === 1) {
                $steamID = $resolveResponse->json('response.steamid');
            } else {
                throw new \Exception('Failed to resolve custom URL to SteamID.');
            }
        }

        return Http::get($this->numericIDendpoint, [
            'key' => config('steam.key'),
            'steamids' => $steamID,
        ]);
    }
}
