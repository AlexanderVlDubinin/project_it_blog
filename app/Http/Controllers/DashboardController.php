<?php

namespace App\Http\Controllers;

use App\Enum\UserRole;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;

class DashboardController extends Controller
{
    /**
     * Display a listing of the dashboard page.
     */
    public function index()
    {
        $user = auth()->user();
        $dashboardViewArray = [];

        // User or Author dashboard (Admin and Moderator are fully implemented via Livewire)
        if ( in_array($user->role, [UserRole::USER, UserRole::AUTHOR]) ) {
            $totalComments = Comment::query()->where('user_id', $user->id)->count();

            // likes for posts; likes and dislikes for comments
            $likeStats = Like::query()
                ->where('user_id', $user->id)
                ->selectRaw("
                COUNT(CASE WHEN likeable_type = ? AND is_like = true THEN 1 END) as post_likes,
                COUNT(CASE WHEN likeable_type = ? AND is_like = true THEN 1 END) as comment_likes,
                COUNT(CASE WHEN likeable_type = ? AND is_like = false THEN 1 END) as comment_dislikes
            ", [Post::class, Comment::class, Comment::class])
                ->first();

            // top 5 posts by likes
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
            $totalRating = $commentLikes - $commentDislikes; // net rating of comments

            // dashboard view array
            $dashboardViewArray = [
                'commentsCount' => $totalComments,
                'postLikesCount' => $postLikes,
                'commentLikesCount' => $commentLikes,
                'commentDislikesCount' => $commentDislikes,
                'totalRating' => $totalRating,
                'topPosts' => $topPosts
            ];
        }

        return view('dashboard', $dashboardViewArray);
    }
}
