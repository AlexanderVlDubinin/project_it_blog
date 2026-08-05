<?php

namespace App\Actions;

class GetDataForNotifications
{
    /**
     * Create a new class instance.
     */
    public function __invoke(): array
    {
        $user = auth()->user();

        $notifications = $user->notifications()->paginate(15);

        $notifications_ttl_days = [
            0 => 'Never',
            1 => '1 day',
            7 => '1 week',
            14 => '2 weeks',
            30 => '1 month',
            90 => '3 months',
        ];

        return [
            'notifications' => $notifications,
            'notifications_ttl_days' => $notifications_ttl_days,
        ];
    }
}
