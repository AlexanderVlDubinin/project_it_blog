<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
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
        $posts = Post::all();

        /*
        if ($posts->isEmpty()) {
            $posts = Post::factory(10)->create();
        }
        */

        foreach ($posts as $post) {
            // 1. Creating root comments for a specific post
            $rootComments = Comment::factory(fake()->numberBetween(3, 7))
                ->make([
                    'post_id' => $post->id,
                    'parent_id' => null,
                ])->map(function ($comment) use ($post) {
                    $comment->created_at = fake()->dateTimeBetween($post->created_at, 'now');
                    // With a 15% chance, mark the root comment as edited.
                    if (fake()->boolean(15)) {
                        $comment->updated_at = fake()->dateTimeBetween($comment->created_at, 'now');
                    }
                    $comment->save();
                    return $comment;
                });

            // 2. Starting the generation of the response chain (maximum depth, for example, 3)
            // Turning the array back into a Collection for recursion to work.
            $this->createRepliesRecursively(collect($rootComments), $post->id, 1, 3);
        }
    }

    /**
     * A recursive method for creating tree responses
     */
    private function createRepliesRecursively(Collection $parentComments, int $postId, int $currentDepth, int $maxDepth): void
    {
        if ($currentDepth > $maxDepth) {
            return;
        }

        foreach ($parentComments as $parent) {
            // There is a 20% chance that the comment will have answers.
            if (fake()->boolean(20)) {

                // Generating from 1 to 3 responses to this particular comment (with parent date $parent->created_at).
                $replies = Comment::factory(fake()->numberBetween(1, 3))
                    ->child($parent->id, $postId, $parent->created_at)
                    ->make()
                    ->map(function ($reply) use ($parent) {
                        $reply->created_at = fake()->dateTimeBetween($parent->created_at, 'now');
                        // With a 15% chance, mark the child comment as edited.
                        if (fake()->boolean(15)) {
                            $reply->updated_at = fake()->dateTimeBetween($reply->created_at, 'now');
                        }
                        $reply->save();
                        return $reply;
                    });

                // Recursively moving to the next level of nesting for created responses
                $this->createRepliesRecursively(collect($replies), $postId, $currentDepth + 1, $maxDepth);
            }
        }
    }
}
