<?php

namespace App\Actions;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Pagination\LengthAwarePaginator;

class LoadPostComments
{
    public function __invoke(Post $post): LengthAwarePaginator
    {
        // Loading the post and recursively only the ROOT comments along with their authors
        /*
        $post->load([
            'comments' => function ($query) {
                $query->whereNull('parent_id')->with(['user', 'allChildren'])->orderBy('created_at', 'desc');
            }
        ]);
        */

        // Loading the ROOT comments along with their authors + children + pagination
        return Comment::query()
            ->where('post_id', $post->id)
            ->whereNull('parent_id')
            ->with(['user', 'allChildren']) // Eager loading authors and all children
            ->orderBy('created_at', 'desc')
            ->paginate(5)
            ->fragment('comments_section_start'); // THIS LINE ADDS AN ANCHOR TO THE LINKS
    }
}
