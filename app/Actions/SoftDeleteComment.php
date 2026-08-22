<?php

namespace App\Actions;

use App\Enum\CommentDeletionReason;
use App\Http\Requests\SoftDeleteCommentRequest;
use App\Models\Comment;

class SoftDeleteComment
{
    public function __invoke(SoftDeleteCommentRequest $request, Comment $comment, array $data): void
    {
        $reason = $data['reason_key'];

        if ($reason === CommentDeletionReason::OTHER->value) {
            $finalReason = $request->input('custom_reason', 'Violation of community rules');
        } else {
            $allReasons = CommentDeletionReason::labels();
            $finalReason = $allReasons[$reason];
        }

        $comment->update([
            'is_deleted' => true,
            'deletion_reason' => $finalReason
        ]);
    }
}
