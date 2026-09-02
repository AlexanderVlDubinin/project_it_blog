<?php

namespace App\Actions;

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

class PublishedPaginatedPosts
{
    /**
     * Get a list of published posts with filtering (if specified)
     * and with authors, number of comments, tags, likes, pagination.
     */
    public function __invoke(array $filters = [], $limit = 10): array // LengthAwarePaginator (for posts only)
    {
        $canManageSite = Gate::allows('manage-site'); // for users who can manage site
        $currentUserId = auth()->id();

        $postsQuery = Post::query();

        // Filter published posts (for users who can not manage site)
        if (!$canManageSite) {
            $postsQuery->where(function ($query) use ($currentUserId) {
                // Show the post if it is published...
                $query->where('posts.is_published', true);

                // ...OR if the current user is the author of this post
                if ($currentUserId) {
                    $query->orWhere('posts.user_id', $currentUserId);
                }
            });
        }

        // Filter possible authors (admin, moderator, author)
        $postsQuery->whereHas('user', function ($query) {
            $query->whereIn('role', ['admin', 'moderator', 'author']);
        });

        $posts = $postsQuery
            // Line search (q)
            ->when(!empty($filters['q']), function ($query) use ($filters) { // search by ID, title or content
                $search = trim((string) ($filters['q'] ?? ''));
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

            // 5. Filter by tad
            ->when(!empty($filters['tag_id']), function ($query) use ($filters) {
                $query->whereHas('tags', function ($q) use ($filters) {
                    $q->where('tags.id', $filters['tag_id']);
                });
            })

            ->with(['user', 'tags', 'userReaction']) // adding - user and tags and user reaction
            ->withCount('comments') // number of comments
            // number of user reactions (likes and dislikes)
            ->withCount([
                'reactions as likes_count' => function ($query) {
                    $query->where('is_like', true);
                },
                'reactions as dislikes_count' => function ($query) {
                    $query->where('is_like', false);
                }
            ])
            ->orderByDesc('created_at')
            ->orderBy('id')
            ->paginate($limit)
            ->withQueryString();

        // Get authors
        $authors = User::query()
            ->whereIn('role', ['admin', 'moderator', 'author'])
            ->whereHas('posts', function ($query) use ($canManageSite) {
                if (!$canManageSite) {
                    $query->where('is_published', true);
                }
            })
            ->orderBy('name')
            ->get();

        // Get tags
        $tags = Tag::query()->orderBy('name')->get();

        return [
            'posts' => $posts,
            'authors' => $authors,
            'tags' => $tags
        ];
    }
}
