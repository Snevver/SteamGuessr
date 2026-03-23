<?php

namespace App\Http\Controllers;

use App\Services\Hints\HintService;
use App\Services\Game\ValidGameService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ClassicGamemodeController extends Controller
{
    public function __construct(
        private HintService $hintService,
        private ValidGameService $validGameService
    ) {}

    /**
     * Get a random game and all hints with their data for the classic gamemode.
     */
    public function index(): JsonResponse
    {
        try {
            // Get all valid games from session
            $validGames = $this->validGameService->getValidGamesFromSession();

            // If no valid games, return 404
            if (empty($validGames)) return new JsonResponse(['error' => 'No valid games found in session'], Response::HTTP_NOT_FOUND);

            // Pick a random game and get all hints with their data
            $randomGame = $validGames[array_rand($validGames)];
            $allHintsWithData = $this->hintService->getAllHintsWithData($randomGame);

            // Get info of the random game
            $gameInfo = [
                'id' => $randomGame['id'] ?? $randomGame['appid'] ?? null,
                'name' => $randomGame['name'] ?? null,
                'cover_url' => $randomGame['cover_url'] ?? null,
            ];

            // Return all hints with their data and the correct game info as JSON
            $payload = [
                'hints_data' => $allHintsWithData,
                'game' => $gameInfo,
            ];

            return new JsonResponse($payload, Response::HTTP_OK);
        } catch (\Throwable $e) {
            // Fallback to basic error_log here instead of the Log facade
            // so that this controller can still be tested as a pure unit
            // test without bootstrapping Laravel's application container.
            error_log('ClassicGamemodeController@index error: ' . $e->getMessage());

            return new JsonResponse([
                'error' => 'Failed to prepare classic gamemode',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}