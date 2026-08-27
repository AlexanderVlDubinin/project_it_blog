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
        $deletionReasons = CommentDeletionReason::labels();
        $deletionReasons[CommentDeletionReason::OTHER->value] = 'Some custom reason';

        return [
            'body' => fake()->paragraph(2),
            'parent_id' => null,
            // Using closures so that requests are executed ONLY if the id is not passed externally
            'post_id' => fn () => Post::query()->first()?->id ?? Post::factory(),
            'user_id' => fn () => fake()->boolean(90)
                ? (User::query()->first()?->id ?? User::factory())
                : null,
            'is_deleted' => $isDeleted,
            'deletion_reason' => $isDeleted ? fake()->randomElement($deletionReasons) : null,
            // Basic date stubs (will be overwritten via states)
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * The status for the root comment of the post, taking into account its creation date.
     */
    public function rootForPost(int $postId, \DateTimeInterface $postCreatedAt): static
    {
        $createdAt = fake()->dateTimeBetween($postCreatedAt, 'now');
        $updatedAt = fake()->boolean(15) ? fake()->dateTimeBetween($createdAt, 'now') : $createdAt;

        return $this->state([
            'post_id' => $postId,
            'parent_id' => null,
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ]);
    }

    /**
     * Status for child comments.
     */
    public function child(int $parentId, int $postId, \DateTimeInterface $parentCreatedAt): static
    {
        $parentDate = Carbon::parse($parentCreatedAt);
        $timeMaxLimit = $parentDate->copy()->addWeeks(2);
        $maxDate = $timeMaxLimit->isFuture() ? 'now' : $timeMaxLimit;

        $childCreatedAt = fake()->dateTimeBetween($parentCreatedAt, $maxDate);
        $childUpdatedAt = fake()->boolean(15) ? fake()->dateTimeBetween($childCreatedAt, 'now') : $childCreatedAt;

        return $this->state([
            'parent_id' => $parentId,
            'post_id' => $postId,
            'created_at' => $childCreatedAt,
            'updated_at' => $childUpdatedAt,
        ]);
    }
}
