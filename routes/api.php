<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\TagController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public post routes
Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/trending', [PostController::class, 'trending']);
Route::get('/posts/{id}', [PostController::class, 'show']);

// Public search
Route::get('/search', [SearchController::class, 'search']);

// Public tags
Route::get('/tags', [TagController::class, 'index']);
Route::get('/tags/{slug}/posts', [TagController::class, 'posts']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'user']);

    // Posts (create, update, delete)
    Route::post('/posts', [PostController::class, 'store']);
    Route::put('/posts/{id}', [PostController::class, 'update']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);
    Route::post('/posts/{id}/like', [PostController::class, 'toggleLike']);

    // Comments
    Route::get('/comments', [CommentController::class, 'index']);
    Route::post('/comments', [CommentController::class, 'store']);
    Route::put('/comments/{id}', [CommentController::class, 'update']);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);
    Route::post('/comments/{id}/like', [CommentController::class, 'toggleLike']);
    
    // Broadcasting auth
    Broadcast::routes(['middleware' => ['auth:sanctum']]);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/mark-read', [NotificationController::class, 'markRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead']);
});
