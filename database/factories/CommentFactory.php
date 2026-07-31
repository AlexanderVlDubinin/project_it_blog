<?php

namespace Database\Factories;

use App\Enum\CommentDeletionReason;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    protected $model = Comment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // In 5% of cases, the comment will be deleted by the admin.
        $isDeleted = fake()->boolean(5);

        // Base creation date
        $createdAt = fake()->dateTimeBetween('-1 year', 'now');

        return [
            'body' => fake()->paragraph(2),
            'parent_id' => null, // By default, the root
            'post_id' => Post::query()->inRandomOrder()->first()?->id ?? Post::factory(),
            'user_id' => fake()->boolean(90) // 10% anonymous
                ? (User::query()->inRandomOrder()->first()?->id ?? User::factory())
                : null,
            'is_deleted' => $isDeleted,
            'deletion_reason' => $isDeleted ? fake()->randomElement([
                CommentDeletionReason::SPAM->value,
                CommentDeletionReason::PROFANITY->value,
                CommentDeletionReason::FLOOD->value,
                CommentDeletionReason::INSULTS->value,
                CommentDeletionReason::RULE_VIOLATION->value,
                'Some custom reason',
            ]) : null,
            'created_at' => $createdAt,
            'updated_at' => $createdAt, // By default, the dates match (the comment was NOT edited)
        ];
    }

    /**
     * Status for child comments
     */
    public function child(int $parentId, int $postId, \DateTimeInterface $parentCreatedAt): static
    {
        // The response date must be later than the parent's date (but not too much), but before the current moment.
        // 1. Parent's date creation
        $parentDate = Carbon::parse($parentCreatedAt);

        // 2. Count time max limit (+2 weeks)
        $timeMaxLimit = $parentDate->copy()->addWeeks(2);

        // 3. Choose which will come earlier: time max limit or the current moment (now)
        $maxDate = $timeMaxLimit->isFuture() ? 'now' : $timeMaxLimit;

        // 4. Generating a date in the correct range
        $childCreatedAt = fake()->dateTimeBetween($parentCreatedAt, $maxDate);

        return $this->state(fn (array $attributes) => [
            'parent_id' => $parentId,
            'post_id' => $postId, // The answer should belong to the same post.
            'created_at' => $childCreatedAt,
            'updated_at' => $childCreatedAt, // Initially equal to the new creation number
        ]);
    }

    /**
     * Comment editing is simulated
     */
    public function edited(): static
    {
        return $this->state(function (array $attributes) {
            // Taking the created_at already calculated in the factory or seeder
            $createdAt = $attributes['created_at'];

            return [
                // Updating to a date strictly LATER THAN the creation date
                'updated_at' => fake()->dateTimeBetween($createdAt, 'now'),
            ];
        });
    }
}
