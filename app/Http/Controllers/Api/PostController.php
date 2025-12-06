<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Resources\PostResource;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct(
        private PostService $postService
    ) {
    }

    /**
     * Display a listing of the resource with pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) ($request->query('per_page', 15));
        $query = $request->query('q');
        $language = $request->query('language');
        $tags = $request->query('tags') ? explode(',', $request->query('tags')) : null;
        $visibility = $request->query('visibility', 'public');

        if ($query || $language || $tags) {
            $result = $this->postService->searchPosts($query ?? '', $language, $tags, $perPage);
            
            if ($result instanceof \Illuminate\Pagination\LengthAwarePaginator) {
                return PostResource::collection($result)->response();
            }
            
            return response()->json([
                'data' => PostResource::collection($result['posts']),
            ]);
        }

        $posts = $this->postService->getAllPosts($perPage, $visibility);

        return PostResource::collection($posts)->response();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request): JsonResponse
    {
        try {
            $result = $this->postService->createPost($request->validated(), $request->user()->id);

            return response()->json([
                'data' => new PostResource($result['post']),
                'message' => 'Post created successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create post',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display the specified resource (by ID or slug).
     */
    public function show(string $id): JsonResponse
    {
        $result = $this->postService->getPost($id);

        if (!$result) {
            return response()->json([
                'message' => 'Post not found',
            ], 404);
        }

        return response()->json([
            'data' => new PostResource($result['post']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StorePostRequest $request, string $id): JsonResponse
    {
        try {
            $result = $this->postService->updatePost((int) $id, $request->validated(), $request->user()->id);

            return response()->json([
                'data' => new PostResource($result['post']),
                'message' => 'Post updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update post',
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
            $this->postService->deletePost((int) $id, $request->user()->id);

            return response()->json([
                'message' => 'Post deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete post',
                'error' => $e->getMessage(),
            ], 403);
        }
    }

    /**
     * Get trending posts.
     */
    public function trending(): JsonResponse
    {
        $result = $this->postService->getTrendingPosts();

        return response()->json([
            'data' => PostResource::collection($result['posts']),
        ]);
    }

    /**
     * Toggle like on a post.
     */
    public function toggleLike(Request $request, string $id): JsonResponse
    {
        try {
            $result = $this->postService->toggleLike((int) $id, $request->user()->id);

            return response()->json([
                'data' => $result,
                'message' => $result['liked'] ? 'Post liked' : 'Post unliked',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to toggle like',
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
