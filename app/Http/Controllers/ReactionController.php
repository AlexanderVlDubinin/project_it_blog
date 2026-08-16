<?php

namespace App\Http\Controllers;

use App\Actions\ToggleUserReaction;
use App\Http\Requests\UserReactionRequest;

class ReactionController extends Controller
{
    public function toggle(UserReactionRequest $request, ToggleUserReaction $toggleUserReaction)
    {
        $result = $toggleUserReaction($request->validated());

        return response()->json([
            'likes_count' => $result['likes_count'],
            'dislikes_count' => $result['dislikes_count'],
            'user_reaction' => $result['user_reaction'], // Transmitting the status of 'like', 'dislike' or 'none'
        ]);
    }
}
