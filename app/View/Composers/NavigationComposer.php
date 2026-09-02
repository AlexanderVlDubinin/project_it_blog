<?php

namespace App\View\Composers;

use Illuminate\View\View;

class NavigationComposer
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Compose the view.
     * Add unread notifications count to the view.
     */
    public function compose(View $view): void
    {
        if (auth()->check()) {
            $view->with([
                'unreadNotificationsCount' => auth()->user()->unreadNotifications()->count(),
                // 'latestNotifications' => auth()->user()->unreadNotifications()->take(5)->get(), // for drop-down only
            ]);
        }
    }
}
