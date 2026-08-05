<?php

namespace App\Http\Controllers;

use App\Actions\DeleteReadNotifications;
use App\Actions\GetDataForNotifications;
use App\Actions\MarkNotificationsAsRead;
use App\Actions\UpdateNotificationSettings;
use App\Http\Requests\UpdateNotificationSettingsRequest;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(GetDataForNotifications $getDataForNotifications)
    {
        $notificationsData = $getDataForNotifications();

        return view('notifications.index', [
            'notifications' => $notificationsData['notifications'],
            'notifications_ttl_days' => $notificationsData['notifications_ttl_days'],
        ]);
    }

    public function updateSettings(
        UpdateNotificationSettingsRequest $request,
        UpdateNotificationSettings $updateNotificationSettings,
    ) {
        $updateNotificationSettings($request->validated());

        return back()->with('success', 'Notification settings have been updated successfully.');
    }

    public function readAndRedirect($id, MarkNotificationsAsRead $markNotificationsAsRead)
    {
        $data = $markNotificationsAsRead($id);

        // Smart redirect depending on the type of data
        // If this is a response to a comment, send it to a post with an anchor to the comment.
        if (isset($data['type']) && $data['type'] === 'comment_reply') {
            return redirect('/posts/' . $data['post_id'] . '#comment-' . $data['comment_id']);
        }

        // If this is a general admin notification or a default redirect
        return redirect()->route('notifications.index')->with('success', 'The notification has been read');
    }

    public function markAllAsRead(Request $request, MarkNotificationsAsRead $markNotificationsAsRead)
    {
        $markNotificationsAsRead();

        // Returning the user back with an alert about a successful action
        return back()->with('success', 'All notifications are marked as read.');
    }

    public function deleteAllRead(DeleteReadNotifications $deleteReadNotifications)
    {
        $deleteReadNotifications();

        return back()->with('success', 'All read notifications have been deleted.');
    }
}
