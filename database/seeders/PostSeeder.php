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
        // Using lazy() saves memory with a large number of authors
        User::query()
            ->where('role', 'author')
            ->lazy()
            ->each(function (User $author) {
                Post::factory()
                    ->count(fake()->numberBetween(2, 12))
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
