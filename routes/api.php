<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\HealthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — /api/v1/
|--------------------------------------------------------------------------
*/

// Public endpoints
Route::prefix('v1')->group(function () {
    // Health check (public)
    Route::get('/health', [HealthController::class, 'check']);

    // Auth
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Authenticated endpoints
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
        Route::put('/auth/password', [AuthController::class, 'changePassword']);
    });
});