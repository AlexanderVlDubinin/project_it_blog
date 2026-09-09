<?php

namespace App\Http\Controllers;

use App\Enum\UserRole;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $dashboardViewArray = [];

        if ( in_array($user->role, [UserRole::USER, UserRole::AUTHOR]) ) {
            $totalComments = Comment::query()->where('user_id', $user->id)->count();

            $likeStats = Like::query()
                ->where('user_id', $user->id)
                ->selectRaw("
                COUNT(CASE WHEN likeable_type = ? AND is_like = true THEN 1 END) as post_likes,
                COUNT(CASE WHEN likeable_type = ? AND is_like = true THEN 1 END) as comment_likes,
                COUNT(CASE WHEN likeable_type = ? AND is_like = false THEN 1 END) as comment_dislikes
            ", [Post::class, Comment::class, Comment::class])
                ->first();

            $topPosts = Post::query()
                ->where('user_id', '!=', $user->id)
                ->where('is_published', true)
                ->withCount(['reactions as likes_count' => function ($query) {
                    $query->where('is_like', true);
                }])
                ->orderByDesc('likes_count')
                ->take(5)
                ->get();

            $postLikes = $likeStats->post_likes ?? 0;
            $commentLikes = $likeStats->comment_likes ?? 0;
            $commentDislikes = $likeStats->comment_dislikes ?? 0;
            $totalRating = $commentLikes - $commentDislikes;

            $dashboardViewArray = [
                'commentsCount' => $totalComments,
                'postLikesCount' => $postLikes,
                'commentLikesCount' => $commentLikes,
                'commentDislikesCount' => $commentDislikes,
                'totalRating' => $totalRating,
                'topPosts' => $topPosts
            ];

            if ($user->role == UserRole::AUTHOR) {
                $myPosts = Post::query()
                    ->where('user_id', $user->id)
                    ->withCount(['reactions as likes_count' => function ($query) {
                        $query->where('is_like', true);
                    }])
                    ->orderByDesc('likes_count')
                    ->get();

                $dashboardViewArray['myPosts'] = $myPosts;
            }
        }

        return view('dashboard', $dashboardViewArray);
    }
}
