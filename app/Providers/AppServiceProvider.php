<?php

namespace App\Providers;

use App\Enum\UserRole;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();
        Model::shouldBeStrict();
        Model::automaticallyEagerLoadRelationships();

        Gate::define('manage-site', function (User $user) {
            return $user->role === UserRole::ADMIN || $user->role === UserRole::MODERATOR;
        });

        Gate::define('owner-action', function (User $user, Post $post) {
            return $user->id === $post->user_id;
        });
    }
}
