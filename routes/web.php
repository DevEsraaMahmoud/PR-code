<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\PostController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    Route::resource('posts', PostController::class)->except(['index']);
    Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
    
    Route::post('/comments', [\App\Http\Controllers\Api\CommentController::class, 'store'])->name('comments.store');
    Route::put('/comments/{id}', [\App\Http\Controllers\Api\CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{id}', [\App\Http\Controllers\Api\CommentController::class, 'destroy'])->name('comments.destroy');
    Route::post('/comments/{id}/like', [\App\Http\Controllers\Api\CommentController::class, 'toggleLike'])->name('comments.like');
});
