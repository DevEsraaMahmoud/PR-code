<?php

namespace App\Listeners;

use App\Events\CommentCreated;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class SendCommentNotification
{
    /**
     * Handle the event.
     */
    public function handle(CommentCreated $event): void
    {
        $comment = $event->comment;
        $snippet = $comment->snippet;
        $post = $snippet->post;

        // Notify post author (if not the commenter)
        if ($post->user_id !== $comment->user_id) {
            Notification::create([
                'user_id' => $post->user_id,
                'type' => 'comment_on_post',
                'payload' => [
                    'comment_id' => $comment->id,
                    'post_id' => $post->id,
                    'snippet_id' => $snippet->id,
                    'commenter_name' => $comment->user->name,
                    'post_title' => $post->title,
                ],
                'read' => false,
            ]);
        }

        // Notify parent comment author (if replying to a comment)
        if ($comment->parent_id) {
            $parentComment = $comment->parent;
            if ($parentComment->user_id !== $comment->user_id) {
                Notification::create([
                    'user_id' => $parentComment->user_id,
                    'type' => 'reply_to_comment',
                    'payload' => [
                        'comment_id' => $comment->id,
                        'parent_comment_id' => $parentComment->id,
                        'post_id' => $post->id,
                        'snippet_id' => $snippet->id,
                        'commenter_name' => $comment->user->name,
                        'post_title' => $post->title,
                    ],
                    'read' => false,
                ]);
            }
        }
    }
}
