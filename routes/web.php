<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\SteamAPIController;
use App\Http\Controllers\ClassicGamemodeController;

Route::get('/', function () {
    return Inertia::render('Landing');
});

Route::get('/admin', function () {
    return Inertia::render('Admin');
});

Route::redirect('/login', '/admin');
Route::redirect('/profile', '/admin');
Route::redirect('/settings', '/admin');
Route::redirect('/account', '/admin');

// Group protected routes that require a SteamID in the session
Route::middleware('steam.auth')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    });

    // Group routes under /minigame/
    Route::prefix('minigame')->group(function () {
        Route::get('/classic', function () {
            return Inertia::render('Minigames/Classic');
        });
    });

    Route::get('/api/classic', [ClassicGamemodeController::class, 'index']);
});

Route::post('/initiate-user', [SteamAPIController::class, 'validateUser']);