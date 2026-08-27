<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Loading the IDs of all users once into memory (excluding N+1)
        $userIds = User::query()->pluck('id');

        if ($userIds->isEmpty()) {
            return;
        }

        // Using lazy() to avoid overloading memory if there are thousands of posts.
        Post::query()->lazy()->each(function (Post $post) use ($userIds) {

            $rootCommentsCount = fake()->numberBetween(4, 12);
            $rootComments = collect();

            // Creating root comments in a bunch at once (one insert request)
            for ($i = 0; $i < $rootCommentsCount; $i++) {
                $rootComments->push(
                    Comment::factory()
                        ->rootForPost($post->id, $post->created_at)
                        ->create([
                            // A random user from an in-memory collection with no DATABASE queries
                            'user_id' => fake()->boolean(90) ? $userIds->random() : null,
                        ])
                );
            }

            // Running recursion to generate a chain of responses
            $this->createRepliesRecursively($rootComments, $post->id, $userIds, 1, 3);
        });
    }

    /**
     * A recursive method for creating tree responses
     */
    private function createRepliesRecursively(Collection $parentComments, int $postId, Collection $userIds, int $currentDepth, int $maxDepth): void
    {
        if ($currentDepth > $maxDepth) {
            return;
        }

        foreach ($parentComments as $parent) {
            // There is a 20% chance that the comment will have answers.
            if (fake()->boolean(20)) {

                $repliesCount = fake()->numberBetween(1, 3);
                $replies = collect();

                for ($i = 0; $i < $repliesCount; $i++) {
                    $replies->push(
                        Comment::factory()
                            ->child($parent->id, $postId, $parent->created_at)
                            ->create([
                                'user_id' => fake()->boolean(90) ? $userIds->random() : null,
                            ])
                    );
                }

                // Recursive transition to the next level of nesting
                $this->createRepliesRecursively($replies, $postId, $userIds, $currentDepth + 1, $maxDepth);
            }
        }
    }
}
