<?php

namespace App\Providers;

use App\View\Composers\NavigationComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
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
        // Notification composer
        // Each time the specified Blade templates (layouts.navigation) are going to be rendered (displayed)
        // on the screen, automatically launch the NavigationComposer class
        View::composer('layouts.navigation', NavigationComposer::class);

        /*
        // Alternative: code right here
        View::composer('layouts.navigation', function ($view) {
            if (auth()->check()) {
                $view->with([
                    'unreadNotificationsCount' => auth()->user()->unreadNotifications()->count(),
                    'latestNotifications' => auth()->user()->unreadNotifications()->take(5)->get(),
                ]);
            }
        });
        */

        // Each time the specified Blade templates (filament.topbar-notifications) are going to be rendered (displayed)
        // on the screen, automatically launch the NavigationComposer class
        View::composer('filament.topbar-notifications', NavigationComposer::class);
    }
}
