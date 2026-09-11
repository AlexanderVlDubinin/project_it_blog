<?php

namespace App\Livewire\Moderator;

use App\Models\Post;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Livewire component for displaying the recent soft-deleted posts in the moderator dashboard.
 */
class RecentDeletedPosts extends Component
{
    /**
     * The number of posts to display per page.
     */
    public int $perPage;

    /**
     * Mount the component - set the initial value of perPage.
     */
    public function mount(): void
    {
        // Dynamic retrieval of a perPage from the Post model (if not specified there, Laravel returns the default 15)
        $this->perPage = new Post()->getPerPage();
    }

    /**
     * Load more posts after the user clicks the "Show More" button.
     */
    public function loadMore(): void
    {
        // Increase by a step equal to the base pagination of this model
        $this->perPage += new Post()->getPerPage();
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        // Fetch the posts that were soft-deleted
        $posts = Post::onlyTrashed()
            ->withCount(['reactions as likes_count' => function ($query) {
                $query->where('is_like', true);
            }])
            ->orderByDesc('created_at') // Sort by creation date in descending order
            ->limit($this->perPage)
            ->get();

        // Count the total number of soft-deleted posts
        $postsCount = Post::query()->whereNotNull('deleted_at')->count();
        // Check if there are more posts to load (more than the current perPage)
        $hasMore = $postsCount > $this->perPage;

        return view('livewire.moderator.recent-soft-deleted-posts', [
            'recentSoftDeletedPosts' => $posts,
            'recentSoftDeletedPostsCount' => $postsCount,
            'hasMore' => $hasMore
        ]);
    }
}
