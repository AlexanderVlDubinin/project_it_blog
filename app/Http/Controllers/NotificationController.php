<?php

namespace App\Http\Controllers;

use App\Actions\DeleteReadNotifications;
use App\Actions\GetDataForNotifications;
use App\Actions\MarkNotificationsAsRead;
use App\Actions\UpdateNotificationSettings;
use App\Http\Requests\NotificationsFiltersRequest;
use App\Http\Requests\UpdateNotificationSettingsRequest;
use App\Models\Comment;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a list of notifications.
     */
    public function index(NotificationsFiltersRequest $request, GetDataForNotifications $getDataForNotifications)
    {
        $notificationsData = $getDataForNotifications($request->validated());

        return view('notifications.index', [
            'notifications' => $notificationsData['notifications'],
            'notifications_ttl_days' => $notificationsData['notifications_ttl_days'],
            'notification_types' => $notificationsData['notification_types'],
        ]);
    }

    /**
     * Update notification settings (notifications_ttl_days).
     */
    public function updateSettings(
        UpdateNotificationSettingsRequest $request,
        UpdateNotificationSettings $updateNotificationSettings,
    ) {
        $updateNotificationSettings($request->validated());

        return back()->with('success', 'Notification settings have been updated successfully.');
    }

    /**
     * Mark a notification as read and redirect based on the type of data.
     * Comment reply - redirect to post with comment anchor
     * Default - redirect to notifications index
     */
    public function readAndRedirect($id, MarkNotificationsAsRead $markNotificationsAsRead)
    {
        $data = $markNotificationsAsRead($id);

        // Smart redirect depending on the type of data
        // If this is a response to a comment, send it to a post with an anchor to the comment.
        if (isset($data['type']) && $data['type'] === 'comment_reply') {
            $comment = Comment::query()->find($data['comment_id']);
            return redirect($comment->pageUrl);
        }

        // If this is a general admin notification or a default redirect
        return redirect()->route('notifications.index')->with('success', 'The notification has been read');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request, MarkNotificationsAsRead $markNotificationsAsRead)
    {
        $markNotificationsAsRead();

        // Returning the user back with an alert about a successful action
        return back()->with('success', 'All notifications are marked as read.');
    }

    /**
     * Delete all read notifications.
     */
    public function deleteAllRead(DeleteReadNotifications $deleteReadNotifications)
    {
        $deleteReadNotifications();

        return back()->with('success', 'All read notifications have been deleted.');
    }
}
