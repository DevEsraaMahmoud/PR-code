<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CommentController;
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
    
    Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::put('/comments/{id}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{id}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::post('/comments/{id}/like', [CommentController::class, 'toggleLike'])->name('comments.like');
    
    // Inline comments
    Route::post('/posts/{id}/inline-comments', [CommentController::class, 'storeInlineComment'])->name('posts.inline-comments.store');
    Route::patch('/inline-comments/{id}', [CommentController::class, 'updateInlineComment'])->name('inline-comments.update');
    
    // Inline comment threads (blocks/snippets)
    Route::get('/posts/{postId}/blocks/{blockId}/threads', [CommentController::class, 'getThreads'])->name('posts.blocks.threads.index');
    Route::post('/posts/{postId}/blocks/{blockId}/threads', [CommentController::class, 'storeThread'])->name('posts.blocks.threads.store');
    Route::patch('/posts/{postId}/blocks/{blockId}/threads/resolve', [CommentController::class, 'resolveThread'])->name('posts.blocks.threads.resolve');
    
    // Like/Follow routes expected by components
    // Route::post('/posts/{id}/like', [PostController::class, 'toggleLike'])->name('posts.like');
    // Route::post('/users/{id}/follow', [UserController::class, 'follow'])->name('users.follow');
    // Route::post('/users/{id}/unfollow', [UserController::class, 'unfollow'])->name('users.unfollow');
});

/*
 * Example controller methods for Inertia pages:
 * 
 * Feed.vue expects:
 * PostController@index() {
 *   return Inertia::render('Feed', [
 *     'posts' => Post::with(['author', 'code'])
 *       ->withCount(['likes', 'comments'])
 *       ->latest()
 *       ->paginate(15)
 *       ->through(fn($post) => [
 *         'id' => $post->id,
 *         'author' => ['id' => $post->author->id, 'name' => $post->author->name, 'avatar_url' => $post->author->avatar_url, 'handle' => $post->author->handle],
 *         'title' => $post->title,
 *         'body' => $post->body_html,
 *         'code' => ['language' => $post->code->language, 'content' => $post->code->content],
 *         'stats' => ['likes' => $post->likes_count, 'comments' => $post->comments_count, 'views' => $post->views],
 *         'created_at' => $post->created_at->toISOString(),
 *       ]),
 *   ]);
 * }
 * 
 * PostShow.vue expects:
 * PostController@show($id) {
 *   $post = Post::with(['author', 'code', 'inlineComments.user', 'comments.user', 'comments.replies.user'])->findOrFail($id);
 *   return Inertia::render('PostShow', [
 *     'post' => [
 *       'id' => $post->id,
 *       'author' => [...],
 *       'title' => $post->title,
 *       'body_html' => $post->body_html,
 *       'code' => ['language' => $post->code->language, 'content' => $post->code->content],
 *       'inline_comments' => $post->inlineComments->map(fn($c) => ['id' => $c->id, 'user' => [...], 'line_number' => $c->line_number, 'text' => $c->text, 'created_at' => $c->created_at->toISOString()]),
 *       'comments' => $post->comments->whereNull('parent_id')->map(fn($c) => ['id' => $c->id, 'user' => [...], 'text' => $c->text, 'created_at' => $c->created_at->toISOString(), 'replies' => [...]]),
 *       'stats' => ['likes' => $post->likes_count, 'comments' => $post->comments_count, 'views' => $post->views],
 *       'is_liked' => auth()->user()->hasLiked($post),
 *       'created_at' => $post->created_at->toISOString(),
 *     ],
 *     'currentUserId' => auth()->id(),
 *   ]);
 * }
 * 
 * CreatePost.vue expects:
 * PostController@create() {
 *   return Inertia::render('CreatePost');
 * }
 * 
 * PostController@store(Request $request) {
 *   $validated = $request->validate(['title' => 'required', 'body' => 'required', 'code_language' => 'nullable', 'code_content' => 'nullable']);
 *   $post = Post::create([...]);
 *   if ($validated['code_content']) {
 *     $post->code()->create(['language' => $validated['code_language'], 'content' => $validated['code_content']]);
 *   }
 *   return redirect()->route('posts.show', $post);
 * }
 */
