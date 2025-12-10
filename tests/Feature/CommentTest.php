<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Snippet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_inline_comment(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $snippet = Snippet::factory()->create(['post_id' => $post->id]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/posts/{$post->id}/comments", [
            'body' => 'Great code!',
            'snippet_id' => $snippet->id,
            'start_line' => 5,
            'end_line' => 5,
            'is_inline' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'comment' => [
                    'id',
                    'body',
                    'start_line',
                    'is_inline',
                ],
            ]);

        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'snippet_id' => $snippet->id,
            'start_line' => 5,
            'is_inline' => true,
        ]);
    }

    public function test_user_can_resolve_comment(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'resolved' => false,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/comments/{$comment->id}/resolve");

        $response->assertStatus(200)
            ->assertJson([
                'comment' => [
                    'resolved' => true,
                ],
            ]);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'resolved' => true,
        ]);
    }
}
