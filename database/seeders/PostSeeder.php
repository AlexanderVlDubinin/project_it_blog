<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $authors = User::query()->where('role', 'author')->get();


        $authors->each(function ($author) {
            Post::factory()
                ->count(fake()->numberBetween(1, 10))
                ->create([
                    'user_id' => $author->id,
                ]);
        });

        /*
        $userIds = User::query()->pluck('id')->toArray();

        Post::factory()->count(30)->create(fn () => [
            'user_id' => fake()->randomElement($userIds),
        ]);
        */
    }
}
