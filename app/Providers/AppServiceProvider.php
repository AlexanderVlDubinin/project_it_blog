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
        Model::unguard(); // disables Mass Assignment protection for all models
        Model::shouldBeStrict(); // enables strict mode for all models
        Model::automaticallyEagerLoadRelationships(); // enables automatic eager loading of relationships
    }
}
