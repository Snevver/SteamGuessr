<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\Steam\SteamStatsService;
use App\Services\Game\GameStatsCalculator;
use App\Services\Steam\SteamAPIClient;
use Carbon\Carbon;

class SteamStatsServiceTest extends TestCase
{
    private SteamStatsService $service;

    /**
     * Runs before each test to create a fresh SteamStatsService
     */
    public function setUp(): void
    {
        // Create a fake API client (we don't need it for these tests)
        $fakeClient = $this->createMock(SteamAPIClient::class);
        
        // Create the service we're testing
        $this->service = new SteamStatsService($fakeClient, new GameStatsCalculator());
    }

    /**
     * Runs after each test to reset Carbon's time back to normal
     */
    public function tearDown(): void
    {
        Carbon::setTestNow();
    }

    /**
     * Tests if an account age is calculated correctly
     * @return void
     */
    public function testAccountWithPartialAge(): void
    {
        // Pretend today is December 2, 2025 UTC (use UTC so service comparisons match)
        Carbon::setTestNow(Carbon::create(2025, 12, 2, 0, 0, 0, 'UTC'));

        // Account created June 15, 2023 UTC (2 years, 5 months, 17 days ago)
        $timestamp = Carbon::create(2023, 6, 15, 0, 0, 0, 'UTC')->getTimestamp();
        
        // Call the method
        $result = $this->service->getAccountAgeAndCreationDate($timestamp);

        // Assertions
        $this->assertEquals('June 15, 2023', $result['date']);
        $this->assertEquals(2, $result['age']['years']);
        $this->assertEquals(5, $result['age']['months']);
        $this->assertEquals(17, $result['age']['days']);
    }

    /**
     * Test if null timestamp returns null for both date and age (edge case)
     */
    public function testNullTimestamp(): void
    {
        $result = $this->service->getAccountAgeAndCreationDate(null);

        $this->assertNull($result['date']);
        $this->assertNull($result['age']);
    }

    /**
     * Tests if a "0" timestamp returns null for both date and age (edge case)
     */
    public function testZeroTimestamp(): void
    {
        $result = $this->service->getAccountAgeAndCreationDate(0);

        $this->assertNull($result['date']);
        $this->assertNull($result['age']);
    }

    /**
     * Tests if a negative timestamp returns null for both date and age (edge case)
     */
    public function testNegativeTimestamp(): void
    {
        $result = $this->service->getAccountAgeAndCreationDate(-1);

        $this->assertNull($result['date']);
        $this->assertNull($result['age']);
    }
}