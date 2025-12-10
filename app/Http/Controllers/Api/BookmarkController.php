<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bookmark;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BookmarkController extends Controller
{
    /**
     * Bookmark a post
     */
    public function store(Request $request, Post $post): JsonResponse
    {
        $user = $request->user();

        $bookmark = Bookmark::firstOrCreate([
            'user_id' => $user->id,
            'bookmarkable_type' => Post::class,
            'bookmarkable_id' => $post->id,
        ], [
            'folder_name' => $request->folder_name,
        ]);

        return response()->json([
            'message' => 'Bookmarked successfully',
            'bookmark' => $bookmark,
        ], 201);
    }

    /**
     * Remove bookmark
     */
    public function destroy(Request $request, Post $post): JsonResponse
    {
        $user = $request->user();

        Bookmark::where('user_id', $user->id)
            ->where('bookmarkable_type', Post::class)
            ->where('bookmarkable_id', $post->id)
            ->delete();

        return response()->json([
            'message' => 'Bookmark removed',
        ]);
    }

    /**
     * Get user's bookmarks
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $folder = $request->query('folder');

        $query = Bookmark::where('user_id', $user->id)
            ->with('bookmarkable');

        if ($folder) {
            $query->where('folder_name', $folder);
        }

        $bookmarks = $query->paginate(20);

        return response()->json($bookmarks);
    }
}


