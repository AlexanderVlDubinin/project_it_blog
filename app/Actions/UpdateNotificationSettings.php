<?php

namespace App\Actions;

class UpdateNotificationSettings
{
    /**
     * Create a new class instance.
     */
    public function __invoke(array $data): void
    {
        // Update field in database
        auth()->user()->update([
            'notifications_ttl_days' => $data['notifications_ttl_days']
        ]);
    }
}
