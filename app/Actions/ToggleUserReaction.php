<?php

namespace App\Actions;

use App\Models\Comment;
use App\Models\Post;

class ToggleUserReaction
{
    public function __invoke(array $data): array
    {
        $model = $data['type'] === 'post'
            ? Post::query()->findOrFail($data['id'])
            : Comment::query()->findOrFail($data['id']);

        $userId = auth()->id();
        $isLike = (bool)$data['is_like'];

        // Looking for an existing reaction
        $existing = $model->reactions()->where('user_id', $userId)->first();

        if ($existing) {
            if ($existing->is_like == $isLike) {
                // If clicking on the same button, deleting the reaction (cancel).
                $existing->delete();
                //return response()->json(['status' => 'removed']);
            }
            // If clicking on the opposite one, changing the like to dislike
            $existing->update(['is_like' => $isLike]);
            //return response()->json(['status' => 'changed']);
        } else {
            // If there was no reaction, create it
            $model->reactions()->create([
                'user_id' => $userId,
                'is_like' => $isLike
            ]);
        }

        $currentReaction = $model->reactions()->where('user_id', $userId)->first();
        $userReactionStatus = 'none';

        if ($currentReaction) {
            $userReactionStatus = $currentReaction->is_like ? 'like' : 'dislike';
        }

        return [
            'likes_count' => $model->likesCount(),
            'dislikes_count' => $model->dislikesCount(),
            'user_reaction' => $userReactionStatus, // Transmitting the status of 'like', 'dislike' or 'none'
        ];
    }
}
