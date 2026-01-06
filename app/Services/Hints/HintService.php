<?php

namespace App\Services\Hints;

use Illuminate\Support\Facades\File;

class HintService
{
    public function __construct(
        private HintDataService $dataService
    ) {}

    /**
     * Get all available hints from the registry.
     *
     * Reads all hints from registry/hints.json and returns them in a flat structure.
     *
     * @return array<string, array{hint_name: string, needed_data_keys: array}>
     * @throws \RuntimeException If hints.json is missing or contains invalid JSON
     */
    public function getAllHints(): array
    {
        $hintsPath = base_path('registry/hints.json');

        if (!File::exists($hintsPath)) {
            throw new \RuntimeException('Hints configuration file not found: registry/hints.json');
        }

        $hintsContent = File::get($hintsPath);
        $hints = json_decode($hintsContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON in hints.json: ' . json_last_error_msg());
        }

        if (!isset($hints['hints']) || !is_array($hints['hints'])) {
            throw new \RuntimeException('Invalid hints.json structure: missing or invalid "hints" key');
        }

        $allHints = [];

        foreach ($hints['hints'] as $hintName => $hintData) {
            $allHints[$hintName] = [
                'hint_name' => $hintName,
                'needed_data_keys' => $hintData['neededData'],
            ];
        }

        return $allHints;
    }

    /**
     * Fetch the required data for all available hints.
     *
     * @param array $gameData The game being guessed, containing 'id', 'name', 'playtime', etc.
     * @return array<string, array{hint_name: string, needed_data_keys: array, data: array}>
     */
    public function getAllHintsWithData(array $gameData): array
    {
        $allHints = $this->getAllHints();
        $allHintsWithData = [];

        foreach ($allHints as $hintKey => $hint) {
            $hintData = [];
            foreach ($hint['needed_data_keys'] as $key) {
                $hintData[$key] = $this->dataService->getDataByKey($key, $gameData);
            }

            $allHintsWithData[$hintKey] = [
                'hint_name' => $hint['hint_name'],
                'data' => $hintData,
            ];
        }

        return $allHintsWithData;
    }
}