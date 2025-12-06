<?php

namespace App\Repositories;

use App\Models\Snippet;
use Illuminate\Database\Eloquent\Collection;

class SnippetRepository
{
    public function create(array $data): Snippet
    {
        return Snippet::create($data);
    }

    public function createMany(array $snippets): void
    {
        Snippet::insert($snippets);
    }

    public function find(int $id): ?Snippet
    {
        return Snippet::with(['post', 'comments.user', 'comments.replies.user'])->find($id);
    }

    public function findByPost(int $postId): Collection
    {
        return Snippet::where('post_id', $postId)
            ->with(['comments.user', 'comments.replies.user'])
            ->orderBy('block_index')
            ->get();
    }

    public function update(Snippet $snippet, array $data): bool
    {
        return $snippet->update($data);
    }

    public function delete(Snippet $snippet): bool
    {
        return $snippet->delete();
    }
}

