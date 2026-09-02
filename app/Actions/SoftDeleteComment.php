<?php

namespace App\Actions;

use App\Enum\CommentDeletionReason;
use App\Http\Requests\SoftDeleteCommentRequest;
use App\Models\Comment;

class SoftDeleteComment
{
    /**
     * Soft deletes a comment.
     */
    public function __invoke(SoftDeleteCommentRequest $request, Comment $comment, array $data): string
    {
        // reason for delete a comment
        $reason = $data['reason_key'];

        // Handle custom delete reason
        if ($reason === CommentDeletionReason::OTHER->value) {
            $finalReason = $request->input('custom_reason', 'Violation of community rules');
        }
        // Handle delete reasons
        else {
            $allReasons = CommentDeletionReason::labels();
            $finalReason = $allReasons[$reason];
        }

        // Soft delete the comment
        $comment->update([
            'is_deleted' => true,
            'deletion_reason' => $finalReason
        ]);

        return ($reason == CommentDeletionReason::SELF_DELETE->value)
            ? 'The comment was hidden by the author.'
            : 'The comment was hidden by the moderator.';
    }
}
