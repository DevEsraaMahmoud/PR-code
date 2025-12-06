<?php

namespace App\Repositories;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Collection;

class CommentRepository
{
    public function create(array $data): Comment
    {
        return Comment::create($data);
    }

    public function find(int $id): ?Comment
    {
        return Comment::with(['user', 'post', 'snippet', 'parent', 'replies.user', 'likes'])->find($id);
    }

    public function findBySnippet(int $snippetId): Collection
    {
        return Comment::where('snippet_id', $snippetId)
            ->whereNull('parent_id')
            ->where('is_inline', true)
            ->with(['user', 'replies.user', 'likes'])
            ->orderBy('start_line')
            ->get();
    }

    public function getAllBySnippet(int $snippetId): Collection
    {
        return Comment::where('snippet_id', $snippetId)
            ->with(['user', 'parent', 'replies.user', 'likes'])
            ->orderBy('start_line')
            ->get();
    }

    public function findByPost(int $postId): Collection
    {
        return Comment::where('post_id', $postId)
            ->whereNull('parent_id')
            ->where('is_inline', false)
            ->with(['user', 'replies.user', 'likes'])
            ->orderBy('created_at')
            ->get();
    }

    public function getInlineCommentsForSnippet(int $snippetId): Collection
    {
        return Comment::where('snippet_id', $snippetId)
            ->where('is_inline', true)
            ->whereNull('parent_id')
            ->with(['user', 'replies.user', 'likes'])
            ->orderBy('start_line')
            ->get();
    }

    public function update(Comment $comment, array $data): bool
    {
        return $comment->update($data);
    }

    public function delete(Comment $comment): bool
    {
        return $comment->delete();
    }
}
