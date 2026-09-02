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
    // Static cache for image files (for not reading disk every time)
    protected static ?array $cachedFiles = null;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $createdAt = fake()->dateTimeBetween('-1 year', 'now');
        // 25% of the time, the post is updated
        $updatedAt = fake()->boolean(25) ? fake()->dateTimeBetween($createdAt, 'now') : $createdAt;

        return [
            'title' => fake()->sentence(5),
            'content' => implode("\n\n", array_map(
                fn() => fake()->paragraph(5, 10),
                range(1, mt_rand(4, 14))
            )),
            'is_published' => fake()->boolean(80), // 80% of the time, the post is published
            'image' => 'pending', // Fill it in afterCreating, if the files exist / fake()->boolean(75) ? 'pending' : null
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }

    /**
     * Special Factory state (for tests): without image.
     */
    public function withoutImage(): static
    {
        return $this->state([
            'image' => null,
        ]);
    }

    /**
     * Factory setup: image copying logic.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Post $post) {
            // If the image is null in definition or test, do nothing
            if ($post->image === null) {
                return;
            }

            // Source path for image files
            $sourcePath = public_path('images/posts_images');

            // Scanning the directory once for the entire siding cycle
            if (self::$cachedFiles === null) {
                self::$cachedFiles = File::exists($sourcePath) ? File::files($sourcePath) : [];
            }

            // Copying the file & updating the model
            if (!empty(self::$cachedFiles)) {
                $randomFile = fake()->randomElement(self::$cachedFiles);
                $targetFolder = 'posts';
                $newFilename = $targetFolder . '/' . Str::random(12) . '.' . $randomFile->getExtension();

                // Coping file
                Storage::disk('public')->put($newFilename, File::get($randomFile->getRealPath()));

                // Updating the model without changing timestamps (since they are already generated in definition)
                $post->timestamps = false;
                $post->update(['image' => $newFilename]);
                $post->timestamps = true;
            } else {
                // If there are no files, set the image to null
                $post->timestamps = false;
                $post->update(['image' => null]);
                $post->timestamps = true;
            }
        });
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
