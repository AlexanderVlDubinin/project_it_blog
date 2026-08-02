<?php

namespace App\Actions;

use App\Models\Post;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

class PublishedPaginatedPosts
{
    /**
     * Create a new class instance.
     */
    public function __invoke(array $filters = [], $limit = 10): array // LengthAwarePaginator (for posts only)
    {
        $canManageSite = Gate::allows('manage-site');

        $postsQuery = Post::query();

        if (!$canManageSite) {
            $postsQuery->where('posts.is_published', true);
        }

        $postsQuery->whereHas('user', function ($query) {
            $query->whereIn('role', ['admin', 'moderator', 'author']);
        });

        $posts = $postsQuery
            // Line search (q)
            ->when(!empty($filters['q']), function ($query) {
                $search = trim((string) request('q'));
                $query->where(function ($q) use ($search) {
                    if (is_numeric($search)) {
                        $q->where('posts.id', (int) $search);
                    }
                    $q->orWhere('posts.title', 'like', "%{$search}%")
                        ->orWhere('posts.content', 'like', "%{$search}%");
                });
            })

            // 2. Filter by author (user_id)
            ->when(!empty($filters['user_id']), function ($query) use ($filters) {
                $query->where('posts.user_id', $filters['user_id']);
            })

            // 3. Filter by date "From" (date_from)
            ->when(!empty($filters['date_from']), function ($query) use ($filters) {
                $query->whereDate('posts.created_at', '>=', $filters['date_from']);
            })

            // 4. Filter by date "To" (date_to)
            ->when(!empty($filters['date_to']), function ($query) use ($filters) {
                $query->whereDate('posts.created_at', '<=', $filters['date_to']);
            })

            ->with('user')
            ->withCount('comments')
            ->orderByDesc('created_at')
            ->orderBy('id')
            ->paginate($limit)
            ->withQueryString();

        $authors = User::query()
            ->whereIn('role', ['admin', 'moderator', 'author'])
            ->whereHas('posts', function ($query) use ($canManageSite) {
                if (!$canManageSite) {
                    $query->where('is_published', true);
                }
            })
            ->orderBy('name')
            ->get();

        return [
            'posts' => $posts,
            'authors' => $authors
        ];
    }
}
