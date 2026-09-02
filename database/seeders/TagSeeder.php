<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tagsPool = Tag::factory()->count(45)->create();

        if ($tagsPool->isEmpty()) {
            return;
        }

        // array for pivot table (post_tag) data
        $pivotRecords = [];

        // Assign tags to posts
        Post::query()
            ->select(['id', 'created_at', 'updated_at'])
            ->lazy(500) // load 500 posts at a time
            ->each(function ($post) use ($tagsPool, &$pivotRecords) {
                // Randomly select 2-7 tags for each post
                $randomTagIds = $tagsPool->random(fake()->numberBetween(2, 7))->pluck('id');

                foreach ($randomTagIds as $tagId) {
                    $pivotRecords[] = [
                        'post_id' => $post->id,
                        'tag_id' => $tagId,
                        'created_at' => $post->created_at,
                        'updated_at' => $post->updated_at ?? $post->created_at,
                    ];
                }
            });

        // Insert pivot table data in chunks (2000 rows per query) so as not to overload the SQL package
        if (!empty($pivotRecords)) {
            foreach (array_chunk($pivotRecords, 2000) as $chunk) {
                DB::table('post_tag')->insert($chunk);
            }
        }
    }
}
