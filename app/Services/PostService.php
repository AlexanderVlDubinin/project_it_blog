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

    /**
     * Store a new post.
     */
    public function store(array $data): Post
    {
        return DB::transaction(function () use ($data) {
            $image = $data['image'] ?? null; // image for post
            $tags = $data['tags'] ?? []; // tags for post

            unset($data['image'], $data['remove_image'], $data['tags']);

            $data['is_published'] = (bool)($data['is_published'] ?? false); // is published

            $post = Post::query()->create($data); // create post

            // Handle image
            if ($image) {
                $path = $image->store('posts', 'public');
                $post->image = $path;
                $post->save();
            }

            // Handle tags
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

    /**
     * Update an existing post.
     */
    public function update(Post $post, array $data): Post
    {
        return DB::transaction(function () use ($post, $data) {
            $newImage = $data['image'] ?? null; // new image for post
            $removeImage = (bool)($data['remove_image'] ?? false); // remove old image
            $tags = $data['tags'] ?? [];

            unset($data['image'], $data['remove_image'], $data['tags']);

            $data['is_published'] = (bool)($data['is_published'] ?? false); // is published

            $post->update($data); // update post

            // remove old image
            if ($removeImage && $post->image) {
                if ($post->image) {
                    Storage::disk('public')->delete($post->image);
                }
                $post->image = null;
            }

            // add new image
            if ($newImage) {
                if ($post->image) {
                    Storage::disk('public')->delete($post->image);
                }
                $path = $newImage->store('posts', 'public');
                $post->image = $path;
            }

            // update (add/remove/change) tags
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

    /**
     * Soft delete a post (moving to the trash)
     */
    public function destroy(Post $post): void
    {
        DB::transaction(function () use ($post) {
            $post->delete();
        });
    }
}
