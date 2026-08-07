<?php

namespace Database\Seeders;

use App\Enum\UserRole;
use App\Models\User;
use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();


        User::factory()->create([
            'name' => 'Test User Admin',
            'email' => 'test@example.com',
            'role' => UserRole::ADMIN,
        ]);

        User::factory()->count(3)->create([
            'role' => UserRole::MODERATOR,
        ]);

        User::factory()->count(10)->create([
            'role' => UserRole::AUTHOR,
        ]);

        User::factory()->count(87)->create();

        /*
        // random version
        User::factory()->count(100)->create([
            'role' => function () {
                $chance = mt_rand(1, 100);
                return match (true) {
                    $chance <= 3  => UserRole::MODERATOR, // 1 - 3 (3%)
                    $chance <= 13 => UserRole::AUTHOR,    // 4 - 13 (10%)
                    default       => UserRole::USER,      // 14 - 100 (87%)
                };
            },
        ]);
        */

        $this->call(PostSeeder::class);

        $this->call(CommentSeeder::class);

        $this->call(TagSeeder::class);
    }
}
