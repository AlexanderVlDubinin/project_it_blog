<?php

namespace App\Actions;

use App\Models\Post;
use Illuminate\Pagination\LengthAwarePaginator;

class PublishedPaginatedPosts
{
    /**
     * Create a new class instance.
     */
    public function __invoke(?string $search = null, $limit = 10): LengthAwarePaginator
    {
        $posts = Post::query()
            ->where('posts.is_published', true)
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    if (is_numeric($search)) {
                        $q->where('posts.id', (int) $search);
                    }
                    $q->orWhere('posts.title', 'like', "%{$search}%")
                        ->orWhere('posts.content', 'like', "%{$search}%");
                });

            })
            ->with('user')
            ->orderByDesc('created_at')
            ->orderBy('id')
            ->paginate($limit)
            ->withQueryString();

        return $posts;
    }
}
