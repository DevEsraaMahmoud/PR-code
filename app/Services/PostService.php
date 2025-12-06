<?php

namespace App\Services;

use App\Events\PostCreated;
use App\Repositories\PostRepository;
use App\Repositories\SnippetRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
            // Extract snippets from body blocks
            $body = $data['body'];
            $snippets = [];

            // Create post
            $post = $this->postRepository->create([
                'user_id' => $userId,
                'title' => $data['title'],
                'body' => $body,
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

            // Clear trending cache
            Cache::forget('trending_posts');

            // Dispatch event
            event(new PostCreated($post));

            return [
                'post' => $this->postRepository->find($post->id),
            ];
        });
    }

    public function getPost(int $id): ?array
    {
        $post = $this->postRepository->find($id);

        if (!$post) {
            return null;
        }

        return [
            'post' => $post,
        ];
    }

    public function getAllPosts(): array
    {
        return [
            'posts' => $this->postRepository->getAll(),
        ];
    }

    public function searchPosts(string $query, ?string $language = null): array
    {
        if ($language) {
            $posts = $this->postRepository->searchByLanguage($language);
        } else {
            $posts = $this->postRepository->searchByTitle($query);
        }

        return [
            'posts' => $posts,
        ];
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
            // Update post
            $this->postRepository->update($post, [
                'title' => $data['title'] ?? $post->title,
                'body' => $data['body'] ?? $post->body,
            ]);

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
}

