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
        return Post::with(['user', 'snippets', 'tags', 'snippets.comments.user', 'snippets.comments.replies.user', 'comments.user', 'comments.replies.user'])
            ->find($id);
    }

    public function findBySlug(string $slug): ?Post
    {
        return Post::with(['user', 'snippets', 'tags', 'snippets.comments.user', 'snippets.comments.replies.user', 'comments.user', 'comments.replies.user'])
            ->where('slug', $slug)
            ->first();
    }

    public function getAll(): Collection
    {
        return Post::with(['user', 'snippets', 'tags'])
            ->where('visibility', 'public')
            ->latest()
            ->get();
    }

    public function paginate(int $perPage = 15, ?string $visibility = 'public'): LengthAwarePaginator
    {
        $query = Post::with(['user', 'snippets', 'tags'])
            ->latest();

        if ($visibility) {
            $query->where('visibility', $visibility);
        }

        return $query->paginate($perPage);
    }

    public function searchByTitle(string $query): Collection
    {
        return Post::where('title', 'like', "%{$query}%")
            ->where('visibility', 'public')
            ->with(['user', 'snippets', 'tags'])
            ->latest()
            ->get();
    }

    public function searchByLanguage(string $language): Collection
    {
        return Post::whereHas('snippets', function ($q) use ($language) {
            $q->where('language', $language);
        })
            ->where('visibility', 'public')
            ->with(['user', 'snippets', 'tags'])
            ->latest()
            ->get();
    }

    public function searchByTags(array $tagIds): Collection
    {
        return Post::whereHas('tags', function ($q) use ($tagIds) {
            $q->whereIn('tags.id', $tagIds);
        })
            ->where('visibility', 'public')
            ->with(['user', 'snippets', 'tags'])
            ->latest()
            ->get();
    }

    public function fulltextSearch(string $query): Collection
    {
        // MySQL fulltext search
        return Post::whereRaw("MATCH(title) AGAINST(? IN BOOLEAN MODE)", [$query])
            ->orWhereRaw("MATCH(title) AGAINST(? IN NATURAL LANGUAGE MODE)", [$query])
            ->where('visibility', 'public')
            ->with(['user', 'snippets', 'tags'])
            ->latest()
            ->get();
    }

    public function getTrending(int $limit = 10): Collection
    {
        // Simple trending: posts with most comments in last 7 days
        return Post::with(['user', 'snippets', 'tags'])
            ->where('visibility', 'public')
            ->whereHas('allComments', function ($q) {
                $q->where('created_at', '>=', now()->subDays(7));
            })
            ->withCount('allComments')
            ->orderBy('all_comments_count', 'desc')
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
