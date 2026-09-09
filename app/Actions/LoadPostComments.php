<?php

namespace App\Actions;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Pagination\LengthAwarePaginator;

class LoadPostComments
{
    /**
     * Load post and its comments with authors, children (tree structure),
     * user reactions (likes/dislikes) and pagination.
     */
    public function __invoke(Post $post): array // LengthAwarePaginator
    {
        // Loading the post and its reaction count
        $post->loadCount([
            'reactions as likes_count' => fn($q) => $q->where('is_like', true)
        ])->load('userReaction');

        // Loading the ROOT comments along with their authors + children + pagination
        $comments = Comment::query()
            ->where('post_id', $post->id)
            ->whereNull('parent_id')
            ->with(['user', 'allChildren', 'userReaction']) // Eager loading authors and all children + user reaction
            ->withCount([
                'reactions as likes_count' => function ($query) {
                    $query->where('is_like', true);
                },
                'reactions as dislikes_count' => function ($query) {
                    $query->where('is_like', false);
                }
            ])
            ->orderBy('created_at', 'desc')
            ->paginate()
            ->fragment('comments_section_start'); // THIS LINE ADDS AN ANCHOR TO THE LINKS

        return [
            'post' => $post,
            'comments' => $comments,
        ];
    }
}
