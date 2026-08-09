<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Like>
 */
class LikeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Selection a random user from the existing ones in the database
            'user_id' => User::query()->inRandomOrder()->first()?->id ?? User::factory(),
            'is_like' => fake()->boolean(67), // 67% chance of a like, 33% dislike
        ];
    }

    // The status for linking to the post
    public function forPost(Post $post): static
    {
        return $this->state(fn (array $attributes) => [
            'likeable_id' => $post->id,
            'likeable_type' => Post::class,
        ]);
    }

    // The status for linking to the comment
    public function forComment(Comment $comment): static
    {
        return $this->state(fn (array $attributes) => [
            'likeable_id' => $comment->id,
            'likeable_type' => Comment::class,
        ]);
    }
}
