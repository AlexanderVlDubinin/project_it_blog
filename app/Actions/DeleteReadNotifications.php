<?php

namespace App\Actions;

class DeleteReadNotifications
{
    /**
     * Delete all read notifications for the authenticated user.
     */
    public function __invoke(): void
    {
        $user = auth()->user();

        $user->notifications()->whereNotNull('read_at')->delete();
    }
}
