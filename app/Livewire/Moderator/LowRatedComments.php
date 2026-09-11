<?php

namespace App\Livewire\Moderator;

use App\Models\Comment;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Livewire component for displaying the low-rated comments in the moderator dashboard.
 */
class LowRatedComments extends Component
{
    /**
     * The number of comments to display per page.
     */
    public int $perPage;

    /**
     * The threshold for the rating of comments.
     */
    public int $threshold = -5;

    /**
     * Mount the component - set the initial value of perPage.
     */
    public function mount(): void
    {
        $this->perPage = new Comment()->getPerPage();
    }

    /**
     * Load more comments after the user clicks the "Show More" button.
     */
    public function loadMore(): void
    {
        $this->perPage += new Comment()->getPerPage();
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        /**
         * Query for comments with a rating lower than the threshold.
         */
        $commentsQuery = Comment::query()
            ->where('is_deleted', false)
            ->whereRaw("
                (SELECT COUNT(*) FROM likes WHERE likes.likeable_id = comments.id AND likes.likeable_type = ? AND likes.is_like = true) -
                (SELECT COUNT(*) FROM likes WHERE likes.likeable_id = comments.id AND likes.likeable_type = ? AND likes.is_like = false) < ?
            ", [Comment::class, Comment::class, $this->threshold]);

        /**
         * Get the comments with their author, post and relationships, ordered by rating.
         */
        $comments = (clone $commentsQuery)
            ->with([
                // get the name of the comment author
                'user' => fn($q) => $q->select('id', 'name'),
                // get the title of the post which was commented
                'post' => fn($q) => $q->withTrashed()->select('id', 'title', 'deleted_at')
            ])
            ->withCount([
                // count likes and dislikes
                'reactions as likes_count' => fn($q) => $q->where('is_like', true),
                'reactions as dislikes_count' => fn($q) => $q->where('is_like', false),
            ])
            // Order by rating - difference between likes and dislikes
            ->orderByRaw("
                (SELECT COUNT(*) FROM likes WHERE likes.likeable_id = comments.id AND likes.likeable_type = ? AND likes.is_like = true) -
                (SELECT COUNT(*) FROM likes WHERE likes.likeable_id = comments.id AND likes.likeable_type = ? AND likes.is_like = false)
            ", [Comment::class, Comment::class])
            ->limit($this->perPage)
            ->get();

        // Get the total number of low-rated comments
        $commentsCount = $commentsQuery->count();
        // Check if there are more comments to load (more than the current perPage)
        $hasMore = $commentsCount > $this->perPage;

        return view('livewire.moderator.low-rated-comments', [
            'lowRatedComments' => $comments,
            'lowRatedCommentsCount' => $commentsCount,
            'hasMore' => $hasMore
        ]);
    }
}
