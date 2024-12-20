<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\LostItemController;
use App\Http\Controllers\FoundItemController;

// User routes
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Posts routes
Route::prefix('posts')->group(function () {
    Route::get('/', [PostController::class, 'index']);
    Route::post('/', [PostController::class, 'store']);
    Route::put('/{id}', [PostController::class, 'update']);
    Route::delete('/{id}', [PostController::class, 'destroy']);
});

// Lost Items routes
Route::prefix('lost-items')->group(function () {
    Route::get('/', [LostItemController::class, 'index']);
    Route::post('/', [LostItemController::class, 'store']);
    Route::get('/{id}', [LostItemController::class, 'show']);
    Route::put('/{id}', [LostItemController::class, 'update']);
    Route::delete('/{id}', [LostItemController::class, 'destroy']);
});

// Found Items routes
Route::prefix('found-items')->group(function () {
    Route::get('/', [FoundItemController::class, 'index']);
    Route::post('/', [FoundItemController::class, 'store']);
    Route::get('/{id}', [FoundItemController::class, 'show']);
    Route::put('/{id}', [FoundItemController::class, 'update']);
    Route::delete('/{id}', [FoundItemController::class, 'destroy']);
});

// Comments routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/comments', [CommentController::class, 'store']);
    Route::get('/comments/{id}', [CommentController::class, 'show']);
    Route::put('/comments/{id}', [CommentController::class, 'update']);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);

    // Notifications routes
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount']);
});
