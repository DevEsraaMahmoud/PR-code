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
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $snippetId = $request->query('snippet_id');

        if (!$snippetId) {
            return response()->json([
                'message' => 'snippet_id is required',
            ], 422);
        }

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

    /**
     * Store a newly created resource in storage.
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
        // Not needed for MVP, but keeping for API consistency
        return response()->json([
            'message' => 'Use index with snippet_id parameter',
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
}
