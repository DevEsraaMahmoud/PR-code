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
        // First, check if snippet exists
        $snippet = \App\Models\Snippet::find($snippetId);
        \Log::debug("getAllBySnippet for snippet {$snippetId}", [
            'snippet_id' => $snippetId,
            'snippet_exists' => $snippet !== null,
            'snippet_post_id' => $snippet?->post_id,
            'snippet_block_index' => $snippet?->block_index,
        ]);
        
        // Check all comments for this post to see if there are comments with wrong snippet_id
        if ($snippet) {
            // Get all snippets for this post
            $allSnippets = \App\Models\Snippet::where('post_id', $snippet->post_id)
                ->orderBy('block_index')
                ->get(['id', 'post_id', 'block_index']);
            
            $allPostComments = Comment::where('post_id', $snippet->post_id)
                ->where('is_inline', true)
                ->get(['id', 'snippet_id', 'start_line', 'end_line']);
            \Log::debug("All inline comments for post {$snippet->post_id}", [
                'total_comments' => $allPostComments->count(),
                'all_snippets' => $allSnippets->map(function ($s) {
                    return ['id' => $s->id, 'block_index' => $s->block_index];
                })->toArray(),
                'comments_by_snippet' => $allPostComments->groupBy('snippet_id')->map->count()->toArray(),
                'comments' => $allPostComments->toArray(),
                'requested_snippet' => [
                    'id' => $snippet->id,
                    'block_index' => $snippet->block_index,
                    'has_comments' => $allPostComments->where('snippet_id', $snippet->id)->count() > 0,
                ],
            ]);
        }
        
        $comments = Comment::where('snippet_id', $snippetId)
            ->with(['user', 'parent', 'replies.user', 'likes'])
            ->orderBy('start_line')
            ->get();
        
        \Log::debug("getAllBySnippet result for snippet {$snippetId}", [
            'snippet_id' => $snippetId,
            'comments_count' => $comments->count(),
            'comments' => $comments->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'snippet_id' => $comment->snippet_id,
                    'is_inline' => $comment->is_inline,
                    'start_line' => $comment->start_line,
                    'end_line' => $comment->end_line,
                    'body' => substr($comment->body, 0, 50),
                ];
            })->toArray(),
        ]);
        
        return $comments;
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
