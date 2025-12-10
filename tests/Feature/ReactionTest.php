<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_reaction_to_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/post/{$post->id}/reactions", [
            'reaction_type' => 'love',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'action',
                'reaction_type',
                'counts',
            ]);

        $this->assertDatabaseHas('reactions', [
            'user_id' => $user->id,
            'reactable_type' => Post::class,
            'reactable_id' => $post->id,
            'type' => 'love',
        ]);
    }

    public function test_user_can_remove_reaction(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        Reaction::create([
            'user_id' => $user->id,
            'reactable_type' => Post::class,
            'reactable_id' => $post->id,
            'type' => 'love',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/post/{$post->id}/reactions", [
            'reaction_type' => 'love',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'action' => 'removed',
            ]);

        $this->assertDatabaseMissing('reactions', [
            'user_id' => $user->id,
            'reactable_type' => Post::class,
            'reactable_id' => $post->id,
            'type' => 'love',
        ]);
    }
}


