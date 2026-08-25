<?php

use App\Enum\UserRole;
use App\Models\Comment;
use App\Models\DatabaseNotification;
use App\Models\Post;
use App\Models\User;
use App\Notifications\CommentReplied;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('notification: clicking Mark as read button makes notification read', function () {
    $user = User::factory()->create();
    $notification = DatabaseNotification::factory()
        ->filamentData(['status' => 'info', 'title' => 'Info Alert'])
        ->create(['notifiable_id' => $user->id, 'created_at' => now()->subDays(14)]);
    $this->actingAs($user);

    visit(route('notifications.index'))
        ->assertPresent('span.badge-notifications-number.bg-red-500')
        ->assertSee('Info Alert')
        ->assertSee('Mark as read')
        ->assertPresent('div.notification-'.$notification->id.' > a.text-blue-700')
        ->click('@mark-as-read-notification-'.$notification->id.'-btn')
        ->assertMissing('@badge-notifications-number')
        ->assertPresent('h3.notification-title-'.$notification->id.'.text-blue-700.saturate-40')
        ->assertSee('Info Alert')
        ->assertDontSee('Mark as read')
    ;
});

test('notification (comment answer): clicking Mark as read & Open button makes notification read & redirects to post page', function () {
    $user = User::factory()->create();
    $commentResponder = User::factory()->create(['name' => 'Comment Responder']);
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $comment = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $commentResponder->id]);

    $user->notify(new CommentReplied($comment, $commentResponder));
    $notification = $user->unreadNotifications()->first();
    $notification->update(['created_at' => now()->subDays(9)]);

    $this->actingAs($user);

    visit(route('notifications.index'))
        ->assertPresent('span.badge-notifications-number.bg-red-500')
        ->assertSee('A new response to your comment')
        ->assertSee('Mark as read & Open')
        ->assertPresent('div.notification-'.$notification->id.' > a.text-blue-700')
        ->click('@mark-as-read-notification-'.$notification->id.'-btn')
        ->assertRoute('posts.show', ['post' => $post])
        ->assertPathIs('/posts/' . $post->id)
        ->assertSee($post->title)
        ->assertSee($comment->body)
        ->click('button.notifications-button-icon')
        ->assertMissing('@badge-notifications-number')
        ->assertPresent('h3.notification-title-'.$notification->id.'.text-blue-700.saturate-40')
        ->assertSee('Open')
        ->assertDontSee('Mark as read & Open')
    ;
});

test('notifications: clicking Mark all as read button makes all unread notification read', function () {
    $user = User::factory()->create();
    $notification1 = DatabaseNotification::factory()
        ->filamentData(['status' => 'info', 'title' => 'Info Alert #1'])
        ->create(['notifiable_id' => $user->id, 'created_at' => now()->subDays(5)]);
    $notification2 = DatabaseNotification::factory()
        ->filamentData(['status' => 'info', 'title' => 'Info Alert #2'])
        ->create(['notifiable_id' => $user->id, 'created_at' => now()->subDays(10)]);
    $notification3 = DatabaseNotification::factory()
        ->filamentData(['status' => 'info', 'title' => 'Info Alert #3'])
        ->create(['notifiable_id' => $user->id, 'created_at' => now()->subDays(15)]);
    $this->actingAs($user);

    visit(route('notifications.index'))
        ->assertPresent('span.badge-notifications-number.bg-red-500')
        ->assertSee('Info Alert #1')
        ->assertPresent('h3.notification-title-'.$notification1->id.'.text-blue-700')
        ->assertSee('Info Alert #2')
        ->assertPresent('h3.notification-title-'.$notification2->id.'.text-blue-700')
        ->assertSee('Info Alert #3')
        ->assertPresent('h3.notification-title-'.$notification3->id.'.text-blue-700')
        ->click('@mark-all-as-read-btn')
        ->assertMissing('@badge-notifications-number')
        ->assertPresent('h3.notification-title-'.$notification1->id.'.text-blue-700.saturate-40')
        ->assertPresent('h3.notification-title-'.$notification2->id.'.text-blue-700.saturate-40')
        ->assertPresent('h3.notification-title-'.$notification3->id.'.text-blue-700.saturate-40')
        ->assertDontSee('Mark all as read')
    ;
});

test('notifications: clicking Clear all read button deletes all read notifications', function () {
    $user = User::factory()->create();
    $notification1 = DatabaseNotification::factory()
        ->filamentData(['status' => 'info', 'title' => 'Info Alert #1'])
        ->create(['notifiable_id' => $user->id, 'created_at' => now()->subDays(5)]);
    $notification2 = DatabaseNotification::factory()
        ->filamentData(['status' => 'info', 'title' => 'Info Alert #2'])
        ->create(['notifiable_id' => $user->id, 'created_at' => now()->subDays(10)]);
    $readAt = now()->subDays(12);
    $notification3 = DatabaseNotification::factory()->read($readAt)
        ->filamentData(['status' => 'info', 'title' => 'Info Alert #3'])
        ->create(['notifiable_id' => $user->id, 'created_at' => now()->subDays(15)]);
    $notification4 = DatabaseNotification::factory()->read($readAt)
        ->filamentData(['status' => 'info', 'title' => 'Info Alert #4'])
        ->create(['notifiable_id' => $user->id, 'created_at' => now()->subDays(15)]);
    $this->actingAs($user);

    visit(route('notifications.index'))
        ->assertPresent('h3.notification-title-'.$notification1->id.'.text-blue-700')
        ->assertPresent('div.notification-'.$notification1->id.'.action-button')
        ->assertPresent('h3.notification-title-'.$notification2->id.'.text-blue-700')
        ->assertPresent('div.notification-'.$notification2->id.'.action-button')
        ->assertPresent('h3.notification-title-'.$notification3->id.'.text-blue-700.saturate-40')
        ->assertPresent('h3.notification-title-'.$notification4->id.'.text-blue-700.saturate-40')
        ->assertScript('(() => { window.confirm = () => true; return true; })()', true) // Mock confirm
            ->click('@clear-all-read-btn') // Click the "Clear all read" button
        ->assertPresent('h3.notification-title-'.$notification1->id.'.text-blue-700')
        ->assertPresent('div.notification-'.$notification1->id.'.action-button')
        ->assertPresent('h3.notification-title-'.$notification2->id.'.text-blue-700')
        ->assertPresent('div.notification-'.$notification2->id.'.action-button')
        ->assertSee('Info Alert #1')
        ->assertSee('Info Alert #2')
        ->assertDontSee('Info Alert #3')
        ->assertDontSee('Info Alert #4')
        ->assertDontSee('Clear all read')
    ;
});







