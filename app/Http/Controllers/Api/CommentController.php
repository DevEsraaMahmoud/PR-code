<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Snippet;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Notification;

class CommentController extends Controller
{
    /**
     * Store a new comment
     */
    public function store(Request $request, Post $post): JsonResponse
    {
        $validated = $request->validate([
            'body' => 'required|string|max:5000',
            'snippet_id' => 'nullable|exists:snippets,id',
            'parent_id' => 'nullable|exists:comments,id',
            'start_line' => 'nullable|integer|min:1',
            'end_line' => 'nullable|integer|min:1|gte:start_line',
            'is_inline' => 'boolean',
        ]);

        $user = $request->user();

        $comment = Comment::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'snippet_id' => $validated['snippet_id'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'body' => $validated['body'],
            'start_line' => $validated['start_line'] ?? null,
            'end_line' => $validated['end_line'] ?? null,
            'is_inline' => $validated['is_inline'] ?? false,
        ]);

        $comment->load(['user:id,name,avatar_url', 'replies']);

        // TODO: Send notification to post author and mentioned users
        // Notification::send(...);

        return response()->json([
            'message' => 'Comment created successfully',
            'comment' => $comment,
        ], 201);
    }

    /**
     * Update a comment
     */
    public function update(Request $request, Comment $comment): JsonResponse
    {
        $this->authorize('update', $comment);

        $validated = $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $comment->update(['body' => $validated['body']]);
        $comment->load(['user:id,name,avatar_url', 'replies']);

        return response()->json([
            'message' => 'Comment updated successfully',
            'comment' => $comment,
        ]);
    }

    /**
     * Delete a comment
     */
    public function destroy(Comment $comment): JsonResponse
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return response()->json([
            'message' => 'Comment deleted successfully',
        ]);
    }

    /**
     * Resolve/unresolve a comment
     */
    public function resolve(Request $request, Comment $comment): JsonResponse
    {
        $user = $request->user();

        // Only post author or comment author can resolve
        if ($comment->post->user_id !== $user->id && $comment->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($comment->resolved) {
            $comment->unresolve();
            $action = 'unresolved';
        } else {
            $comment->resolve($user);
            $action = 'resolved';
        }

        return response()->json([
            'message' => "Comment {$action}",
            'comment' => $comment->fresh(),
        ]);
    }

    /**
     * Get comments for a post
     */
    public function index(Request $request, Post $post): JsonResponse
    {
        $snippetId = $request->query('snippet_id');
        $inlineOnly = $request->boolean('inline_only');

        $query = Comment::where('post_id', $post->id)
            ->whereNull('parent_id')
            ->with(['user:id,name,avatar_url', 'replies.user:id,name,avatar_url']);

        if ($snippetId) {
            $query->where('snippet_id', $snippetId);
        }

        if ($inlineOnly) {
            $query->where('is_inline', true);
        }

        $comments = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($comments);
    }
}
