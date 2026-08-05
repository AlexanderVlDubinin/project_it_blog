<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $notifications = $user->notifications()->paginate(15);

        return view('notifications.index', [
            'notifications' => $notifications,
        ]);
    }

    public function readAndRedirect($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);

        // Mark it as read
        $notification->markAsRead();

        // Smart redirect depending on the type of data
        $data = $notification->data['data'] ?? [];

        // If this is a response to a comment, send it to a post with an anchor to the comment.
        if (isset($data['type']) && $data['type'] === 'comment_reply') {
            return redirect('/posts/' . $data['post_id'] . '#comment-' . $data['comment_id']);
        }

        // If this is a general admin notification or a default redirect
        return redirect()->route('notifications.index')->with('success', 'The notification has been read');
    }

    public function markAllAsRead(Request $request)
    {
        // A collection of unread notifications and a call to the markAsRead() method
        auth()->user()->unreadNotifications->markAsRead();

        // Returning the user back with an alert about a successful action
        return back()->with('success', 'All notifications are marked as read.');
    }

    public function deleteAllRead()
    {
        $user = auth()->user();

        $user->notifications()->whereNotNull('read_at')->delete();

        return back()->with('success', 'All read notifications have been deleted.');
    }
}
