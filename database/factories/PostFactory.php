<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(),
            'body' => [
                [
                    'type' => 'text',
                    'content' => fake()->paragraph(),
                ],
                [
                    'type' => 'code',
                    'language' => 'javascript',
                    'content' => "function hello() {\n    console.log('Hello World');\n}",
                ],
            ],
        ];
    }
}
