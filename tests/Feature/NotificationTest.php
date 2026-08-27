<?php

use App\Enum\NotificationTypes;
use App\Enum\UserRole;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Notifications\CommentReplied;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\DatabaseNotification;
use Illuminate\Console\Scheduling\Schedule;

uses(RefreshDatabase::class);

test('authenticated user with no notifications', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('posts.index'));

    $response->assertStatus(200)
        ->assertSee('<button class="notifications-button-icon', false)
        ->assertDontSee('<span class="badge-notifications-number', false);

    $response = $this->get(route('notifications.index'));

    $response->assertStatus(200)
        ->assertSee('<button class="notifications-button-icon', false)
        ->assertDontSee('<span class="badge-notifications-number', false)
        ->assertSee('You do not have any notifications yet.')
        ->assertDontSee('<div class="custom-pagination', false);
});

test('authenticated user with unread notifications', function () {
    $user = User::factory()->create();
    $commentResponder = User::factory()->create(['name' => 'Comment Responder']);
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $comment = Comment::factory()->create([
        'post_id' => $post->id,
        'user_id' => $commentResponder->id,
        'is_deleted' => false
    ]);
    $this->actingAs($user);

    // unread notifications
    DatabaseNotification::factory()
        ->filamentData(['status' => 'success', 'title' => 'Success Alert'])
        ->create(['notifiable_id' => $user->id]);
    DatabaseNotification::factory()
        ->filamentData(['status' => 'info', 'title' => 'Info Alert'])
        ->create(['notifiable_id' => $user->id]);
    // Comment Answer notification
    $user->notify(new CommentReplied($comment, $commentResponder));

    // read notifications
    $readAt = now()->subDays(4);
    DatabaseNotification::factory()
        ->read($readAt)
        ->filamentData(['status' => 'warning', 'title' => 'Warning Alert'])
        ->create(['notifiable_id' => $user->id]);
    $readAt = now()->subDays(12);
    DatabaseNotification::factory()
        ->read($readAt)
        ->filamentData(['status' => 'danger', 'title' => 'Danger Alert'])
        ->create(['notifiable_id' => $user->id]);

    $badgeArray = [
        '<button class="notifications-button-icon',
        '<span class="badge-notifications-number',
        3,
        '</span>',
        '</button>',
    ];

    // testing - badge is visible on different pages
    $response = $this->get(route('posts.index'));

    $response->assertStatus(200)
        ->assertSeeInOrder($badgeArray, false);

    $response = $this->get(route('notifications.index'));

    $response->assertStatus(200)
        ->assertSeeInOrder($badgeArray, false);
});

