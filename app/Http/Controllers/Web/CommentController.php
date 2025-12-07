<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Services\CommentService;
use App\Repositories\CommentRepository;
use App\Repositories\SnippetRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class CommentController extends Controller
{
    public function __construct(
        private CommentService $commentService,
        private CommentRepository $commentRepository,
        private SnippetRepository $snippetRepository
    ) {
    }

    /**
     * Store a newly created comment (inline or regular).
     */
    public function store(StoreCommentRequest $request)
    {
        try {
            $result = $this->commentService->createComment($request->validated(), $request->user()->id);
            
            $postId = $result['comment']->post_id;
            
            return redirect()->back()->with('message', 'Comment created successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create comment: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreCommentRequest $request, string $id)
    {
        try {
            $result = $this->commentService->updateComment((int) $id, $request->validated(), $request->user()->id);

            return redirect()->back()->with('message', 'Comment updated successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update comment: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $this->commentService->deleteComment((int) $id, $request->user()->id);

            return redirect()->back()->with('message', 'Comment deleted successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete comment: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle like on a comment.
     */
    public function toggleLike(Request $request, string $id)
    {
        try {
            $result = $this->commentService->toggleLike((int) $id, $request->user()->id);

            return redirect()->back();
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to toggle like: ' . $e->getMessage()]);
        }
    }

    /**
     * Store a new inline comment for a post.
     */
    public function storeInlineComment(Request $request, string $id)
    {
        try {
            // Merge route parameter into request data
            $request->merge([
                'post_id' => (int) $id,
                'is_inline' => true,
            ]);
            
            // Validate the request
            $validated = $request->validate([
                'post_id' => ['required', 'integer', 'exists:posts,id'],
                'snippet_id' => ['nullable', 'integer', 'exists:snippets,id'],
                'start_line' => ['required', 'integer', 'min:1'],
                'end_line' => ['required', 'integer', 'min:1'],
                'body' => ['required', 'string', 'max:5000'],
                'is_inline' => ['required', 'boolean'],
            ]);
            
            // Additional validation
            if ($validated['start_line'] > $validated['end_line']) {
                return back()->withErrors(['start_line' => 'Start line must be less than or equal to end line.']);
            }
            
            $result = $this->commentService->createComment($validated, $request->user()->id);

            return redirect()->back()->with('message', 'Inline comment created successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create inline comment: ' . $e->getMessage()]);
        }
    }

    /**
     * Update an inline comment.
     */
    public function updateInlineComment(StoreCommentRequest $request, string $id)
    {
        try {
            $result = $this->commentService->updateComment((int) $id, $request->validated(), $request->user()->id);

            return redirect()->back()->with('message', 'Inline comment updated successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update inline comment: ' . $e->getMessage()]);
        }
    }

    /**
     * Get threads for a specific block/snippet and line.
     */
    public function getThreads(Request $request, string $postId, string $blockId): JsonResponse
    {
        try {
            $line = $request->query('line');
            
            if (!$line) {
                return response()->json([
                    'messages' => [],
                    'resolved' => false,
                ]);
            }

            // Map blockId to snippet_id
            $snippetId = $this->resolveSnippetId($blockId, $postId);
            
            if (!$snippetId) {
                return response()->json([
                    'messages' => [],
                    'resolved' => false,
                ]);
            }

            // Get all comments for this snippet
            $allComments = $this->commentRepository->getAllBySnippet($snippetId);
            
            \Log::debug("Loading threads for snippet {$snippetId}, line {$line}", [
                'snippet_id' => $snippetId,
                'line' => $line,
                'all_comments_count' => $allComments->count(),
                'comments' => $allComments->map(function ($comment) {
                    return [
                        'id' => $comment->id,
                        'snippet_id' => $comment->snippet_id,
                        'is_inline' => $comment->is_inline,
                        'start_line' => $comment->start_line,
                        'end_line' => $comment->end_line,
                        'parent_id' => $comment->parent_id,
                    ];
                })->toArray(),
            ]);
            
            // Filter inline comments that match the line number
            $comments = $allComments->filter(function ($comment) use ($line) {
                if (!$comment->is_inline) {
                    return false;
                }
                
                // Check if comment is on this line
                if ($comment->start_line <= $line && $comment->end_line >= $line) {
                    return true;
                }
                
                // Check if it's a reply to a comment on this line
                if ($comment->parent_id) {
                    $parent = $allComments->firstWhere('id', $comment->parent_id);
                    if ($parent && $parent->start_line <= $line && $parent->end_line >= $line) {
                        return true;
                    }
                }
                
                return false;
            });
            
            // Load relationships
            $comments->load(['user', 'replies.user', 'parent']);

            // Group by thread (parent comments and their replies)
            // Flatten all messages into a single array (parent comments + replies)
            $messages = [];
            
            // First, get parent comments (thread starters)
            $parentComments = $comments->whereNull('parent_id');
            
            foreach ($parentComments as $parent) {
                // Add parent comment
                $messages[] = [
                    'id' => $parent->id,
                    'user' => [
                        'id' => $parent->user->id,
                        'name' => $parent->user->name,
                        'avatar_url' => $parent->user->avatar_url ?? null,
                    ],
                    'text' => $parent->body,
                    'body' => $parent->body,
                    'created_at' => $parent->created_at->toISOString(),
                    'edited_at' => $parent->edited_at?->toISOString(),
                    'line_number' => $parent->start_line,
                ];
                
                // Add replies
                foreach ($parent->replies as $reply) {
                    $messages[] = [
                        'id' => $reply->id,
                        'user' => [
                            'id' => $reply->user->id,
                            'name' => $reply->user->name,
                            'avatar_url' => $reply->user->avatar_url ?? null,
                        ],
                        'text' => $reply->body,
                        'body' => $reply->body,
                        'created_at' => $reply->created_at->toISOString(),
                        'edited_at' => $reply->edited_at?->toISOString(),
                        'line_number' => $reply->start_line ?? $parent->start_line,
                        'parent_id' => $parent->id,
                    ];
                }
            }
            
            // Sort by created_at
            usort($messages, function ($a, $b) {
                return strtotime($a['created_at']) - strtotime($b['created_at']);
            });

            // Check if any thread is resolved (for now, we'll use a simple check)
            // You can add a resolved column to comments table later if needed
            $resolved = false;

            return response()->json([
                'messages' => $messages,
                'resolved' => $resolved,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch threads: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a new thread comment (create or reply).
     */
    public function storeThread(Request $request, string $postId, string $blockId): JsonResponse|RedirectResponse
    {
        try {
            $validated = $request->validate([
                'line' => ['required', 'integer', 'min:1'],
                'content' => ['required', 'string', 'max:5000'],
                'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
            ]);

            // Map blockId to snippet_id
            $snippetId = $this->resolveSnippetId($blockId, $postId);
            
            if (!$snippetId) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Block or snippet not found'], 404);
                }
                return back()->withErrors(['error' => 'Block or snippet not found']);
            }

            // Prepare comment data
            $commentData = [
                'post_id' => (int) $postId,
                'snippet_id' => $snippetId,
                'is_inline' => true,
                'start_line' => $validated['line'],
                'end_line' => $validated['line'],
                'body' => $validated['content'],
                'parent_id' => $validated['parent_id'] ?? null,
            ];

            $result = $this->commentService->createComment($commentData, $request->user()->id);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Comment added successfully',
                    'comment' => [
                        'id' => $result['comment']->id,
                        'user' => [
                            'id' => $result['comment']->user->id,
                            'name' => $result['comment']->user->name,
                            'avatar_url' => $result['comment']->user->avatar_url ?? null,
                        ],
                        'text' => $result['comment']->body,
                        'body' => $result['comment']->body,
                        'created_at' => $result['comment']->created_at->toISOString(),
                    ],
                ], 201);
            }

            return redirect()->back()->with('message', 'Comment added successfully');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Failed to create comment: ' . $e->getMessage(),
                ], 422);
            }
            return back()->withErrors(['error' => 'Failed to create comment: ' . $e->getMessage()]);
        }
    }

    /**
     * Resolve or unresolve a thread.
     */
    public function resolveThread(Request $request, string $postId, string $blockId): JsonResponse|RedirectResponse
    {
        try {
            $validated = $request->validate([
                'line' => ['required', 'integer', 'min:1'],
                'resolved' => ['required', 'boolean'],
            ]);

            // Map blockId to snippet_id
            $snippetId = $this->resolveSnippetId($blockId, $postId);
            
            if (!$snippetId) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Block or snippet not found'], 404);
                }
                return back()->withErrors(['error' => 'Block or snippet not found']);
            }

            // For now, we'll just return success
            // You can add a resolved column to comments table later if needed
            // Or create a separate thread_resolutions table

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Thread resolution updated',
                    'resolved' => $validated['resolved'],
                ]);
            }

            return redirect()->back()->with('message', 'Thread resolution updated');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Failed to update thread resolution: ' . $e->getMessage(),
                ], 422);
            }
            return back()->withErrors(['error' => 'Failed to update thread resolution: ' . $e->getMessage()]);
        }
    }

    /**
     * Resolve blockId to snippet_id.
     * blockId can be:
     * - A numeric snippet ID
     * - A string like "code-1" (we'll try to find matching snippet)
     */
    private function resolveSnippetId(string $blockId, string $postId): ?int
    {
        \Log::debug("resolveSnippetId called", [
            'blockId' => $blockId,
            'postId' => $postId,
        ]);
        
        // If it's numeric, treat it as snippet ID
        if (is_numeric($blockId)) {
            $snippet = $this->snippetRepository->find((int) $blockId);
            if ($snippet && $snippet->post_id == $postId) {
                \Log::debug("Found snippet by ID", ['snippet_id' => $snippet->id]);
                return $snippet->id;
            }
            \Log::debug("Snippet not found or post_id mismatch", [
                'snippet' => $snippet ? ['id' => $snippet->id, 'post_id' => $snippet->post_id] : null,
                'postId' => $postId,
            ]);
        }

        // If it starts with "code-", try to extract index or find by block_index
        if (str_starts_with($blockId, 'code-')) {
            $parts = explode('-', $blockId);
            if (count($parts) >= 2 && is_numeric($parts[1])) {
                // Try to find snippet by block_index
                $snippets = $this->snippetRepository->findByPost((int) $postId);
                $index = (int) $parts[1] - 1; // Convert to 0-based index
                \Log::debug("Looking for snippet by block_index", [
                    'block_index' => $index,
                    'available_snippets' => $snippets->map(fn($s) => ['id' => $s->id, 'block_index' => $s->block_index])->toArray(),
                ]);
                if (isset($snippets[$index])) {
                    \Log::debug("Found snippet by block_index", ['snippet_id' => $snippets[$index]->id]);
                    return $snippets[$index]->id;
                }
            }
        }

        // Try to find first snippet for this post
        $snippets = $this->snippetRepository->findByPost((int) $postId);
        if ($snippets->isNotEmpty()) {
            \Log::debug("Using first snippet as fallback", ['snippet_id' => $snippets->first()->id]);
            return $snippets->first()->id;
        }

        \Log::debug("No snippet found");
        return null;
    }
}

