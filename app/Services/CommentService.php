<?php

namespace App\Services;

use App\Events\CommentCreated;
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
            $snippet = $this->snippetRepository->find($data['snippet_id']);

            if (!$snippet) {
                throw new \Exception('Snippet not found');
            }

            // Validate line range
            $lines = explode("\n", $snippet->code_text);
            $totalLines = count($lines);

            if ($data['start_line'] < 1 || $data['end_line'] > $totalLines || $data['start_line'] > $data['end_line']) {
                throw new \Exception('Invalid line range');
            }

            $comment = $this->commentRepository->create([
                'user_id' => $userId,
                'snippet_id' => $data['snippet_id'],
                'parent_id' => $data['parent_id'] ?? null,
                'start_line' => $data['start_line'],
                'end_line' => $data['end_line'],
                'body' => $data['body'],
            ]);

            // Dispatch event for notifications
            event(new CommentCreated($comment));

            return [
                'comment' => $this->commentRepository->find($comment->id),
            ];
        });
    }

    public function getCommentsBySnippet(int $snippetId): array
    {
        $snippet = $this->snippetRepository->find($snippetId);

        if (!$snippet) {
            throw new \Exception('Snippet not found');
        }

        return [
            'comments' => $this->commentRepository->findBySnippet($snippetId),
        ];
    }

    public function updateComment(int $id, array $data, int $userId): array
    {
        $comment = $this->commentRepository->find($id);

        if (!$comment || $comment->user_id !== $userId) {
            throw new \Exception('Unauthorized or comment not found');
        }

        $this->commentRepository->update($comment, $data);

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
}

