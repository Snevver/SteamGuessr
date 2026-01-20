<?php

namespace Tests\Unit;

use App\Http\Controllers\ClassicGamemodeController;
use App\Services\Hints\HintService;
use App\Services\Game\ValidGameService;
use Illuminate\Http\JsonResponse;
use PHPUnit\Framework\TestCase;

class ClassicGamemodeControllerTest extends TestCase
{
    /**
     * Tests successful hint generation when session contains valid games
     * @return void
     */
    public function testIndexReturnsHintsForValidSession(): void
    {
        $games = [
            ['id' => 123, 'name' => 'Test Game', 'cover_url' => 'https://example.com/cover.jpg'],
        ];

        $allHintsWithData = [
            'first_letter' => ['hint_name' => 'first_letter', 'data' => ['first_letter' => 'T']],
            'total_playtime' => ['hint_name' => 'total_playtime', 'data' => ['playtime' => 100]],
            'reviews' => ['hint_name' => 'reviews', 'data' => ['review_ratio' => '95%', 'total_reviews' => 1000]],
        ];

        $hintServiceMock = $this->createMock(HintService::class);
        $hintServiceMock->method('getAllHintsWithData')->willReturn($allHintsWithData);

        $validGameServiceMock = $this->createMock(ValidGameService::class);
        $validGameServiceMock->method('getValidGamesFromSession')->willReturn($games);

        $controller = new ClassicGamemodeController($hintServiceMock, $validGameServiceMock);

        $response = $controller->index();

        $this->assertInstanceOf(JsonResponse::class, $response);

        $expected = [
            'hints_data' => $allHintsWithData,
            'game' => [
                'id' => 123,
                'name' => 'Test Game',
                'cover_url' => 'https://example.com/cover.jpg',
            ],
        ];

        $this->assertEquals($expected, $response->getData(true));
    }

    /**
     * Tests 404 when session is empty
     * @return void
     */
    public function testIndexReturns404WhenSessionEmpty(): void
    {
        $hintServiceMock = $this->createMock(HintService::class);
        $validGameServiceMock = $this->createMock(ValidGameService::class);
        $validGameServiceMock->method('getValidGamesFromSession')->willReturn([]);

        $controller = new ClassicGamemodeController($hintServiceMock, $validGameServiceMock);

        $response = $controller->index();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(['error' => 'No valid games found in session'], $response->getData(true));
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * Tests 404 when no valid games exist in session
     * @return void
     */
    public function testIndexReturns404WhenNoValidGames(): void
    {
        $hintServiceMock = $this->createMock(HintService::class);
        $validGameServiceMock = $this->createMock(ValidGameService::class);
        $validGameServiceMock->method('getValidGamesFromSession')->willReturn([]);

        $controller = new ClassicGamemodeController($hintServiceMock, $validGameServiceMock);

        $response = $controller->index();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(['error' => 'No valid games found in session'], $response->getData(true));
        $this->assertEquals(404, $response->getStatusCode());
    }
}
