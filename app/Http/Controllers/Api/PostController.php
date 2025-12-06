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
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->query('q');
        $language = $request->query('language');

        if ($query || $language) {
            $result = $this->postService->searchPosts($query ?? '', $language);
        } else {
            $result = $this->postService->getAllPosts();
        }

        return response()->json([
            'data' => PostResource::collection($result['posts']),
        ]);
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
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $result = $this->postService->getPost((int) $id);

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
}
