<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_post(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/posts', [
                'title' => 'Test Post',
                'body' => [
                    [
                        'type' => 'text',
                        'content' => 'This is a text block',
                    ],
                    [
                        'type' => 'code',
                        'language' => 'javascript',
                        'content' => 'console.log("Hello World");',
                    ],
                ],
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'body',
                    'user',
                    'snippets',
                ],
            ]);
    }

    public function test_user_can_view_post(): void
    {
        $user = User::factory()->create();

        $postResponse = $this->actingAs($user, 'sanctum')
            ->postJson('/api/posts', [
                'title' => 'Test Post',
                'body' => [
                    [
                        'type' => 'text',
                        'content' => 'This is a text block',
                    ],
                ],
            ]);

        $postId = $postResponse->json('data.id');

        $response = $this->getJson("/api/posts/{$postId}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'body',
                    'user',
                    'snippets',
                    'comments',
                    'inline_comments_index',
                ],
            ]);
    }

    public function test_user_can_search_posts(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/posts', [
                'title' => 'JavaScript Tutorial',
                'body' => [
                    [
                        'type' => 'code',
                        'language' => 'javascript',
                        'content' => 'const x = 1;',
                    ],
                ],
            ]);

        $response = $this->getJson('/api/posts?q=JavaScript');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                    ],
                ],
            ]);
    }
}

