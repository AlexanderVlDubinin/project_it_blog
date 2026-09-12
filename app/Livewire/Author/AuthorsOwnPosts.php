<?php

namespace App\Livewire\Author;

use App\Models\Post;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Livewire component for displaying the author's own posts in the author's dashboard.
 */
class AuthorsOwnPosts extends Component
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
        // Get the current authenticated user
        $user = auth()->user();

        // Get the user's posts with likes count
        $myPostsQuery = Post::query()->where('user_id', $user->id);
        $myPosts = $myPostsQuery->withCount(['reactions as likes_count' => function ($query) {
                $query->where('is_like', true);
            }])
            ->orderByDesc('likes_count') // Sort by likes count in descending order
            ->limit($this->perPage)
            ->get();

        // Count the total number of user's posts
        $myPostsCount = $myPostsQuery->count();
        // Check if there are more posts to load (more than the current perPage)
        $hasMore = $myPostsCount > $this->perPage;

        return view('livewire.author.authors-own-posts', [
            'myPosts' => $myPosts,
            'myPostsCount' => $myPostsCount,
            'hasMore' => $hasMore,
        ]);
    }
}
