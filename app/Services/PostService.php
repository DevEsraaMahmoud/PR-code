<?php

namespace App\Services;

use App\Events\PostCreated;
use App\Models\Tag;
use App\Repositories\PostRepository;
use App\Repositories\SnippetRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PostService
{
    public function __construct(
        private PostRepository $postRepository,
        private SnippetRepository $snippetRepository
    ) {
    }

    public function createPost(array $data, int $userId): array
    {
        return DB::transaction(function () use ($data, $userId) {
            // Extract snippets and tags from data
            $body = $data['body'];
            $tags = $data['tags'] ?? [];
            $visibility = $data['visibility'] ?? 'public';
            $snippets = [];

            // Generate slug from title
            $slug = Str::slug($data['title']);
            $originalSlug = $slug;
            $count = 1;
            while (\App\Models\Post::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }

            // Create post
            $post = $this->postRepository->create([
                'user_id' => $userId,
                'title' => $data['title'],
                'slug' => $slug,
                'body' => $body,
                'visibility' => $visibility,
                'meta' => $data['meta'] ?? null,
            ]);

            // Process body blocks and create snippets
            $blockIndex = 0;
            foreach ($body as $block) {
                if ($block['type'] === 'code') {
                    $snippets[] = [
                        'post_id' => $post->id,
                        'language' => $block['language'] ?? 'text',
                        'code_text' => $block['content'],
                        'block_index' => $blockIndex,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                $blockIndex++;
            }

            if (!empty($snippets)) {
                $this->snippetRepository->createMany($snippets);
            }

            // Attach tags
            if (!empty($tags)) {
                $tagIds = [];
                foreach ($tags as $tagName) {
                    $tag = Tag::firstOrCreate(
                        ['name' => $tagName],
                        ['slug' => Str::slug($tagName)]
                    );
                    $tagIds[] = $tag->id;
                }
                $post->tags()->sync($tagIds);
            }

            // Clear trending cache
            Cache::forget('trending_posts');

            // Dispatch event
            event(new PostCreated($post));

            return [
                'post' => $this->postRepository->find($post->id),
            ];
        });
    }

    public function getPost(int|string $idOrSlug): ?array
    {
        $post = is_numeric($idOrSlug)
            ? $this->postRepository->find((int) $idOrSlug)
            : $this->postRepository->findBySlug($idOrSlug);

        if (!$post) {
            return null;
        }

        // Ensure all relationships are loaded
        if (!$post->relationLoaded('snippets')) {
            $post->load([
                'snippets.allComments' => function ($query) {
                    $query->with(['user', 'replies.user'])->orderBy('start_line');
                },
                'snippets.comments' => function ($query) {
                    $query->with(['user', 'replies.user'])->orderBy('start_line');
                }
            ]);
        }
        if (!$post->relationLoaded('comments')) {
            $post->load('comments.user', 'comments.replies.user');
        }
        if (!$post->relationLoaded('allComments')) {
            $post->load('allComments.user', 'allComments.replies.user');
        }

        return [
            'post' => $post,
        ];
    }

    public function getAllPosts(int $perPage = 15, ?string $visibility = 'public'): LengthAwarePaginator
    {
        return $this->postRepository->paginate($perPage, $visibility);
    }

    public function searchPosts(string $query, ?string $language = null, ?array $tags = null, int $perPage = 15): LengthAwarePaginator|array
    {
        if ($tags && !empty($tags)) {
            $tagModels = Tag::whereIn('slug', $tags)->pluck('id');
            $posts = $this->postRepository->searchByTags($tagModels->toArray());
            return [
                'posts' => $posts,
            ];
        }

        if ($language) {
            $posts = $this->postRepository->searchByLanguage($language);
            return [
                'posts' => $posts,
            ];
        }

        if (!empty($query)) {
            // Try fulltext search first, fallback to LIKE
            try {
                $posts = $this->postRepository->fulltextSearch($query);
            } catch (\Exception $e) {
                $posts = $this->postRepository->searchByTitle($query);
            }
            return [
                'posts' => $posts,
            ];
        }

        return $this->postRepository->paginate($perPage);
    }

    public function getTrendingPosts(): array
    {
        return Cache::remember('trending_posts', 3600, function () {
            return [
                'posts' => $this->postRepository->getTrending(),
            ];
        });
    }

    public function updatePost(int $id, array $data, int $userId): array
    {
        $post = $this->postRepository->find($id);

        if (!$post || $post->user_id !== $userId) {
            throw new \Exception('Unauthorized or post not found');
        }

        return DB::transaction(function () use ($post, $data) {
            $updateData = [];

            if (isset($data['title'])) {
                $updateData['title'] = $data['title'];
                // Regenerate slug if title changed
                $slug = Str::slug($data['title']);
                if ($slug !== $post->slug) {
                    $originalSlug = $slug;
                    $count = 1;
                    while (\App\Models\Post::where('slug', $slug)->where('id', '!=', $post->id)->exists()) {
                        $slug = $originalSlug . '-' . $count++;
                    }
                    $updateData['slug'] = $slug;
                }
            }

            if (isset($data['body'])) {
                $updateData['body'] = $data['body'];
            }

            if (isset($data['visibility'])) {
                $updateData['visibility'] = $data['visibility'];
            }

            if (isset($data['meta'])) {
                $updateData['meta'] = $data['meta'];
            }

            $this->postRepository->update($post, $updateData);

            // Update tags if provided
            if (isset($data['tags'])) {
                $tagIds = [];
                foreach ($data['tags'] as $tagName) {
                    $tag = Tag::firstOrCreate(
                        ['name' => $tagName],
                        ['slug' => Str::slug($tagName)]
                    );
                    $tagIds[] = $tag->id;
                }
                $post->tags()->sync($tagIds);
            }

            // If body changed, update snippets
            if (isset($data['body'])) {
                // Delete old snippets
                $post->snippets()->delete();

                // Create new snippets
                $snippets = [];
                $blockIndex = 0;
                foreach ($data['body'] as $block) {
                    if ($block['type'] === 'code') {
                        $snippets[] = [
                            'post_id' => $post->id,
                            'language' => $block['language'] ?? 'text',
                            'code_text' => $block['content'],
                            'block_index' => $blockIndex,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    $blockIndex++;
                }

                if (!empty($snippets)) {
                    $this->snippetRepository->createMany($snippets);
                }
            }

            Cache::forget('trending_posts');

            return [
                'post' => $this->postRepository->find($post->id),
            ];
        });
    }

    public function deletePost(int $id, int $userId): bool
    {
        $post = $this->postRepository->find($id);

        if (!$post || $post->user_id !== $userId) {
            throw new \Exception('Unauthorized or post not found');
        }

        Cache::forget('trending_posts');

        return $this->postRepository->delete($post);
    }

    public function toggleLike(int $postId, int $userId): array
    {
        $post = $this->postRepository->find($postId);

        if (!$post) {
            throw new \Exception('Post not found');
        }

        $like = $post->likes()->where('user_id', $userId)->first();

        if ($like) {
            $like->delete();
            $liked = false;
        } else {
            $post->likes()->create(['user_id' => $userId]);
            $liked = true;
        }

        return [
            'liked' => $liked,
            'likes_count' => $post->likes()->count(),
        ];
    }
}
