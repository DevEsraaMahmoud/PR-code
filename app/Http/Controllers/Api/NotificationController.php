<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get user's notifications.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = (int) ($request->query('per_page', 20));
        $unreadOnly = $request->query('unread_only', false);

        $query = Notification::where('user_id', $user->id)
            ->latest();

        if ($unreadOnly) {
            $query->where('read', false);
        }

        $notifications = $query->paginate($perPage);

        return response()->json([
            'data' => $notifications->items(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'unread_count' => Notification::where('user_id', $user->id)->where('read', false)->count(),
            ],
        ]);
    }

    /**
     * Mark notifications as read.
     */
    public function markRead(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:notifications,id'],
        ]);

        $user = $request->user();

        Notification::whereIn('id', $request->ids)
            ->where('user_id', $user->id)
            ->update(['read' => true]);

        return response()->json([
            'message' => 'Notifications marked as read',
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $user = $request->user();

        Notification::where('user_id', $user->id)
            ->where('read', false)
            ->update(['read' => true]);

        return response()->json([
            'message' => 'All notifications marked as read',
        ]);
    }
}
