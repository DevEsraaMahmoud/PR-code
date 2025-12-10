<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ReactionController extends Controller
{
    /**
     * Toggle reaction on a post or comment
     */
    public function toggle(Request $request, string $type, int $id): JsonResponse
    {
        $request->validate([
            'reaction_type' => 'required|in:like,love,wow,clap,lightbulb,laugh',
        ]);

        $user = $request->user();
        $reactableType = $type === 'post' ? \App\Models\Post::class : \App\Models\Comment::class;

        $reaction = Reaction::where('user_id', $user->id)
            ->where('reactable_type', $reactableType)
            ->where('reactable_id', $id)
            ->where('type', $request->reaction_type)
            ->first();

        if ($reaction) {
            $reaction->delete();
            $action = 'removed';
        } else {
            Reaction::create([
                'user_id' => $user->id,
                'reactable_type' => $reactableType,
                'reactable_id' => $id,
                'type' => $request->reaction_type,
            ]);
            $action = 'added';
        }

        // Get updated reaction counts
        $counts = Reaction::where('reactable_type', $reactableType)
            ->where('reactable_id', $id)
            ->select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        return response()->json([
            'action' => $action,
            'reaction_type' => $request->reaction_type,
            'counts' => $counts,
        ]);
    }

    /**
     * Get reactions for a post or comment
     */
    public function index(Request $request, string $type, int $id): JsonResponse
    {
        $reactableType = $type === 'post' ? \App\Models\Post::class : \App\Models\Comment::class;

        $reactions = Reaction::where('reactable_type', $reactableType)
            ->where('reactable_id', $id)
            ->with('user:id,name,avatar_url')
            ->get();

        $counts = Reaction::where('reactable_type', $reactableType)
            ->where('reactable_id', $id)
            ->select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        return response()->json([
            'reactions' => $reactions,
            'counts' => $counts,
        ]);
    }
}


