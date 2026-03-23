# Backend best practices

## 1. File naming conventions

### Controllers
- Use **PascalCase** for controller names
- Always suffix with `Controller`
- Use singular nouns for resource controllers
- Examples: `UserController`, `GameController`, `LeaderboardController`

### Models
- Use **PascalCase** for model names
- Use singular nouns
- Examples: `User`, `Game`, `Score`, `Achievement`

### Routes
- Use **kebab-case** for URL segments
- Use plural nouns for resource routes
- Examples: `/games`, `/user-profiles`, `/game-sessions`

## 2. MVC architecture

### Models
- Keep models focused on data representation and business logic
- Use Eloquent relationships to define model associations
- Implement validation rules within models when appropriate
- Use accessors and mutators for data formatting

```php
class Game extends Model
{
    protected $fillable = [
        'title', 
        'description'
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
        'is_active' => 'boolean'
    ];
    
    public function users()
    {
        return $this->belongsToMany(User::class, 'game_sessions');
    }
}
```

### Controllers
- Keep controllers thin - business logic is done in services or models
- Follow RESTful conventions for resource controllers
- Use dependency injection for services and repositories
- Return appropriate HTTP status codes

```php
class GameController extends Controller
{
    public function __construct(
        private GameService $gameService
    ) {}
    
    public function index()
    {
        $games = $this->gameService->getActiveGames();
        // Pass back to the front end
    }
}
```

## 3. Laravel best practices

### Validation
- Use Form Request classes for complex validation rules
- Keep validation logic separate from controllers
- Create custom validation rules for business-specific logic

```php
class StoreGameRequest extends FormRequest
{
    public function rules()
    {
        return [
            'title' => 'required|string|max:255|unique:games',
            'description' => 'nullable|string|max:1000'
        ];
    }
}
```

### Service classes
- Create service classes for complex business logic
- Keep services focused on a single responsibility
- Use dependency injection for service dependencies

```php
class GameService
{
    public function __construct(
        private GameRepository $gameRepository,
        private ScoreCalculator $scoreCalculator
    ) {}
    
    public function createGame(array $data): Game
    {
        // Business logic here
        return $this->gameRepository->create($data);
    }
}
```

## 4. Code organization

### Directory structure
- Follow Laravel's conventional directory structure
- Create additional directories for custom classes (Services for example)
- Group related functionality together

```
app/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   └── Requests/
├── Models/
├── Services/
└── Providers/
```

### Namespacing
- Use proper PSR-4 autoloading standards
- Organize classes in logical namespaces
- Import classes at the top of files

```php
<?php

namespace App\Services;

use App\Models\Game;
use App\Repositories\GameRepository;
use Illuminate\Support\Collection;

class GameService
{
    // Class implementation
}
```

## 5. Error Handling

### Exception Handling
- Use Laravel's built-in exception handling
- Create custom exception classes for domain-specific errors
- Always log exceptions with appropriate context

```php
try {
    $game = $this->gameService->createGame($request->validated());
} catch (GameCreationException $e) {
    Log::error('Game creation failed', [
        'user_id' => auth()->id(),
        'data' => $request->validated(),
        'error' => $e->getMessage()
    ]);
    
    return back()->withErrors(['error' => 'Failed to create game']);
}
```

### Validation errors
- Return meaningful error messages
- Use Laravel's validation message system
- Provide user-friendly error feedback

## 6. Security best practices

### Data protection
- Use Laravel's CSRF protection for forms
- Validate and sanitize all user inputs
- Use mass assignment protection with `$fillable` or `$guarded`

### API security
- Validate API requests thoroughly

## 7. Testing

We use PHPUnit for automated testing to ensure code quality and prevent regressions. Tests are located in the `tests/` directory and follow Laravel's testing conventions.

### Test Structure
- `tests/Unit/`: Unit tests for individual classes and services.
  - `GameStatsCalculatorTest`: Tests the `GameStatsCalculator` service for computing game statistics (e.g., playtime calculations, top games).
  - `SteamIdentityServiceTest`: Tests the `SteamIdentityService` for input sanitization, vanity URL resolution, and persona state mapping.
  - `SteamStatsServiceTest`: Tests the `SteamStatsService` for fetching and processing Steam user statistics.
  - `ValidationResponseTest`: Tests the validation response logic for API input and output correctness.
- `tests/Feature/`: Feature tests (for end-to-end functionality, if added).

### Running Tests
- Run all tests: `php artisan test`
- Run unit tests only: `php artisan test --testsuite=Unit`
- Run a specific test file: `php artisan test tests/Unit/GameStatsCalculatorTest.php`
- With verbose output: `php artisan test --verbose`
- Generate coverage report: `php artisan test --coverage`

### Best Practices for Tests
- Use descriptive test method names (e.g., `testSanitizeInputWithVanityUrl`).
- Mock external dependencies (e.g., API clients) to keep tests fast and isolated.
- Aim for high coverage, but focus on critical logic.
- Run tests before committing changes to catch issues early.

### Example Test
```php
class SteamIdentityServiceTest extends TestCase
{
    public function testSanitizeInputWithVanityUrl(): void
    {
        $clientMock = $this->createMock(SteamAPIClient::class);
        $service = new SteamIdentityService($clientMock);

        // Mock vanity resolution
        $clientMock->method('resolveVanityUrl')
            ->with('customname')
            ->willReturn('12345678901234567');

        $result = $service->sanitizeInput('https://steamcommunity.com/id/customname/', true);
        $this->assertSame('12345678901234567', $result);
    }
}
```

## 8. Documentation

### Code documentation
- Write clear and concise docblocks for methods
- Document complex business logic
- Keep documentation up-to-date with code changes

### API documentation
- Document all API endpoints with proper examples
- Include request/response schemas
- Specify authentication requirements

## 9. Environment configuration

### Configuration management
- Use environment variables for configuration
- Never commit sensitive data to version control
- Use different configurations for different environments

---