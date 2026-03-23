<?php

namespace Tests\Unit;

use App\Services\Game\GameStatsCalculator;
use PHPUnit\Framework\TestCase;

class GameStatsCalculatorTest extends TestCase
{
    private GameStatsCalculator $calculator;

    /**
     * Runs before each test to create a fresh calculator
     */
    protected function setUp(): void
    {
        $this->calculator = new GameStatsCalculator();
    }

    /**
     * Tests if game count returns 0 for empty array
     * @return void
     */
    public function testGetGameCountWithEmptyArray(): void
    {
        $result = $this->calculator->getGameCount([]);

        $this->assertEquals(0, $result);
    }

    /**
     * Tests if game count returns 1 for single game
     * @return void
     */
    public function testGetGameCountWithSingleGame(): void
    {
        $games = [['appid' => 1]];

        $result = $this->calculator->getGameCount($games);

        $this->assertEquals(1, $result);
    }

    /**
     * Tests if game count returns correct number for multiple games
     * @return void
     */
    public function testGetGameCountWithMultipleGames(): void
    {
        $games = [
            ['appid' => 1],
            ['appid' => 2],
            ['appid' => 3]
        ];

        $result = $this->calculator->getGameCount($games);

        $this->assertEquals(3, $result);
    }

    /**
     * Tests if total playtime is summed correctly, skipping invalid entries
     * @return void
     */
    public function testGetTotalPlaytimeMinutes(): void
    {
        $games = [
            ['playtime_forever' => 60],
            ['playtime_forever' => 120],
            ['name' => null], // Missing playtime - should be skipped (edge case)
            ['playtime_forever' => 30],
        ];

        $result = $this->calculator->getTotalPlaytimeMinutes($games);

        // 60 + 120 + 30 = 210
        $this->assertEquals(210, $result);
    }

    /**
     * Tests if average playtime returns 0 for empty array
     * @return void
     */
    public function testGetAveragePlaytimeWithEmptyArray(): void
    {
        $result = $this->calculator->getAveragePlaytimeMinutes([]);

        $this->assertEquals(0, $result);
    }

    /**
     * Tests if average playtime is calculated correctly for single game
     * @return void
     */
    public function testGetAveragePlaytimeWithSingleGame(): void
    {
        $games = [['playtime_forever' => 60]];

        $result = $this->calculator->getAveragePlaytimeMinutes($games);

        $this->assertEquals(60, $result);
    }

    /**
     * Tests if average playtime is calculated correctly for multiple games
     * @return void
     */
    public function testGetAveragePlaytimeWithMultipleGames(): void
    {
        $games = [
            ['playtime_forever' => 60],
            ['playtime_forever' => 120],
            ['playtime_forever' => 30],
        ];

        $result = $this->calculator->getAveragePlaytimeMinutes($games);

        // (60 + 120 + 30) / 3 = 70
        $this->assertEquals(70, $result);
    }

    /**
     * Tests if top games are sorted by playtime and limited to requested count
     * @return void
     */
    public function testGetTopGames(): void
    {
        $games = [
            ['appid' => 1, 'name' => 'Game A', 'playtime_forever' => 30],
            ['appid' => 2, 'name' => 'Game B', 'playtime_forever' => 60],
            ['appid' => 3, 'name' => 'Game C', 'playtime_forever' => 10],
            ['appid' => 4, 'name' => 'Game D', 'playtime_forever' => 50],
            ['appid' => 5, 'name' => 'Game E', 'playtime_forever' => 40],
        ];

        $result = $this->calculator->getTopGames($games, 3);

        // Should return only 3 games
        $this->assertCount(3, $result);

        // Should be sorted by playtime (highest first)
        $this->assertEquals(2, $result[0]['appid']); // Game B, 60 minutes
        $this->assertEquals(4, $result[1]['appid']); // Game D, 50 minutes
        $this->assertEquals(5, $result[2]['appid']); // Game E, 40 minutes
    }

