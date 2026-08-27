<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Load user IDs
        $userIds = User::query()->pluck('id');

        if ($userIds->isEmpty()) {
            return;
        }

        $likesData = [];
        $totalUsers = $userIds->count();

        // 2. Generating likes for Posts (in chunks)
        Post::query()
            ->select(['id', 'created_at'])
            ->chunk(500, function ($posts) use ($userIds, $totalUsers, &$likesData) {
                foreach ($posts as $post) {
                    if (fake()->boolean(2)) { // 2% chance of no reaction
                        continue;
                    }

                    // Each post is liked by 1 to 35 random unique users.
                    $likesCount = fake()->numberBetween(1, min(35, $totalUsers));
                    $shuffledUsers = $userIds->random($likesCount);

                    foreach ($shuffledUsers as $userId) {
                        $createdAt = fake()->dateTimeBetween($post->created_at, 'now');

                        $likesData[] = [
                            'user_id'       => $userId,
                            'likeable_id'   => $post->id,
                            'likeable_type' => Post::class,
                            'is_like'       => true,
                            'created_at'    => $createdAt,
                            'updated_at'    => $createdAt,
                        ];
                    }
                }
            });

        // 3. Generating likes/dislikes for Comments (in chunks)
        Comment::query()
            ->select(['id', 'created_at'])
            ->chunk(500, function ($comments) use ($userIds, $totalUsers, &$likesData) {
                foreach ($comments as $comment) {
                    if (fake()->boolean(5)) { // 5% chance of no reaction
                        continue;
                    }

                    // Each comment is rated by 1 to 25 unique users.
                    $reactionsCount = fake()->numberBetween(1, min(25, $totalUsers));
                    $shuffledUsers = $userIds->random($reactionsCount);

                    foreach ($shuffledUsers as $userId) {
                        $createdAt = fake()->dateTimeBetween($comment->created_at, 'now');

                        $likesData[] = [
                            'user_id'       => $userId,
                            'likeable_id'   => $comment->id,
                            'likeable_type' => Comment::class,
                            'is_like'       => fake()->boolean(67), // 67% like, 33% dislike
                            'created_at'    => $createdAt,
                            'updated_at'    => $createdAt,
                        ];
                    }
                }
            });

        // 4. Mass recording of all likes in batches of 3,000 lines
        if (!empty($likesData)) {
            foreach (array_chunk($likesData, 3000) as $chunk) {
                DB::table('likes')->insert($chunk);
            }
        }
    }
}