test('authenticated user with different notifications', function () {
    $user = User::factory()->create();
    $commentResponder = User::factory()->create(['name' => 'Comment Responder']);
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $comment1 = Comment::factory()->create([
        'post_id' => $post->id,
        'user_id' => $commentResponder->id,
        'is_deleted' => false
    ]);
    $comment2 = Comment::factory()->create([
        'post_id' => $post->id,
        'user_id' => $commentResponder->id,
        'is_deleted' => false
    ]);
    $this->actingAs($commentResponder);

    $user->notify(new CommentReplied($comment1, $commentResponder));
    $notification1 = $user->unreadNotifications()->first(); // get notification
    $notification1->update(['read_at' => now(), 'created_at' => now()->subDays(9)]); // Mark as read

    $user->notify(new CommentReplied($comment2, $commentResponder));
    $notification2 = $user->unreadNotifications()->first();
    $notification2->update(['created_at' => now()->subDays(25)]);

    $readAt = now()->subDays(12);
    $notification3 = DatabaseNotification::factory()
        ->read($readAt)                                                             // Mark as read
        ->filamentData(['icon' => 'heroicon-c-arrow-trending-down', 'status' => 'danger', 'title' => 'Danger Alert'])
        ->create(['notifiable_id' => $user->id, 'created_at' => now()->subDays(14)]);

    $notification4 = DatabaseNotification::factory()
        ->filamentData(['icon' => 'heroicon-c-bolt', 'status' => 'success', 'title' => 'Success Alert'])
        ->create(['notifiable_id' => $user->id, 'created_at' => now()->subDays(9)]);

    $this->actingAs($user);

    $badgeArray = [
        '<button class="notifications-button-icon',
            '<span class="badge-notifications-number', 2, '</span>',
        '</button>',
    ];

    $notificationsArray = [
        // for unread system notification (unread, 9 days ago)
        '<div class="notification-block-'.$notification4->id, 'border-green-400', // block & green border
        '<span class="unread-label-'.$notification4->id, 'bg-green-500', '</span>', // unread label
        '<svg class="heroicon-c-bolt', 'text-green-700', '</svg>', // icon
        '<h3 class="notification-title-'.$notification4->id, 'text-green-700', // title
            'Success Alert',
        '</h3>',
        '<p class="notification-body-'.$notification4->id, 'text-green-500', // body
            $notification4->data['body'],
        '</p>',
        '<span class="notification-time-'.$notification4->id, 'text-green-400', // created_at time
            $notification4->created_at->diffForHumans(),
        '</span>',
        '<div class="notification-'.$notification4->id.' action-button', 'text-green-700', // action button
            'Mark as read',
        '</div>',

        // for unread comment notification (unread, 25 days ago)
        '<div class="notification-block-'.$notification2->id, 'border-blue-400', // block & blue border
        '<span class="unread-label-'.$notification2->id, 'bg-blue-500', '</span>', // unread label
        '<svg class="heroicon-o-chat-bubble-left-right', 'text-blue-700', '</svg>', // icon
        '<h3 class="notification-title-'.$notification2->id, 'text-blue-700', // title
            'A new response to your comment',
        '</h3>',
        '<p class="notification-body-'.$notification2->id, 'text-blue-500', // body
            $commentResponder->name . ' answered:',
            $comment2->body,
        '</p>',
        '<span class="notification-time-'.$notification2->id,'text-blue-400', // created_at time
            $notification2->created_at->diffForHumans(),
        '</span>',
        '<div class="notification-'.$notification2->id.' action-button smart-link', 'text-blue-700', // action button-link
            'Mark as read & Open',
        '</div',

        // for read comment notification (read, 9 days ago)
        '<div class="notification-block-'.$notification1->id, 'border-blue-400 saturate-40', // block & blue border + 40% saturation
        '<svg class="heroicon-o-chat-bubble-left-right', 'text-blue-700 saturate-40', '</svg>', // icon
        '<h3 class="notification-title-'.$notification1->id, 'text-blue-700 saturate-40', // title
            'A new response to your comment',
        '</h3>',
        '<p class="notification-body-'.$notification1->id, 'text-blue-500 saturate-40', // body
            $commentResponder->name . ' answered:',
            $comment1->body,
        '</p>',
        '<span class="notification-time-'.$notification1->id, 'text-blue-400 saturate-40', // created_at time
            $notification1->created_at->diffForHumans(),
        '</span>',
        '<div class="notification-'.$notification1->id.' smart-link', 'text-blue-700 saturate-40', // action button
            'Open',
        '</div>',

        // for read system notification (read, 12 days ago)
        '<div class="notification-block-'.$notification3->id, 'border-red-400 saturate-40', // block & red border + 40% saturation
        '<svg class="heroicon-c-arrow-trending-down', 'text-red-700 saturate-40', '</svg>', // icon
        '<h3 class="notification-title-'.$notification3->id, 'text-red-700 saturate-40', // title
            'Danger Alert',
        '</h3>',
        '<p class="notification-body-'.$notification3->id, 'text-red-500 saturate-40', // body
            $notification3->body,
        '</p>',
        '<span class="notification-time-'.$notification3->id, 'text-red-400 saturate-40', // created_at time
            $notification3->created_at->diffForHumans(),
        '</span>',
    ];

    $response = $this->get(route('notifications.index'));

    $response->assertStatus(200)
        ->assertSeeInOrder($badgeArray, false)
        ->assertDontSee('<span class="unread-label-'.$notification1->id, false)
        ->assertDontSee('<span class="unread-label-'.$notification3->id, false)
        ->assertDontSee('<div class="notification-'.$notification3->id, false)
        ->assertSeeInOrder($notificationsArray, false)
    ;
});

