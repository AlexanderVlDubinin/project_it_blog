<?php

namespace App\Livewire\Admin;

use App\Models\Post;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Livewire component for listing recently imported posts in the admin dashboard.
 */
class RecentImportedPosts extends Component
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
        // Fetch the posts that were imported from external sources
        $posts = Post::withTrashed()
            ->where('source_type', '!=', 'user')
            ->withCount(['reactions as likes_count' => function ($query) {
                $query->where('is_like', true);
            }])
            ->orderByDesc('created_at')
            ->limit($this->perPage)
            ->get();

        // Count the total number of imported posts
        $postsCount = Post::query()->where('source_type', '!=', 'user')->count();
        // Check if there are more posts to load (more than the current perPage)
        $hasMore = $postsCount > $this->perPage;

        return view('livewire.admin.recent-imported-posts', [
            'recentImportedPosts' => $posts,
            'recentImportedPostsCount' => $postsCount,
            'hasMore' => $hasMore
        ]);
    }
}
