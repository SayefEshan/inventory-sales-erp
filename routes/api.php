<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// API Version 1
Route::prefix('v1')->group(function () {

    Route::middleware('throttle:10,1')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });

    // Protected routes (authentication required) - with rate limiting
    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {

        // Auth endpoints
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);

        // Report endpoints
        Route::prefix('reports')->group(function () {
            Route::get('/top-products', [ReportController::class, 'topProducts']);
            Route::get('/sales-summary', [ReportController::class, 'salesSummary']);
        });
    });
});

// Fallback for undefined routes
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Endpoint not found. Please check the API documentation.'
    ], 404);
});
