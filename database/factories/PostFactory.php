<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sourcePath = public_path('images/posts_images');
        $targetFolder = 'posts';

        $newFilename = $targetFolder . '/' . Str::random(12) . '.jpg';

        if (File::exists($sourcePath) && $files = File::files($sourcePath)) { // Gets an array of Symfony\Component\HttpFoundation\File\File objects
            $randomFile = fake()->randomElement($files);
            $imageContent = File::get($randomFile->getRealPath());
            Storage::disk('public')->put($newFilename, $imageContent);
            // just string file paths
            //$filePaths = array_map(fn($file) => $file->getPathname(), $files);
        } else {
            $newFilename = null;
        }

        $createdAt = fake()->dateTimeBetween('-1 year', 'now');
        if (fake()->boolean(25)) {
            $updatedAt = fake()->dateTimeBetween($createdAt, 'now');
        } else {
            $updatedAt = $createdAt;
        }

        return [
            'title' => fake()->sentence(5),
            'content' => collect(range(4, mt_rand(8, 14)))
                ->map(fn($i) => fake()->paragraph(5, 10))
                ->implode("\n\n"), // collect - multiparagraph text
            'is_published' => fake()->boolean(80),
            'image' => $newFilename,
            'created_at' => $createdAt,
            'updated_at' => $updatedAt
        ];
    }

    /*
    // for testing
    public function definition(): array
    {
        // Instead of reading the physical disk, we just generate a fake path string.
        // If you physically need a file in a specific test, you will create it via Storage::fake().
        $newFilename = 'posts/' . Str::random(12) . '.jpg';

        $createdAt = fake()->dateTimeBetween('-1 year', 'now');
        $updatedAt = fake()->boolean(25)
            ? fake()->dateTimeBetween($createdAt, 'now')
            : $createdAt;

        return [
            'title' => fake()->sentence(5),
            // Shorten the text! 2-3 short paragraphs of 2-3 sentences
            // are absolutely enough to check the layout, filtering and logic.
            'content' => fake()->paragraphs(mt_rand(2, 3), true),
            'is_published' => fake()->boolean(80),
            'image' => $newFilename,
            'created_at' => $createdAt,
            'updated_at' => $updatedAt
        ];
    }
    */
}
