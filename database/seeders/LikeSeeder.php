<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Presumably users posts and comments have already been created
        // If not, uncomment the lines below:
        // User::factory()->count(30)->create();
        // Post::factory()->count(15)->create();
        // Comment::factory()->count(50)->create();

        $users = User::all();
        $posts = Post::all();
        $comments = Comment::all();

        // 2. Generating likes for Posts
        foreach ($posts as $post) {
            $noReaction = rand(0, 100) < 2; // 2% chance of no reaction
            if ($noReaction) {
                continue;
            }
            // Each post is liked by 1 to 35 random unique users.
            $shuffledUsers = $users->random(rand(1, min(35, $users->count())));

            foreach ($shuffledUsers as $user) {
                $createdAt = fake()->dateTimeBetween($post->created_at, 'now');

                Like::factory()
                    ->forPost($post)
                    ->create([
                        'user_id' => $user->id,
                        'is_like' => true, // The posts only have likes (hearts)
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt
                    ]);
            }
        }

        // 3. Generating likes and dislikes for Comments
        foreach ($comments as $comment) {
            $noReaction = rand(0, 100) < 5; // 5% chance of no reaction
            if ($noReaction) {
                continue;
            }
            // Each comment is rated by 1 to 25 unique users.
            $shuffledUsers = $users->random(rand(1, min(25, $users->count())));

            foreach ($shuffledUsers as $user) {
                $createdAt = fake()->dateTimeBetween($comment->created_at, 'now');

                Like::factory()
                    ->forComment($comment)
                    ->create([
                        'user_id' => $user->id,
                        // Boolean(80) from the factory will automatically work here (like or dislike)
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt
                    ]);
            }
        }
    }
}
