<?php

namespace App\Actions;

use App\Enum\NotificationTypes;
use App\Enum\OldReadNotificationTerms;

class GetDataForNotifications
{
    /**
     * Get data for notifications.
     */
    public function __invoke(array $data): array
    {
        $user = auth()->user();

        $query = $user->notifications();
        if (!empty($data['notification_type'])) {
            //$query->where('data->status', $data['notification_type']); // need jsonb type
            //$query->whereRaw("CAST(data AS jsonb)->>'status' = ?", [$data['notification_type']]); // do not work in tests

            // data type is text with json
            $query->whereJsonContains('data', ['status' => $data['notification_type']]);
        }

        // Reorder notifications: unread first, then by date
        $query->reorder()
            ->orderByRaw('CASE WHEN read_at IS NULL THEN 0 ELSE 1 END ASC')
            ->orderBy('created_at', 'desc');

        $notifications = $query->paginate(15)->withQueryString(); // Paginate notifications with query string

        return [
            'notifications' => $notifications,
            'notifications_ttl_days' => OldReadNotificationTerms::labels(), // terms for old read notifications
            'notification_types' => NotificationTypes::labels(), // notification types
        ];
    }
}
