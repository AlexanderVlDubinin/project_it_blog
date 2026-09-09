<?php

namespace App\Livewire\Moderator;

use App\Models\Comment;
use Illuminate\View\View;
use Livewire\Component;

class LowRatedComments extends Component
{
    public int $perPage;
    public int $threshold = -5;

    public function mount(): void
    {
        $this->perPage = new Comment()->getPerPage();
    }

    public function loadMore(): void
    {
        $this->perPage += new Comment()->getPerPage();
    }

    public function render(): View
    {
        $commentsQuery = Comment::query()
            ->where('is_deleted', false)
            ->whereRaw("
                (SELECT COUNT(*) FROM likes WHERE likes.likeable_id = comments.id AND likes.likeable_type = ? AND likes.is_like = true) -
                (SELECT COUNT(*) FROM likes WHERE likes.likeable_id = comments.id AND likes.likeable_type = ? AND likes.is_like = false) < ?
            ", [Comment::class, Comment::class, $this->threshold]);

        $comments = (clone $commentsQuery)
            ->with([
                'user' => fn($q) => $q->select('id', 'name'),
                'post' => fn($q) => $q->withTrashed()->select('id', 'title', 'deleted_at')
            ])
            ->withCount([
                'reactions as likes_count' => fn($q) => $q->where('is_like', true),
                'reactions as dislikes_count' => fn($q) => $q->where('is_like', false),
            ])
            ->limit($this->perPage)
            ->get();

        $commentsCount = $commentsQuery->count();
        $hasMore = $commentsCount > $this->perPage;

        return view('livewire.moderator.low-rated-comments', [
            'lowRatedComments' => $comments,
            'lowRatedCommentsCount' => $commentsCount,
            'hasMore' => $hasMore
        ]);
    }
}
