<?php

namespace Tests\Unit;

use App\Services\Game\ValidGameService;
use PHPUnit\Framework\TestCase;

class ValidGameServiceTest extends TestCase
{
    /**
     * Tests that games with zero playtime are excluded
     * @return void
     */
    public function testGamesWithZeroPlaytimeAreExcluded(): void
    {
        $service = new class extends ValidGameService {
            protected function getAllGamesFromSession(): array
            {
                return [
                    ['id' => '1', 'name' => 'Game One', 'playtime' => 0],
                    ['id' => '2', 'name' => 'Game Two', 'playtime' => 100],
                    ['id' => '3', 'name' => 'Game Three', 'playtime' => 0],
                ];
            }
        };

        $result = $service->getValidGamesFromSession();

        $this->assertCount(1, $result);
        $this->assertSame('2', $result[0]['id']);
        $this->assertSame('Game Two', $result[0]['name']);
    }

    /**
     * Tests that games with playtime > 0 are included
     * @return void
     */
    public function testGamesWithPlaytimeGreaterThanZeroAreIncluded(): void
    {
        $service = new class extends ValidGameService {
            protected function getAllGamesFromSession(): array
            {
                return [
                    ['id' => '1', 'name' => 'Game One', 'playtime' => 1],
                    ['id' => '2', 'name' => 'Game Two', 'playtime' => 50],
                    ['id' => '3', 'name' => 'Game Three', 'playtime' => 1000],
                ];
            }
        };

        $result = $service->getValidGamesFromSession();

        $this->assertCount(3, $result);
        $this->assertSame('1', $result[0]['id']);
        $this->assertSame('2', $result[1]['id']);
        $this->assertSame('3', $result[2]['id']);
    }

    /**
     * Tests that games without playtime field are treated as zero and excluded
     * @return void
     */
    public function testGamesWithoutPlaytimeFieldAreExcluded(): void
    {
        $service = new class extends ValidGameService {
            protected function getAllGamesFromSession(): array
            {
                return [
                    ['id' => '1', 'name' => 'Game One'], // No playtime field
                    ['id' => '2', 'name' => 'Game Two', 'playtime' => 50],
                ];
            }
        };

        $result = $service->getValidGamesFromSession();

        $this->assertCount(1, $result);
        $this->assertSame('2', $result[0]['id']);
    }

    /**
     * Tests that all games are excluded when all have zero playtime
     * @return void
     */
    public function testAllGamesExcludedWhenAllHaveZeroPlaytime(): void
    {
        $service = new class extends ValidGameService {
            protected function getAllGamesFromSession(): array
            {
                return [
                    ['id' => '1', 'name' => 'Game One', 'playtime' => 0],
                    ['id' => '2', 'name' => 'Game Two', 'playtime' => 0],
                    ['id' => '3', 'name' => 'Game Three', 'playtime' => 0],
                ];
            }
        };

        $result = $service->getValidGamesFromSession();

        $this->assertEmpty($result);
    }

    /**
     * Tests that games must still have valid id and name even with playtime > 0
     * @return void
     */
    public function testGamesWithPlaytimeMustStillHaveValidIdAndName(): void
    {
        $service = new class extends ValidGameService {
            protected function getAllGamesFromSession(): array
            {
                return [
                    ['id' => '', 'name' => 'Game One', 'playtime' => 100], // Empty id
                    ['id' => '2', 'name' => '', 'playtime' => 100], // Empty name
                    ['name' => 'Game Three', 'playtime' => 100], // No id
                    ['id' => '4', 'playtime' => 100], // No name
                    ['id' => '5', 'name' => 'Game Five', 'playtime' => 100], // Valid
                ];
            }
        };

        $result = $service->getValidGamesFromSession();

        $this->assertCount(1, $result);
        $this->assertSame('5', $result[0]['id']);
        $this->assertSame('Game Five', $result[0]['name']);
    }

    /**
     * Tests that empty session returns empty array
     * @return void
     */
    public function testEmptySessionReturnsEmptyArray(): void
    {
        $service = new class extends ValidGameService {
            protected function getAllGamesFromSession(): array
            {
                return [];
            }
        };

        $result = $service->getValidGamesFromSession();

        $this->assertEmpty($result);
    }

    /**
     * Tests that null playtime is treated as zero and excluded
     * @return void
     */
    public function testNullPlaytimeIsExcluded(): void
    {
        $service = new class extends ValidGameService {
            protected function getAllGamesFromSession(): array
            {
                return [
                    ['id' => '1', 'name' => 'Game One', 'playtime' => null],
                    ['id' => '2', 'name' => 'Game Two', 'playtime' => 50],
                ];
            }
        };

        $result = $service->getValidGamesFromSession();

        $this->assertCount(1, $result);
        $this->assertSame('2', $result[0]['id']);
    }

    /**
     * Tests that array indices are properly reset after filtering
     * @return void
     */
    public function testArrayIndicesAreResetAfterFiltering(): void
    {
        $service = new class extends ValidGameService {
            protected function getAllGamesFromSession(): array
            {
                return [
                    ['id' => '1', 'name' => 'Game One', 'playtime' => 0],
                    ['id' => '2', 'name' => 'Game Two', 'playtime' => 100],
                    ['id' => '3', 'name' => 'Game Three', 'playtime' => 0],
                    ['id' => '4', 'name' => 'Game Four', 'playtime' => 200],
                ];
            }
        };

        $result = $service->getValidGamesFromSession();

        $this->assertCount(2, $result);
        $this->assertArrayHasKey(0, $result);
        $this->assertArrayHasKey(1, $result);
        $this->assertArrayNotHasKey(2, $result);
        $this->assertSame('2', $result[0]['id']);
        $this->assertSame('4', $result[1]['id']);
    }
}
