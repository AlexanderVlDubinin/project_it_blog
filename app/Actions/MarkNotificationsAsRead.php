<?php

namespace App\Actions;

class MarkNotificationsAsRead
{
    /**
     * Create a new class instance.
     */
    public function __invoke(string $id = ''): array|null
    {
        if ($id) {
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
