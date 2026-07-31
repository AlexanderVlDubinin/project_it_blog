<?php

namespace App\Actions;

use App\Enum\CommentDeletionReason;
use App\Http\Requests\SoftDeleteCommentRequest;
use App\Models\Comment;

class SoftDeleteComment
{
    public function __invoke(SoftDeleteCommentRequest $request, Comment $comment, array $data): void
    {
        $finalReason = $data['reason_key'];

        if ($finalReason === CommentDeletionReason::OTHER->value) {
            $finalReason = $request->input('custom_reason', 'Violation of community rules');
        }

        $comment->update([
            'is_deleted' => true,
            'deletion_reason' => $finalReason
        ]);
    }
}
