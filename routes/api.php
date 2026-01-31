<?php

use App\Http\Controllers\API\V1\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:api')->group(function () {
    // Public routes with stricter throttling
    Route::middleware('throttle:auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        
        Route::post('/pdf/upload', [\App\Http\Controllers\API\V1\FileHandler\FileHandlerContoller::class, 'upload']);
        Route::post('/chat/query', [\App\Http\Controllers\API\V1\Chat\ChatController::class, 'query']);
        Route::post('/chat/query-sync', [\App\Http\Controllers\API\V1\Chat\ChatController::class, 'querySync']);
        
        Route::post('/debug/search', [\App\Http\Controllers\API\V1\Debug\DebugController::class, 'testSearch']);

        Route::get('/user', function (Request $request) {
            return $request->user();
        });
    });
});
