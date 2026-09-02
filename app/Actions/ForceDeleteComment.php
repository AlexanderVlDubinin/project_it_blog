<?php

namespace App\Actions;

use App\Models\Comment;

class ForceDeleteComment
{
    /**
     * Force deletes a soft-deleted comment.
     */
    public function __invoke(Comment $comment)
    {
        // Find the soft-deleted comment
        $comment = Comment::query()->where('comments.is_deleted', true)->find($comment->id);

        if (!$comment instanceof Comment) {
            return null;
        }

        // final deletion of the comment
        $comment->delete();
    }
}
