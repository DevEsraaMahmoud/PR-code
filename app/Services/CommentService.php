<?php

namespace App\Services;

use App\Events\CommentCreated;
use App\Events\CommentCreatedBroadcast;
use App\Models\Post;
use App\Repositories\CommentRepository;
use App\Repositories\PostRepository;
use App\Repositories\SnippetRepository;
use Illuminate\Support\Facades\DB;

class CommentService
{
    public function __construct(
        private CommentRepository $commentRepository,
        private PostRepository $postRepository,
        private SnippetRepository $snippetRepository
    ) {
    }

    public function createComment(array $data, int $userId): array
    {
        return DB::transaction(function () use ($data, $userId) {
            $isInline = $data['is_inline'] ?? false;
            $postId = $data['post_id'] ?? null;
            $snippetId = $data['snippet_id'] ?? null;

            // Validate post exists
            if ($postId) {
                $post = $this->postRepository->find($postId);
                if (!$post) {
                    throw new \Exception('Post not found');
                }
            }

            // For inline comments, validate snippet and line range
            if ($isInline && $snippetId) {
                $snippet = $this->snippetRepository->find($snippetId);
                if (!$snippet) {
                    throw new \Exception('Snippet not found');
                }

                // Validate line range
                $lines = explode("\n", $snippet->code_text);
                $totalLines = count($lines);

                $startLine = $data['start_line'] ?? null;
                $endLine = $data['end_line'] ?? null;

                if ($startLine === null || $endLine === null) {
                    throw new \Exception('Line range required for inline comments');
                }

                if ($startLine < 1 || $endLine > $totalLines || $startLine > $endLine) {
                    throw new \Exception("Invalid line range. Lines must be between 1 and {$totalLines}");
                }

                // Ensure post_id is set from snippet
                if (!$postId) {
                    $postId = $snippet->post_id;
                }
            } else {
                // Regular comment - ensure post_id is set
                if (!$postId && $snippetId) {
                    $snippet = $this->snippetRepository->find($snippetId);
                    if ($snippet) {
                        $postId = $snippet->post_id;
                    }
                }

                if (!$postId) {
                    throw new \Exception('Post ID or Snippet ID required');
                }
            }

            $comment = $this->commentRepository->create([
                'user_id' => $userId,
                'post_id' => $postId,
                'snippet_id' => $snippetId,
                'parent_id' => $data['parent_id'] ?? null,
                'is_inline' => $isInline,
                'start_line' => $data['start_line'] ?? null,
                'end_line' => $data['end_line'] ?? null,
                'body' => $data['body'],
            ]);

            // Dispatch event for notifications
            event(new CommentCreated($comment));
            
            // Broadcast comment creation
            broadcast(new CommentCreatedBroadcast($comment))->toOthers();

            return [
                'comment' => $this->commentRepository->find($comment->id),
            ];
        });
    }

    public function getCommentsByPost(int $postId, int $perPage = 20): array
    {
        $post = $this->postRepository->find($postId);

        if (!$post) {
            throw new \Exception('Post not found');
        }

        // Get top-level comments (non-inline, no parent)
        $comments = $post->comments()
            ->with(['user', 'replies.user', 'likes'])
            ->paginate($perPage);

        return [
            'comments' => $comments,
        ];
    }

    public function getCommentsBySnippet(int $snippetId): array
    {
        $snippet = $this->snippetRepository->find($snippetId);

        if (!$snippet) {
            throw new \Exception('Snippet not found');
        }

        // Get inline comments for this snippet
        $comments = $this->commentRepository->getAllBySnippet($snippetId)
            ->where('is_inline', true)
            ->whereNull('parent_id')
            ->load(['user', 'replies.user', 'likes']);

        return [
            'comments' => $comments,
        ];
    }

    public function updateComment(int $id, array $data, int $userId): array
    {
        $comment = $this->commentRepository->find($id);

        if (!$comment || $comment->user_id !== $userId) {
            throw new \Exception('Unauthorized or comment not found');
        }

        // Only allow updating body for now
        $updateData = [];
        if (isset($data['body'])) {
            $updateData['body'] = $data['body'];
        }

        $this->commentRepository->update($comment, $updateData);

        return [
            'comment' => $this->commentRepository->find($id),
        ];
    }

    public function deleteComment(int $id, int $userId): bool
    {
        $comment = $this->commentRepository->find($id);

        if (!$comment || $comment->user_id !== $userId) {
            throw new \Exception('Unauthorized or comment not found');
        }

        return $this->commentRepository->delete($comment);
    }

    public function toggleLike(int $commentId, int $userId): array
    {
        $comment = $this->commentRepository->find($commentId);

        if (!$comment) {
            throw new \Exception('Comment not found');
        }

        $like = $comment->likes()->where('user_id', $userId)->first();

        if ($like) {
            $like->delete();
            $liked = false;
        } else {
            $comment->likes()->create(['user_id' => $userId]);
            $liked = true;
        }

        return [
            'liked' => $liked,
            'likes_count' => $comment->likes()->count(),
        ];
    }
}
