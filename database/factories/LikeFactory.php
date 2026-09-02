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
        $createdAt = fake()->dateTimeBetween('-1 year', 'now');

        return [
            // The request will be executed only if the user_id is not explicitly passed when calling the factory
            'user_id' => fn () => User::query()->first()?->id ?? User::factory(),
            'likeable_id' => null,  // It must be transmitted through status
            'likeable_type' => null, // It must be transmitted through status
            'is_like' => fake()->boolean(67), // 67% of the time, the like is positive
            'created_at' => $createdAt,
            'updated_at' => $createdAt
        ];
    }

    /**
     * Custom helper state for linking to the post
     */
    public function forPost(Post $post): static
    {
        return $this->state(fn (array $attributes) => [
            'likeable_id' => $post->id,
            'likeable_type' => Post::class,
        ]);
    }

    /**
     * Custom helper state for linking to the comment
     */
    public function forComment(Comment $comment): static
    {
        return $this->state(fn (array $attributes) => [
            'likeable_id' => $comment->id,
            'likeable_type' => Comment::class,
        ]);
    }
}
