<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PostService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function store(array $data): Post
    {
        return DB::transaction(function () use ($data) {
            $image = $data['image'] ?? null;
            $tags = $data['tags'] ?? [];

            unset($data['image'], $data['remove_image'], $data['tags']);

            $data['is_published'] = (bool)($data['is_published'] ?? false);

            $post = Post::query()->create($data);

            if ($image) {
                $path = $image->store('posts', 'public');
                $post->image = $path;
                $post->save();
            }

            if (!empty($tags)) {
                $tagIds = [];
                foreach ($tags as $tagInput) {
                    $cleanTagName = trim(mb_strtolower($tagInput));

                    if ($cleanTagName !== '') {
                        $tag = Tag::query()->firstOrCreate(['name' => $cleanTagName]);
                        $tagIds[] = $tag->id;
                    }
                }
                $post->tags()->sync($tagIds);
            }

            return $post;
        });
    }

    public function update(Post $post, array $data): Post
    {
        return DB::transaction(function () use ($post, $data) {
            $newImage = $data['image'] ?? null;
            $removeImage = (bool)($data['remove_image'] ?? false);
            $tags = $data['tags'] ?? [];

            unset($data['image'], $data['remove_image'], $data['tags']);

            $data['is_published'] = (bool)($data['is_published'] ?? false);

            $post->update($data);

            if ($removeImage && $post->image) {
                if ($post->image) {
                    Storage::disk('public')->delete($post->image);
                }
                $post->image = null;
            }

            if ($newImage) {
                if ($post->image) {
                    Storage::disk('public')->delete($post->image);
                }
                $path = $newImage->store('posts', 'public');
                $post->image = $path;
            }

            $tagIds = [];
            foreach ($tags as $tagInput) {
                $cleanTagName = trim(mb_strtolower($tagInput));

                if ($cleanTagName !== '') {
                    $tag = Tag::query()->firstOrCreate(['name' => $cleanTagName]);
                    $tagIds[] = $tag->id;
                }
            }

            $post->tags()->sync($tagIds);

            $post->save();

            return $post;
        });
    }

    public function destroy(Post $post): void
    {
        DB::transaction(function () use ($post) {
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
            $post->delete();
        });
    }
}