test('authenticated user with notification filtering by type', function () {
    $user = User::factory()->create();
    $commentResponder = User::factory()->create(['name' => 'Comment Responder']);
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $comment = Comment::factory()->create([
        'post_id' => $post->id,
        'user_id' => $commentResponder->id,
        'is_deleted' => false
    ]);
    $this->actingAs($commentResponder);

    $user->notify(new CommentReplied($comment, $commentResponder)); // comment reply / blue notification
    $notification1 = $user->unreadNotifications()->first(); // get notification
    $notification1->update(['created_at' => now()->subDays(5)]);

    $notification2 = DatabaseNotification::factory()
        ->filamentData(['icon' => 'heroicon-c-cake', 'status' => 'info', 'title' => 'Info Alert']) // info / blue notification
        ->create(['notifiable_id' => $user->id, 'created_at' => now()->subDays(9)]);

    $notification3 = DatabaseNotification::factory()
        ->filamentData(['icon' => 'heroicon-c-backspace', 'status' => 'warning', 'title' => 'Warning Alert']) // warning / yellow notification
        ->create(['notifiable_id' => $user->id, 'created_at' => now()->subDays(14)]);

    $notification4 = DatabaseNotification::factory()
        ->filamentData(['icon' => 'heroicon-c-arrow-trending-down', 'status' => 'danger', 'title' => 'Danger Alert']) // danger / red notification
        ->create(['notifiable_id' => $user->id, 'created_at' => now()->subDays(17)]);

    $notification5 = DatabaseNotification::factory()
        ->filamentData(['icon' => 'heroicon-c-bolt', 'status' => 'success', 'title' => 'Success Alert']) // success / green notification
        ->create(['notifiable_id' => $user->id, 'created_at' => now()->subDays(25)]);

    $this->actingAs($user);

    $badgeArray = [
        '<button class="notifications-button-icon',
            '<span class="badge-notifications-number', 5, '</span>',
        '</button>',
    ];

    // no filtering
    $response = $this->get(route('notifications.index'));

    $notificationsArray = [
        'A new response to your comment',
        $commentResponder->name . ' answered:',
        'Info Alert',
        'Warning Alert',
        'Danger Alert',
        'Success Alert',
    ];

    $response->assertStatus(200)
        ->assertSeeInOrder($badgeArray, false)
        ->assertSeeInOrder($notificationsArray, false);
    expect($user->notifications()->get()->count())->toBe(5);

    // filtering by info
    $response = $this->get(route('notifications.index', [
        'notification_type' => NotificationTypes::INFO
    ]));

    $notificationsArray = [
        'A new response to your comment',
        $commentResponder->name . ' answered:',
        'Info Alert',
    ];

    $response->assertStatus(200)
        ->assertSeeInOrder($notificationsArray, false)
        ->assertDontSee('Warning Alert')
        ->assertDontSee('Danger Alert')
        ->assertDontSee('Success Alert');
    expect($response->viewData('notifications'))->toHaveCount(2);

    // filtering by warning
    $response = $this->get(route('notifications.index', [
        'notification_type' => NotificationTypes::WARNING
    ]));

    $response->assertStatus(200)
        ->assertSee('Warning Alert')
        ->assertDontSee('A new response to your comment')
        ->assertDontSee($commentResponder->name . ' answered:')
        ->assertDontSee('Info Alert')
        ->assertDontSee('Danger Alert')
        ->assertDontSee('Success Alert');
    expect($response->viewData('notifications'))->toHaveCount(1);

    // filtering by danger
    $response = $this->get(route('notifications.index', [
        'notification_type' => NotificationTypes::DANGER
    ]));

    $response->assertStatus(200)
        ->assertSee('Danger Alert')
        ->assertDontSee('A new response to your comment')
        ->assertDontSee($commentResponder->name . ' answered:')
        ->assertDontSee('Info Alert')
        ->assertDontSee('Warning Alert')
        ->assertDontSee('Success Alert');
    expect($response->viewData('notifications'))->toHaveCount(1);

    // filtering by success
    $response = $this->get(route('notifications.index', [
        'notification_type' => NotificationTypes::SUCCESS
    ]));

    $response->assertStatus(200)
        ->assertSee('Success Alert')
        ->assertDontSee('A new response to your comment')
        ->assertDontSee($commentResponder->name . ' answered:')
        ->assertDontSee('Info Alert')
        ->assertDontSee('Warning Alert')
        ->assertDontSee('Danger Alert');
    expect($response->viewData('notifications'))->toHaveCount(1);
});

