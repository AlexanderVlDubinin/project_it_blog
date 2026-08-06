<?php

namespace App\Providers;

use App\Enum\UserRole;
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
        Gate::define('manage-site', function (User $user) {
            return $user->role === UserRole::ADMIN || $user->role === UserRole::MODERATOR;
        });

        Gate::define('can-be-author', function (User $user) {
            return in_array($user->role, [UserRole::ADMIN, UserRole::MODERATOR, UserRole::AUTHOR]);
        });

        Gate::define('owner-action', function (User $user, Post $post) {
            return $user->id === $post->user_id;
        });
    }
}
