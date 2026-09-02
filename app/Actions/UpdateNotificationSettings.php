<?php

namespace App\Actions;

class UpdateNotificationSettings
{
    /**
     * Update notification settings.
     * notifications_ttl_days - waiting time for notifications after reading and before deleting
     */
    public function __invoke(array $data): void
    {
        // Update field in database
        auth()->user()->update([
            'notifications_ttl_days' => $data['notifications_ttl_days']
        ]);
    }
}
