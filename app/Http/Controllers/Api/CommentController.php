<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct(
        private CommentService $commentService
    ) {
    }

    /**
     * Display comments for a post or snippet.
     */
    public function index(Request $request): JsonResponse
    {
        $postId = $request->query('post_id');
        $snippetId = $request->query('snippet_id');
        $perPage = (int) ($request->query('per_page', 20));

        if ($postId) {
            try {
                $result = $this->commentService->getCommentsByPost((int) $postId, $perPage);
                return response()->json([
                    'data' => CommentResource::collection($result['comments']),
                    'meta' => [
                        'current_page' => $result['comments']->currentPage(),
                        'last_page' => $result['comments']->lastPage(),
                        'per_page' => $result['comments']->perPage(),
                        'total' => $result['comments']->total(),
                    ],
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Failed to fetch comments',
                    'error' => $e->getMessage(),
                ], 404);
            }
        }

        if ($snippetId) {
            try {
                $result = $this->commentService->getCommentsBySnippet((int) $snippetId);
                return response()->json([
                    'data' => CommentResource::collection($result['comments']),
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Failed to fetch comments',
                    'error' => $e->getMessage(),
                ], 404);
            }
        }

        return response()->json([
            'message' => 'post_id or snippet_id is required',
        ], 422);
    }

    /**
     * Store a newly created comment (inline or regular).
     */
    public function store(StoreCommentRequest $request): JsonResponse
    {
        try {
            $result = $this->commentService->createComment($request->validated(), $request->user()->id);

            return response()->json([
                'data' => new CommentResource($result['comment']),
                'message' => 'Comment created successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create comment',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        // Not typically needed, but keeping for API consistency
        return response()->json([
            'message' => 'Use index with post_id or snippet_id parameter',
        ], 400);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreCommentRequest $request, string $id): JsonResponse
    {
        try {
            $result = $this->commentService->updateComment((int) $id, $request->validated(), $request->user()->id);

            return response()->json([
                'data' => new CommentResource($result['comment']),
                'message' => 'Comment updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update comment',
                'error' => $e->getMessage(),
            ], 403);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $this->commentService->deleteComment((int) $id, $request->user()->id);

            return response()->json([
                'message' => 'Comment deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete comment',
                'error' => $e->getMessage(),
            ], 403);
        }
    }

    /**
     * Toggle like on a comment.
     */
    public function toggleLike(Request $request, string $id): JsonResponse
    {
        try {
            $result = $this->commentService->toggleLike((int) $id, $request->user()->id);

            return response()->json([
                'data' => $result,
                'message' => $result['liked'] ? 'Comment liked' : 'Comment unliked',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to toggle like',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Store a new inline comment for a post.
     */
    public function storeInlineComment(StoreCommentRequest $request, string $id): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['post_id'] = (int) $id;
            $data['is_inline'] = true;

            $result = $this->commentService->createComment($data, $request->user()->id);

            return response()->json([
                'data' => new CommentResource($result['comment']),
                'message' => 'Inline comment created successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create inline comment',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update an inline comment.
     */
    public function updateInlineComment(StoreCommentRequest $request, string $id): JsonResponse
    {
        try {
            $result = $this->commentService->updateComment((int) $id, $request->validated(), $request->user()->id);

            return response()->json([
                'data' => new CommentResource($result['comment']),
                'message' => 'Inline comment updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update inline comment',
                'error' => $e->getMessage(),
            ], 403);
        }
    }
}