    /**
     * Tests if played percentage returns 0 when no games are played
     * @return void
     */
    public function testGetPlayedPercentageWithNoGamesPlayed(): void
    {
        $games = [
            ['playtime_forever' => 0],
            ['playtime_forever' => 0],
        ];

        $result = $this->calculator->getPlayedPercentage($games);

        $this->assertEquals(0.0, $result);
    }

    /**
     * Tests if played percentage returns 100 when all games are played
     * @return void
     */
    public function testGetPlayedPercentageWithAllGamesPlayed(): void
    {
        $games = [
            ['playtime_forever' => 10],
            ['playtime_forever' => 20],
        ];

        $result = $this->calculator->getPlayedPercentage($games);

        $this->assertEquals(100.0, $result);
    }

    /**
     * Tests if played percentage calculates correctly for partially played library
     * @return void
     */
    public function testGetPlayedPercentageWithPartiallyPlayedGames(): void
    {
        $games = [
            ['playtime_forever' => 10],
            ['playtime_forever' => 0],
            ['playtime_forever' => 20],
        ];

        $result = $this->calculator->getPlayedPercentage($games);

        // 2 out of 3 games played = 66.67%
        $this->assertEquals(round((2 / 3) * 100, 2), $result);
    }

    /**
     * Tests if computeAll returns all statistics correctly
     * 
     * This method calls all other methods and combines the results
     * @return void
     */
    public function testComputeAll(): void
    {
        $games = [
            ['appid' => 1, 'name' => 'Game A', 'playtime_forever' => 30],
            ['appid' => 2, 'name' => 'Game B', 'playtime_forever' => 60],
            ['appid' => 3, 'name' => 'Game C', 'playtime_forever' => 9],
        ];

        $result = $this->calculator->computeAll($games, 2);

        // Check all the computed statistics
        $this->assertEquals(3, $result['game_count']);
        $this->assertEquals(99, $result['total_playtime_minutes']);
        $this->assertEquals(33, $result['average_playtime_minutes']);
        $this->assertEquals(100.0, $result['played_percentage']);

        // Check top games (should only return 2 as requested)
        $this->assertCount(2, $result['top_games']);
        $this->assertEquals(2, $result['top_games'][0]['appid']); // Game B has most playtime
        $this->assertEquals(1, $result['top_games'][1]['appid']); // Game A is second

        // Check all games are included
        $this->assertCount(3, $result['all_games']);
        $this->assertEquals(1, $result['all_games'][0]['id']);
        $this->assertEquals('Game A', $result['all_games'][0]['name']);
        $this->assertEquals(2, $result['all_games'][1]['id']);
        $this->assertEquals('Game B', $result['all_games'][1]['name']);
    }

    /**
     * Test getAllGamesWithNames with various edge cases
     * @return void
     */
    public function testGetAllGamesWithNames(): void
    {
        $games = [
            ['appid' => 123, 'name' => 'Game One', 'playtime_forever' => 120],
            ['appid' => 456, 'name' => 'Game Two'],
            ['appid' => 789], // Missing name
            ['name' => 'Game Four'], // Missing appid
            [], // Empty game object
        ];

        $result = $this->calculator->getAllGamesWithNames($games);

        // Should return all games, even with missing data
        $this->assertCount(5, $result);

        // Normal case with both appid and name
        $this->assertEquals(123, $result[0]['id']);
        $this->assertEquals('Game One', $result[0]['name']);
        $this->assertEquals('https://steamcdn-a.akamaihd.net/steam/apps/123/capsule_616x353.jpg', $result[0]['cover_url']);
        $this->assertEquals(120, $result[0]['playtime']);

        // Missing playtime defaults to 0
        $this->assertEquals(0, $result[1]['playtime']);

        // Missing name returns null for name
        $this->assertEquals(789, $result[2]['id']);
        $this->assertNull($result[2]['name']);

        // Missing appid returns null for id
        $this->assertNull($result[3]['id']);
        $this->assertEquals('Game Four', $result[3]['name']);

        // Empty array
        $emptyResult = $this->calculator->getAllGamesWithNames([]);
        $this->assertEquals([], $emptyResult);
    }
}