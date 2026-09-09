<?php

namespace App\Livewire\Moderator;

use App\Models\Post;
use Illuminate\View\View;
use Livewire\Component;

class RecentDeletedPosts extends Component
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
        $posts = Post::onlyTrashed()
            ->withCount(['reactions as likes_count' => function ($query) {
                $query->where('is_like', true);
            }])
            ->orderByDesc('created_at')
            ->limit($this->perPage)
            ->get();

        $postsCount = Post::query()->whereNotNull('deleted_at')->count();
        $hasMore = $postsCount > $this->perPage;

        return view('livewire.moderator.recent-soft-deleted-posts', [
            'recentSoftDeletedPosts' => $posts,
            'recentSoftDeletedPostsCount' => $postsCount,
            'hasMore' => $hasMore
        ]);
    }
}
