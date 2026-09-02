<?php

namespace App\Actions;

use App\Models\Comment;

class RestoreComment
{
    /**
     * Restores a soft-deleted comment.
     */
    public function __invoke(Comment $comment)
    {
        // Find the soft-deleted comment
        $comment = Comment::query()->where('comments.is_deleted', true)->find($comment->id);

        if (!$comment instanceof Comment) {
            return null;
        }

        // Restore the comment
        $comment->update([
            'is_deleted' => false,
            'deletion_reason' => NULL
        ]);
    }
}
