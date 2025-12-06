<?php

namespace App\Events;

use App\Models\Comment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentCreatedBroadcast implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Comment $comment;

    /**
     * Create a new event instance.
     */
    public function __construct(Comment $comment)
    {
        $this->comment = $comment->load(['user', 'snippet', 'post', 'parent', 'replies.user']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $postId = $this->comment->post_id ?? $this->comment->snippet->post_id ?? null;
        
        if (!$postId) {
            return [];
        }

        return [
            new PrivateChannel('post.' . $postId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'comment.created';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'comment' => [
                'id' => $this->comment->id,
                'user_id' => $this->comment->user_id,
                'post_id' => $this->comment->post_id,
                'snippet_id' => $this->comment->snippet_id,
                'parent_id' => $this->comment->parent_id,
                'is_inline' => $this->comment->is_inline,
                'start_line' => $this->comment->start_line,
                'end_line' => $this->comment->end_line,
                'body' => $this->comment->body,
                'created_at' => $this->comment->created_at->toIso8601String(),
                'user' => [
                    'id' => $this->comment->user->id,
                    'name' => $this->comment->user->name,
                ],
                'snippet' => $this->comment->snippet ? [
                    'id' => $this->comment->snippet->id,
                    'language' => $this->comment->snippet->language,
                ] : null,
            ],
        ];
    }
}
