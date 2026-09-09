<?php

namespace App\Livewire\Admin;

use App\Models\Post;
use Illuminate\View\View;
use Livewire\Component;

class RecentImportedPosts extends Component
{
    public int $perPage;

    public function mount(): void
    {
        // Dynamic retrieval of a perPage from the Post model (if not specified there, Laravel returns the default 15)
        $this->perPage = new Post()->getPerPage();
    }

    public function loadMore(): void
    {
        // Increase by a step equal to the base pagination of this model
        $this->perPage += new Post()->getPerPage();
    }

    public function render(): View
    {
        $posts = Post::withTrashed()
            ->where('source_type', '!=', 'user')
            ->withCount(['reactions as likes_count' => function ($query) {
                $query->where('is_like', true);
            }])
            ->orderByDesc('created_at')
            ->limit($this->perPage)
            ->get();

        $postsCount = Post::query()->where('source_type', '!=', 'user')->count();
        $hasMore = $postsCount > $this->perPage;

        return view('livewire.moderator.recent-imported-posts', [
            'recentImportedPosts' => $posts,
            'recentImportedPostsCount' => $postsCount,
            'hasMore' => $hasMore
        ]);
    }
}
