<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_post_with_snippet(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/posts', [
            'title' => 'Test Post',
            'body' => [['type' => 'text', 'content' => 'Test content']],
            'snippets' => [
                [
                    'language' => 'php',
                    'code_text' => 'echo "Hello World";',
                    'block_index' => 0,
                ],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'slug',
                    'snippets' => [
                        '*' => ['id', 'language', 'code_text'],
                    ],
                ],
            ]);

        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('snippets', [
            'language' => 'php',
            'code_text' => 'echo "Hello World";',
        ]);
    }

    public function test_user_can_view_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->getJson("/api/posts/{$post->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'user',
                    'snippets',
                ],
            ]);
    }
}
