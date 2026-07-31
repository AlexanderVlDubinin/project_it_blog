<?php

namespace App\Actions;

use App\Models\Comment;

class ForceDeleteComment
{
    public function __invoke(Comment $comment)
    {
        $comment = Comment::query()->where('comments.is_deleted', true)->find($comment->id);

        if (!$comment instanceof Comment) {
            return null;
        }

        $comment->delete();
    }
}
