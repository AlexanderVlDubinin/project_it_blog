<?php

namespace App\Actions;

use App\Models\Comment;

class RestoreComment
{
    public function __invoke(Comment $comment)
    {
        $comment = Comment::query()->where('comments.is_deleted', true)->find($comment->id);

        if (!$comment instanceof Comment) {
            return null;
        }

        $comment->update([
            'is_deleted' => false,
            'deletion_reason' => NULL
        ]);
    }
}
