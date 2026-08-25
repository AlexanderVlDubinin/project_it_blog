<?php

use App\Enum\NotificationTypes;
use App\Enum\UserRole;
use App\Filament\Pages\SendNotification;
use App\Models\User;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

test('admin can visit send notifications page', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN
    ]);
    // Disable the internal Authenticate from Filament, as there is a custom Middleware
    $this->withoutMiddleware(Authenticate::class);

    $this->actingAs($admin)
        ->get('/admin/send-notification')
        ->assertSuccessful();
});

test('non-admin user is redirected to dashboard', function () {
    $user = User::factory()->create();
    $this->withoutMiddleware(Authenticate::class);

    $this->actingAs($user)
        ->get('/admin/send-notification')
        ->assertRedirect('/dashboard');
});

test('validation errors when submitting empty form fields', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN
    ]);
    $this->withoutMiddleware(Authenticate::class);
    $this->actingAs($admin);

    livewire(SendNotification::class)
        ->call('send') // trying to send empty form
        ->assertHasFormErrors([
            'target' => 'required',
            'title' => 'required',
            'message' => 'required',
        ]);
});

test('requires specifying a specific user if single user is selected', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN
    ]);
    $this->withoutMiddleware(Authenticate::class);
    $this->actingAs($admin);

    livewire(SendNotification::class)
        ->fillForm([
            'target' => 'single',
            'user_id' => null, // leave empty
            'title' => 'Test notification',
            'message' => 'Test notification text',
        ])
        ->call('send')
        ->assertHasFormErrors(['user_id' => 'required']);
});

test('successfully sends notification to a single user', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN
    ]);
    $targetUser = User::factory()->create();
    $this->withoutMiddleware(Authenticate::class);
    $this->actingAs($admin);

    livewire(SendNotification::class)
        ->fillForm([
            'target' => 'single',
            'user_id' => $targetUser->id,
            'title' => 'Test notification',
            'message' => 'Test notification to a single user',
            'notificationType' => NotificationTypes::INFO->value,
            'icon' => 'heroicon-o-user',
        ])
        ->call('send')
        ->assertHasNoFormErrors()
        // Checking that the form state has been reset after submission ($this->form->fill([]))
        ->assertSchemaStateSet(['title' => null, 'message' => null]);

    // Check that the notification was sent to the target user
    expect($targetUser->notifications()->count())->toBe(1)
    // Check that admin has no notifications (admin sends it)
        ->and($admin->notifications()->count())->toBe(0);

    // Check that the notification JSON data is correct
    $notificationData = $targetUser->notifications()->first()->data;
    expect($notificationData['title'])->toBe('Test notification')
        ->and($notificationData['body'])->toBe('Test notification to a single user')
        ->and($notificationData['status'])->toBe('info')
        ->and($notificationData['icon'])->toBe('heroicon-o-user');
});

test('successfully sends notifications to a specific role (author)', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN
    ]);
    $author1 = User::factory()->create(['role' => UserRole::AUTHOR]);
    $author2 = User::factory()->create(['role' => UserRole::AUTHOR]);
    $moderator = User::factory()->create(['role' => UserRole::MODERATOR]);
    $this->actingAs($admin);

    livewire(SendNotification::class)
        ->fillForm([
            'target' => 'author',
            'title' => 'For authors',
            'message' => 'Text for authors',
        ])
        ->call('send')
        ->assertHasNoFormErrors();

    // Check that the notification was sent to the target users (authors)...
    expect($author1->notifications()->count())->toBe(1)
        ->and($author2->notifications()->count())->toBe(1)
    // ... and have not sent to the moderator
        ->and($moderator->notifications()->count())->toBe(0);
});

test('successfully sends notifications to all users', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN
    ]);
    $moderator = User::factory()->create(['role' => UserRole::MODERATOR]);
    $authors = User::factory()->count(2)->create(['role' => UserRole::AUTHOR]);
    $users = User::factory()->count(5)->create();
    $this->actingAs($admin);

    livewire(SendNotification::class)
        ->fillForm([
            'target' => 'all',
            'title' => 'General announcement',
            'message' => 'General announcement text.',
        ])
        ->call('send')
        ->assertHasNoFormErrors();

    // users in the DB: 1 (admin) + 1 (moderator) + 2 (authors) + 5 (users) = 9
    expect(\Illuminate\Support\Facades\DB::table('notifications')->count())->toBe(9);
});






