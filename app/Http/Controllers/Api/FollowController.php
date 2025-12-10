<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FollowController extends Controller
{
    /**
     * Follow a user
     */
    public function follow(Request $request, User $user): JsonResponse
    {
        $follower = $request->user();

        if ($follower->id === $user->id) {
            return response()->json(['error' => 'Cannot follow yourself'], 400);
        }

        if ($follower->following()->where('following_id', $user->id)->exists()) {
            return response()->json(['error' => 'Already following'], 400);
        }

        $follower->following()->attach($user->id);

        return response()->json([
            'message' => 'Followed successfully',
            'following' => true,
            'followers_count' => $user->followers()->count(),
            'following_count' => $user->following()->count(),
        ]);
    }

    /**
     * Unfollow a user
     */
    public function unfollow(Request $request, User $user): JsonResponse
    {
        $follower = $request->user();

        $follower->following()->detach($user->id);

        return response()->json([
            'message' => 'Unfollowed successfully',
            'following' => false,
            'followers_count' => $user->followers()->count(),
            'following_count' => $user->following()->count(),
        ]);
    }

    /**
     * Check if current user follows a user
     */
    public function check(Request $request, User $user): JsonResponse
    {
        $currentUser = $request->user();
        $isFollowing = $currentUser->following()->where('following_id', $user->id)->exists();

        return response()->json([
            'following' => $isFollowing,
        ]);
    }
}


