<?php

namespace App\Livewire\Moderator;

use App\Models\Comment;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Livewire component for listing recently soft-deleted comments in the moderator dashboard.
 */
class RecentDeletedComments extends Component
{
    /**
     * The number of comments to display per page.
     */
    public int $perPage;

    /**
     * Mount the component - set the initial value of perPage.
     */
    public function mount(): void
    {
        // Gets a perPage from the Comment model
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
        // Fetch the soft-deleted comments
        $commentsQuery = Comment::query()->where('is_deleted', true);

        // Fetch the soft-deleted comments with relationships
        $comments = (clone $commentsQuery)
            ->with([
                // get the name of the comment author
                'user' => fn($q) => $q->select('id', 'name'),
                // get the title of the post which was commented
                'post' => fn($q) => $q->withTrashed()->select('id', 'title', 'deleted_at')
            ])
//            ->withCount([
//                'reactions as likes_count' => fn($q) => $q->where('is_like', true),
//                'reactions as dislikes_count' => fn($q) => $q->where('is_like', false),
//            ])
            ->orderByDesc('created_at') // Sort by creation date in descending order
            ->limit($this->perPage)
            ->get();

        // Count the total number of soft-deleted comments
        $commentsCount = $commentsQuery->count();
        // Check if there are more comments to load (more than the current perPage)
        $hasMore = $commentsCount > $this->perPage;

        return view('livewire.moderator.recent-soft-deleted-comments', [
            'recentSoftDeletedComments' => $comments,
            'recentSoftDeletedCommentsCount' => $commentsCount,
            'hasMore' => $hasMore
        ]);
    }
}
