<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Snippet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_comment(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        $snippet = Snippet::factory()->create([
            'post_id' => $post->id,
            'code_text' => "line1\nline2\nline3",
            'language' => 'javascript',
            'block_index' => 0,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/comments', [
                'snippet_id' => $snippet->id,
                'start_line' => 1,
                'end_line' => 2,
                'body' => 'This is a comment',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'snippet_id',
                    'start_line',
                    'end_line',
                    'body',
                    'user',
                ],
            ]);
    }

    public function test_user_can_view_comments(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        $snippet = Snippet::factory()->create([
            'post_id' => $post->id,
            'code_text' => "line1\nline2\nline3",
            'language' => 'javascript',
            'block_index' => 0,
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/comments', [
                'snippet_id' => $snippet->id,
                'start_line' => 1,
                'end_line' => 2,
                'body' => 'This is a comment',
            ]);

        $response = $this->getJson("/api/comments?snippet_id={$snippet->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'body',
                        'start_line',
                        'end_line',
                    ],
                ],
            ]);
    }
}

