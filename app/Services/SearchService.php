<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Tag;
use App\Repositories\PostRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SearchService
{
    public function __construct(
        private PostRepository $postRepository
    ) {
    }

    /**
     * Search posts by query, type, and paginate results.
     * 
     * @param string $query Search term
     * @param string|null $type Search type: 'posts' or 'code'
     * @param array|null $tags Array of tag slugs
     * @param string|null $language Programming language filter
     * @param int $perPage Results per page
     * @return LengthAwarePaginator|Collection
     */
    public function search(string $query, ?string $type = 'posts', ?array $tags = null, ?string $language = null, int $perPage = 15): LengthAwarePaginator|Collection
    {
        $cacheKey = 'search_' . md5($query . $type . serialize($tags) . $language . $perPage);

        return Cache::remember($cacheKey, 300, function () use ($query, $type, $tags, $language, $perPage) {
            if ($type === 'code' && $language) {
                // Search in code blocks by language
                return $this->postRepository->searchByLanguage($language);
            }

            if ($tags && !empty($tags)) {
                // Search by tags
                $tagModels = Tag::whereIn('slug', $tags)->pluck('id');
                return $this->postRepository->searchByTags($tagModels->toArray());
            }

            if (!empty($query)) {
                // Try fulltext search
                try {
                    if (DB::getDriverName() === 'mysql') {
                        return $this->postRepository->fulltextSearch($query);
                    }
                } catch (\Exception $e) {
                    // Fallback to LIKE search
                }

                // Fallback: LIKE search on title
                return $this->postRepository->searchByTitle($query);
            }

            // No query - return paginated list
            return $this->postRepository->paginate($perPage);
        });
    }

    /**
     * Search posts by code content (searches in snippets).
     */
    public function searchCode(string $query, ?string $language = null): Collection
    {
        $posts = Post::whereHas('snippets', function ($q) use ($query, $language) {
            $q->where('code_text', 'like', "%{$query}%");
            if ($language) {
                $q->where('language', $language);
            }
        })
            ->where('visibility', 'public')
            ->with(['user', 'snippets', 'tags'])
            ->latest()
            ->get();

        return $posts;
    }
}

