<?php

namespace App\Actions;

class DeleteReadNotifications
{
    /**
     * Create a new class instance.
     */
    public function __invoke(): void
    {
        $user = auth()->user();

        $user->notifications()->whereNotNull('read_at')->delete();
    }
}
