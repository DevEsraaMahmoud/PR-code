<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    /**
     * Get all tags.
     */
    public function index(Request $request): JsonResponse
    {
        $tags = Tag::withCount('posts')
            ->orderBy('posts_count', 'desc')
            ->get();

        return response()->json([
            'data' => $tags,
        ]);
    }

    /**
     * Get posts by tag slug.
     */
    public function posts(Request $request, string $slug): JsonResponse
    {
        $tag = Tag::where('slug', $slug)->first();

        if (!$tag) {
            return response()->json([
                'message' => 'Tag not found',
            ], 404);
        }

        $perPage = (int) ($request->query('per_page', 15));
        $posts = $tag->posts()
            ->where('visibility', 'public')
            ->with(['user', 'snippets', 'tags'])
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'data' => $posts->items(),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
                'tag' => $tag,
            ],
        ]);
    }
}
