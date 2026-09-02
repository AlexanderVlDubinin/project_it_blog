<?php

namespace App\Actions;

use App\Models\Comment;

class UpdateComment
{
    /**
     * Updates a comment.
     */
    public function __invoke(Comment $comment, array $data): void
    {
        $comment->update([
            'body' => $data['body'],
        ]);
    }
}
