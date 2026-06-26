<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BlueprintController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\RawContentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Protected Routes (require Bearer token)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function (): void {

    Route::post('/logout', [AuthController::class, 'logout']);

    // Blueprints (Sprint 3)
    Route::apiResource('blueprints', BlueprintController::class);

    // Content & Posts (Sprint 4)
    Route::post('content/repurpose', [RawContentController::class, 'store']);
    Route::get('posts', [PostController::class, 'index']);
    Route::get('posts/{post}', [PostController::class, 'show']);
    Route::patch('posts/{post}', [PostController::class, 'update']);

    // Ghostwriter Agent Chat (Sprint 5)
    // Route::post('posts/{post}/chat', [ChatController::class, 'ask']);
});
