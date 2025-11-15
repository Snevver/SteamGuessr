<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\SteamAPIController;

Route::get('/', function () {
    return Inertia::render('Welcome');
});

// Route to get basic user info
Route::post('/get-basic-info', [
    SteamAPIController::class,
    'getBasicInfo'
]);