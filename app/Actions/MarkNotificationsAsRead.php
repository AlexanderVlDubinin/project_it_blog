<?php

namespace App\Actions;

class MarkNotificationsAsRead
{
    /**
     * Mark notifications as read.
     */
    public function __invoke(string $id = ''): array|null
    {
        if ($id) {
            // Mark a specific (ID) notification as read
            $notification = auth()->user()->notifications()->findOrFail($id);

            if (is_null($notification->read_at)) {
                // Mark it as read
                $notification->markAsRead();
            }

            return $notification->data['data'] ?? [];
        } else {
            // Mark all notifications as read
            auth()->user()->unreadNotifications->markAsRead();
        }

        return null;
    }
}
