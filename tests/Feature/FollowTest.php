<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FollowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_follow_another_user(): void
    {
        $follower = User::factory()->create();
        $following = User::factory()->create();

        Sanctum::actingAs($follower);

        $response = $this->postJson("/api/users/{$following->id}/follow");

        $response->assertStatus(200)
            ->assertJson([
                'following' => true,
            ]);

        $this->assertTrue($follower->following()->where('following_id', $following->id)->exists());
    }

    public function test_user_can_unfollow(): void
    {
        $follower = User::factory()->create();
        $following = User::factory()->create();

        $follower->following()->attach($following->id);

        Sanctum::actingAs($follower);

        $response = $this->deleteJson("/api/users/{$following->id}/follow");

        $response->assertStatus(200)
            ->assertJson([
                'following' => false,
            ]);

        $this->assertFalse($follower->following()->where('following_id', $following->id)->exists());
    }
}


