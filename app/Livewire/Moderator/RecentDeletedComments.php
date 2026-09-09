<?php

namespace App\Livewire\Moderator;

use App\Models\Comment;
use Illuminate\View\View;
use Livewire\Component;

class RecentDeletedComments extends Component
{
    public int $perPage;

    public function mount(): void
    {
        // Gets a perPage from the Comment model
        $this->perPage = new Comment()->getPerPage();
    }

    public function loadMore(): void
    {
        $this->perPage += new Comment()->getPerPage();
    }

    public function render(): View
    {
        $commentsQuery = Comment::query()->where('is_deleted', true);

        $comments = (clone $commentsQuery)
            ->with([
                'user' => fn($q) => $q->select('id', 'name'),
                'post' => fn($q) => $q->withTrashed()->select('id', 'title', 'deleted_at')
            ])
//            ->withCount([
//                'reactions as likes_count' => fn($q) => $q->where('is_like', true),
//                'reactions as dislikes_count' => fn($q) => $q->where('is_like', false),
//            ])
            ->orderByDesc('created_at')
            ->limit($this->perPage)
            ->get();

        $commentsCount = $commentsQuery->count();
        $hasMore = $commentsCount > $this->perPage;

        return view('livewire.moderator.recent-soft-deleted-comments', [
            'recentSoftDeletedComments' => $comments,
            'recentSoftDeletedCommentsCount' => $commentsCount,
            'hasMore' => $hasMore
        ]);
    }
}
