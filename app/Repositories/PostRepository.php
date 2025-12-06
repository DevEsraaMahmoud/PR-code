<?php

namespace App\Repositories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PostRepository
{
    public function create(array $data): Post
    {
        return Post::create($data);
    }

    public function find(int $id): ?Post
    {
        return Post::with(['user', 'snippets', 'snippets.comments.user', 'snippets.comments.replies.user'])->find($id);
    }

    public function getAll(): Collection
    {
        return Post::with(['user', 'snippets'])->latest()->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Post::with(['user', 'snippets'])->latest()->paginate($perPage);
    }

    public function searchByTitle(string $query): Collection
    {
        return Post::where('title', 'like', "%{$query}%")
            ->with(['user', 'snippets'])
            ->latest()
            ->get();
    }

    public function searchByLanguage(string $language): Collection
    {
        return Post::whereHas('snippets', function ($q) use ($language) {
            $q->where('language', $language);
        })
            ->with(['user', 'snippets'])
            ->latest()
            ->get();
    }

    public function getTrending(int $limit = 10): Collection
    {
        // Simple trending: posts with most comments in last 7 days
        return Post::with(['user', 'snippets'])
            ->whereHas('comments', function ($q) {
                $q->where('created_at', '>=', now()->subDays(7));
            })
            ->withCount('comments')
            ->orderBy('comments_count', 'desc')
            ->limit($limit)
            ->get();
    }

    public function update(Post $post, array $data): bool
    {
        return $post->update($data);
    }

    public function delete(Post $post): bool
    {
        return $post->delete();
    }
}

