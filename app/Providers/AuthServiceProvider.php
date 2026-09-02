<?php

namespace App\Providers;

use App\Enum\UserRole;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Global gates

        // admin can do anything
        Gate::before(function (User $user, string $ability) {
            if ($user->role === UserRole::ADMIN) {
                return true; // admin can do anything
            }
            return null;
        });

        // moderator can manage site
        Gate::define('manage-site', function (User $user) {
            return $user->role === UserRole::MODERATOR;
        });

        // moderator and author can be authors
        Gate::define('can-be-author', function (User $user) {
            return in_array($user->role, [UserRole::MODERATOR, UserRole::AUTHOR]);
        });

        // moderator and owner can change posts
        Gate::define('change-post-action', function (User $user, Post $post) {
            $postOwner = $post->user;

            // only admin can change admins post
            if ($postOwner->role === UserRole::ADMIN) {
                return $user->role === UserRole::ADMIN;
            }

            // moderator and owner can change other posts
            return $user->role === UserRole::MODERATOR
                || $user->id === $post->user_id;
        });

        // owner can do owner actions
        Gate::define('owner-action', function (User $user, Post $post) {
            return $user->id === $post->user_id;
        });
    }
}
