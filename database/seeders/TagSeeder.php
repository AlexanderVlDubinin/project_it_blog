<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $posts = Post::all();

        $tagsPool = Tag::factory()->count(45)->create();

        $posts->each(function ($post) use ($tagsPool) {
            $randomTags = $tagsPool->random(fake()->numberBetween(2, 7))->pluck('id');

            $attachData = $randomTags->combine(
                array_fill(0, $randomTags->count(), [
                    'created_at' => $post->created_at,
                    'updated_at' => $post->updated_at ?? $post->created_at,
                ])
            )->toArray();

            $post->tags()->attach($attachData);
        });
    }
}
