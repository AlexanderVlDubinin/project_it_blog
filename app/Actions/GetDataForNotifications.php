<?php

namespace App\Actions;

use App\Enum\NotificationTypes;
use App\Enum\OldReadNotificationTerms;

class GetDataForNotifications
{
    /**
     * Create a new class instance.
     */
    public function __invoke(array $data): array
    {
        $user = auth()->user();

        $query = $user->notifications();
        if (!empty($data['notification_type'])) {
            $query->whereRaw("CAST(data AS jsonb)->>'status' = ?", [$data['notification_type']]);
        }

        $notifications = $query->paginate(15)->withQueryString();

        return [
            'notifications' => $notifications,
            'notifications_ttl_days' => OldReadNotificationTerms::labels(),
            'notification_types' => NotificationTypes::labels(),
        ];
    }
}