test('pagination for 25 notifications, first page - 15, second page - 10', function () {
    $user = User::factory()->create();
    for ($i = 1; $i <= 25; $i++) {
        DatabaseNotification::factory()->create(['notifiable_id' => $user->id]);
    }

    $this->actingAs($user);

    // first page
    $response = $this->get(route('notifications.index'));

    $response->assertStatus(200);
    expect($user->notifications()->get()->count())->toBe(25) // 25 notifications in total
        ->and($response->viewData('notifications'))->toHaveCount(15); // 15 notifications on first page

    // second page
    $response = $this->get(route('notifications.index', ['page' => 2]));

    $response->assertStatus(200);
    expect($user->notifications()->get()->count())->toBe(25) // 25 notifications in total
        ->and($response->viewData('notifications'))->toHaveCount(10); // 10 notifications on second page
});

test('console command deletes old notifications that have already been read', function () {
    $user1 = User::factory()->create(['notifications_ttl_days' => 7]); // delete old read notifications in 7 days after read
    $user2 = User::factory()->create(['notifications_ttl_days' => 0]); // do not delete old read notifications

    // this notification should not be deleted
    $readAt1 = now()->subDays(2);
    $notification1 = DatabaseNotification::factory()->read($readAt1)
        ->filamentData(['status' => 'info', 'title' => 'Info Alert #1'])
        ->create(['notifiable_id' => $user1->id, 'created_at' => now()->subDays(5)]);

    // this notification should be deleted
    $readAt2 = now()->subDays(8);
    $notification2 = DatabaseNotification::factory()->read($readAt2)
        ->filamentData(['status' => 'info', 'title' => 'Info Alert #2'])
        ->create(['notifiable_id' => $user1->id, 'created_at' => now()->subDays(14)]);

    // should not be deleted should not be deleted
    $readAt3 = now()->subDays(30);
    $notification3 = DatabaseNotification::factory()->read($readAt3)
        ->filamentData(['status' => 'info', 'title' => 'Info Alert #3'])
        ->create(['notifiable_id' => $user2->id, 'created_at' => now()->subMonths(2)]);

    // (UNREAD) should not be deleted should not be deleted
    $notification4 = DatabaseNotification::factory()
        ->filamentData(['status' => 'info', 'title' => 'Info Alert #4'])
        ->create(['notifiable_id' => $user2->id, 'created_at' => now()->subMonths(3)]);

    // check for notifications number before command
    expect($user1->notifications()->get()->count())->toBe(2)
        ->and($user2->notifications()->get()->count())->toBe(2);

    // run console command to delete old read notifications
    $this->artisan('notifications:clear-old-read')->assertExitCode(0);

    // check for notifications number after command
    expect($user1->notifications()->get()->count())->toBe(1)
        ->and($user2->notifications()->get()->count())->toBe(2);

    // check for notifications in the database
    $this->assertDatabaseHas('notifications', ['id' => $notification1->id]); // not deleted
    $this->assertDatabaseMissing('notifications', ['id' => $notification2->id]); // deleted
    $this->assertDatabaseHas('notifications', ['id' => $notification3->id]); // not deleted
    $this->assertDatabaseHas('notifications', ['id' => $notification4->id]); // not deleted

    $this->actingAs($user1);
    $response = $this->get(route('notifications.index'));

    $response->assertStatus(200)
        ->assertSee('Info Alert #1')
        ->assertDontSee('Info Alert #2');

    $this->actingAs($user2);
    $response = $this->get(route('notifications.index'));

    $response->assertStatus(200)
        ->assertSee('Info Alert #3')
        ->assertSee('Info Alert #4');

});

test('check that the console command has been added to the daily schedule', function () {
    // Extracting the Laravel Scheduler
    $schedule = app(Schedule::class);

    // search for a console command (notifications:clear-old-read) in the list of registered ones
    /** @var Event|null $commandEvent */
    $commandEvent = collect($schedule->events())->first(function ($event) {
        return str_contains($event->command, 'notifications:clear-old-read');
    });

    expect($commandEvent)->not->toBeNull() // Checking for the existence of a task
        ->and($commandEvent->expression)->toBe('0 0 * * *'); // Periodicity Check (Cron Magic)
});













