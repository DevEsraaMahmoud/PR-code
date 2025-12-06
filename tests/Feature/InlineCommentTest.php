<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Snippet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InlineCommentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Post $post;
    protected Snippet $snippet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->post = Post::factory()->create([
            'user_id' => $this->user->id,
            'body' => [
                ['type' => 'text', 'content' => 'Test post'],
                ['type' => 'code', 'language' => 'php', 'content' => "<?php\necho 'Hello';\n"],
            ],
        ]);

        $this->snippet = Snippet::create([
            'post_id' => $this->post->id,
            'language' => 'php',
            'code_text' => "<?php\necho 'Hello';\n",
            'block_index' => 1,
        ]);
    }

    public function test_get_post_with_inline_comments(): void
    {
        // Create an inline comment
        Comment::create([
            'user_id' => $this->user->id,
            'post_id' => $this->post->id,
            'snippet_id' => $this->snippet->id,
            'is_inline' => true,
            'start_line' => 1,
            'end_line' => 1,
            'body' => 'Test inline comment',
        ]);

        $response = $this->getJson("/api/posts/{$this->post->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'snippets' => [
                        '*' => [
                            'id',
                            'code_text',
                            'comments',
                        ],
                    ],
                    'inline_comments_index',
                ],
            ]);
    }

    public function test_create_inline_comment(): void
    {
        $response = $this->actingAs($this->user)->postJson(
            "/api/posts/{$this->post->id}/inline-comments",
            [
                'snippet_id' => $this->snippet->id,
                'start_line' => 2,
                'end_line' => 2,
                'body' => 'This is a test inline comment',
                'is_inline' => true,
            ]
        );

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'is_inline',
                    'start_line',
                    'end_line',
                    'body',
                    'user',
                ],
                'message',
            ]);

        $this->assertDatabaseHas('comments', [
            'post_id' => $this->post->id,
            'snippet_id' => $this->snippet->id,
            'is_inline' => true,
            'start_line' => 2,
            'end_line' => 2,
            'body' => 'This is a test inline comment',
        ]);
    }

    public function test_create_inline_comment_requires_authentication(): void
    {
        $response = $this->postJson(
            "/api/posts/{$this->post->id}/inline-comments",
            [
                'snippet_id' => $this->snippet->id,
                'start_line' => 2,
                'end_line' => 2,
                'body' => 'This is a test inline comment',
            ]
        );

        $response->assertStatus(401);
    }

    public function test_create_inline_comment_validates_line_range(): void
    {
        $response = $this->actingAs($this->user)->postJson(
            "/api/posts/{$this->post->id}/inline-comments",
            [
                'snippet_id' => $this->snippet->id,
                'start_line' => 10, // Invalid line number
                'end_line' => 10,
                'body' => 'This should fail',
            ]
        );

        $response->assertStatus(422);
    }

    public function test_update_inline_comment(): void
    {
        $comment = Comment::create([
            'user_id' => $this->user->id,
            'post_id' => $this->post->id,
            'snippet_id' => $this->snippet->id,
            'is_inline' => true,
            'start_line' => 1,
            'end_line' => 1,
            'body' => 'Original comment',
        ]);

        $response = $this->actingAs($this->user)->patchJson(
            "/api/inline-comments/{$comment->id}",
            [
                'body' => 'Updated comment',
            ]
        );

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'body' => 'Updated comment',
                ],
            ]);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'body' => 'Updated comment',
        ]);
    }

    public function test_update_inline_comment_requires_ownership(): void
    {
        $otherUser = User::factory()->create();
        $comment = Comment::create([
            'user_id' => $this->user->id,
            'post_id' => $this->post->id,
            'snippet_id' => $this->snippet->id,
            'is_inline' => true,
            'start_line' => 1,
            'end_line' => 1,
            'body' => 'Original comment',
        ]);

        $response = $this->actingAs($otherUser)->patchJson(
            "/api/inline-comments/{$comment->id}",
            [
                'body' => 'Updated comment',
            ]
        );

        $response->assertStatus(403);
    }

    public function test_delete_inline_comment(): void
    {
        $comment = Comment::create([
            'user_id' => $this->user->id,
            'post_id' => $this->post->id,
            'snippet_id' => $this->snippet->id,
            'is_inline' => true,
            'start_line' => 1,
            'end_line' => 1,
            'body' => 'Comment to delete',
        ]);

        $response = $this->actingAs($this->user)->deleteJson(
            "/api/comments/{$comment->id}"
        );

        $response->assertStatus(200);

        $this->assertDatabaseMissing('comments', [
            'id' => $comment->id,
        ]);
    }
}

