<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    /**
     * Get user profile
     */
    public function show(User $user): JsonResponse
    {
        $user->loadCount(['posts', 'followers', 'following']);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'bio' => $user->bio,
                'skills' => $user->skills ?? [],
                'location' => $user->location,
                'website' => $user->website,
                'github_username' => $user->github_username,
                'avatar_url' => $user->avatar_url,
                'posts_count' => $user->posts_count,
                'followers_count' => $user->followers_count,
                'following_count' => $user->following_count,
                'created_at' => $user->created_at,
            ],
        ]);
    }

    /**
     * Update user profile
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'skills' => 'nullable|array',
            'skills.*' => 'string|max:50',
            'location' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'github_username' => 'nullable|string|max:255',
            'avatar_url' => 'nullable|url|max:500',
            'theme_preference' => 'sometimes|in:dark,light',
        ]);

        $user = $request->user();
        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Get user's posts
     */
    public function posts(User $user, Request $request): JsonResponse
    {
        $posts = $user->posts()
            ->with(['user:id,name,avatar_url', 'tags', 'snippets'])
            ->withCount(['likes', 'comments'])
            ->latest()
            ->paginate(20);

        return response()->json($posts);
    }

    /**
     * Get user's bookmarks
     */
    public function bookmarks(User $user, Request $request): JsonResponse
    {
        $bookmarks = $user->bookmarks()
            ->with('bookmarkable')
            ->latest()
            ->paginate(20);

        return response()->json($bookmarks);
    }
}


