<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookmarkController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\FollowController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ReactionController;
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
    Route::get('/posts/{post}/comments', [CommentController::class, 'index']);
    Route::post('/posts/{post}/comments', [CommentController::class, 'store']);
    Route::put('/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
    Route::post('/comments/{comment}/resolve', [CommentController::class, 'resolve']);
    
    // Reactions
    Route::post('/{type}/{id}/reactions', [ReactionController::class, 'toggle'])->where('type', 'post|comment');
    Route::get('/{type}/{id}/reactions', [ReactionController::class, 'index'])->where('type', 'post|comment');
    
    // Follows
    Route::post('/users/{user}/follow', [FollowController::class, 'follow']);
    Route::delete('/users/{user}/follow', [FollowController::class, 'unfollow']);
    Route::get('/users/{user}/follow-status', [FollowController::class, 'check']);
    
    // Bookmarks
    Route::get('/bookmarks', [BookmarkController::class, 'index']);
    Route::post('/posts/{post}/bookmark', [BookmarkController::class, 'store']);
    Route::delete('/posts/{post}/bookmark', [BookmarkController::class, 'destroy']);
    
    // Note: Inline comments moved to web routes - use /posts/{id}/inline-comments instead
    
    // Broadcasting auth
    Broadcast::routes(['middleware' => ['auth:sanctum']]);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/mark-read', [NotificationController::class, 'markRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead']);
    
    // Profile
    Route::get('/profile', [\App\Http\Controllers\Api\ProfileController::class, 'show'])->middleware('auth:sanctum');
    Route::put('/profile', [\App\Http\Controllers\Api\ProfileController::class, 'update'])->middleware('auth:sanctum');
    Route::get('/users/{user}/profile', [\App\Http\Controllers\Api\ProfileController::class, 'show']);
    Route::get('/users/{user}/posts', [\App\Http\Controllers\Api\ProfileController::class, 'posts']);
    Route::get('/users/{user}/bookmarks', [\App\Http\Controllers\Api\ProfileController::class, 'bookmarks'])->middleware('auth:sanctum');
});
