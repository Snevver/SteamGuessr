<?php

namespace Tests\Unit;

use App\Services\Steam\SteamAPIClient;
use App\Services\Steam\SteamIdentityService;
use PHPUnit\Framework\TestCase;

class SteamIdentityServiceTest extends TestCase
{
    private SteamAPIClient $clientMock;
    private SteamIdentityService $service;

    /**
     * Runs before each test to create fresh objects
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a fake Steam API client
        $this->clientMock = $this->createMock(SteamAPIClient::class);
        
        // Create the service instance
        $this->service = new SteamIdentityService($this->clientMock);
    }

    /**
     * Tests if status code 0 converts to "Offline"
     * @return void
     */
    public function testPersonaStateOffline(): void
    {
        $result = $this->service->getPersonaStateMeaning(0);
        
        $this->assertSame('Offline', $result);
    }

    /**
     * Tests if status code 1 converts to "Online"
     * @return void
     */
    public function testPersonaStateOnline(): void
    {
        $result = $this->service->getPersonaStateMeaning(1);
        
        $this->assertSame('Online', $result);
    }

    /**
     * Tests if status code 2 converts to "Busy"
     * @return void
     */
    public function testPersonaStateBusy(): void
    {
        $result = $this->service->getPersonaStateMeaning(2);
        
        $this->assertSame('Busy', $result);
    }

    /**
     * Tests if status code 3 converts to "Away"
     * @return void
     */
    public function testPersonaStateAway(): void
    {
        $result = $this->service->getPersonaStateMeaning(3);
        
        $this->assertSame('Away', $result);
    }

    /**
     * Tests if status code 4 converts to "Snooze"
     * @return void
     */
    public function testPersonaStateSnooze(): void
    {
        $result = $this->service->getPersonaStateMeaning(4);
        
        $this->assertSame('Snooze', $result);
    }

    /**
     * Tests if status code 5 converts to "Looking to trade"
     * @return void
     */
    public function testPersonaStateLookingToTrade(): void
    {
        $result = $this->service->getPersonaStateMeaning(5);
        
        $this->assertSame('Looking to trade', $result);
    }

    /**
     * Tests if status code 6 converts to "Looking to play"
     * @return void
     */
    public function testPersonaStateLookingToPlay(): void
    {
        $result = $this->service->getPersonaStateMeaning(6);
        
        $this->assertSame('Looking to play', $result);
    }

    /**
     * Tests if invalid status codes return "Unknown"
     * @return void
     */
    public function testPersonaStateUnknown(): void
    {
        $result = $this->service->getPersonaStateMeaning(999);
        
        $this->assertSame('Unknown', $result);
    }

    /**
     * Tests if vanity URL (custom name) converts to numeric Steam ID
     * 
     * Example: "https://steamcommunity.com/id/customname/" should become "12345678901234567"
     * This requires calling the Steam API to resolve the custom name
     * @return void
     */
    public function testSanitizeInputWithVanityUrl(): void
    {
        // Tell the fake API client what to return when asked about "customname"
        $this->clientMock
            ->method('resolveVanityUrl')
            ->with('customname')
            ->willReturn('12345678901234567');

        // Test with a vanity URL
        $result = $this->service->sanitizeInput('https://steamcommunity.com/id/customname/', true);
        
        $this->assertSame('12345678901234567', $result);
    }

    /**
     * Tests if numeric Steam ID returns as-is without conversion
     * 
     * When given a pure numeric ID, it should return it without calling the API
     * @return void
     */
    public function testSanitizeInputWithNumericId(): void
    {
        // No API call should be made for numeric IDs
        $result = $this->service->sanitizeInput('76561198000000000', false);
        
        $this->assertSame('76561198000000000', $result);
    }
}