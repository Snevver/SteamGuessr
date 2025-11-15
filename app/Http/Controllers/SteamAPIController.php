<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SteamAPIService;

class SteamAPIController extends Controller
{
    /**
     * Get the users basic info
     * 
     * @param Request $request
     * @param SteamAPIService $steam
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBasicInfo(Request $request, SteamAPIService $steam)
    {
        // Validate the request
        $request->validate([
            'steamID' => 'required|string'
        ]);

        // Fetch player summary from Steam API
        try {
            $response = $steam->fetchPlayerSummary($request->steamID);

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json(['error' => 'Steam API request failed'], 400);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
