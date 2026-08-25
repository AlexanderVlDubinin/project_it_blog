<?php

use App\Enum\UserRole;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Tags\Pages\CreateTag;
use App\Filament\Resources\Tags\TagResource;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
//use Filament\Actions\Testing\TestAction;
use Filament\Forms\Components\Select;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

// ADMIN PANEL TESTS
test('admin can visit admin panel pages', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN
    ]);
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $tag = Tag::factory()->create();
    // Disable the internal Authenticate from Filament, as there is a custom Middleware
    $this->withoutMiddleware(Authenticate::class);

    $sidePanelArray = [
        '<span', 'class="fi-sidebar-item-label"', 'Dashboard', '</span>',
        '<span', 'class="fi-sidebar-item-label"', 'Go to the website', '</span>',
        '<span', 'class="fi-sidebar-item-label"', 'Sending notifications', '</span>',
        '<span', 'class="fi-sidebar-item-label"', 'Users', '</span>',
        '<span', 'class="fi-sidebar-item-label"', 'Posts', '</span>',
        '<span', 'class="fi-sidebar-item-label"', 'Tags', '</span>',
    ];

    // Dashboard
    $this->actingAs($admin)->get('/admin')
        ->assertSuccessful()
        ->assertSeeInOrder($sidePanelArray, false);

    // Sending notifications
    $this->actingAs($admin)->get('/admin/send-notification')
        ->assertSuccessful()
        ->assertSee('Send a notification to users');

    // Users
    $this->actingAs($admin)->get('/admin/users') // Users main
    ->assertSuccessful()
        ->assertSee('Users');
    $this->actingAs($admin)->get('/admin/users/create') // Users create
    ->assertSuccessful()
        ->assertSee('Create User');
    $this->actingAs($admin)->get('/admin/users/'.$admin->id.'/edit') // Users edit
    ->assertSuccessful()
        ->assertSee('Edit ' . $admin->name);

    // Posts
    $this->actingAs($admin)->get('/admin/posts') // Posts main
    ->assertSuccessful()
        ->assertSee('Posts');
    $this->actingAs($admin)->get('/admin/posts/create') // Posts create
    ->assertSuccessful()
        ->assertSee('Create Post');
    $this->actingAs($admin)->get('/admin/posts/'.$post->id.'/edit') // Posts edit
    ->assertSuccessful()
        ->assertSee('Edit ' . $post->title)
        ->assertSee('Comments');

    // Tags
    $this->actingAs($admin)->get('/admin/tags') // Tags main
    ->assertSuccessful()
        ->assertSee('Tags');
    $this->actingAs($admin)->get('/admin/tags/create') // Tags create
    ->assertSuccessful()
        ->assertSee('Create Tag');
    $this->actingAs($admin)->get('/admin/tags/'.$tag->id.'/edit') // Tags edit
    ->assertSuccessful()
        ->assertSee('Edit Tag');
});

test('moderator can visit admin panel pages', function () {
    $moderator = User::factory()->create([
        'role' => UserRole::MODERATOR
    ]);
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $tag = Tag::factory()->create();
    // Disable the internal Authenticate from Filament, as there is a custom Middleware
    $this->withoutMiddleware(Authenticate::class);

    $sidePanelArray = [
        '<span', 'class="fi-sidebar-item-label"', 'Dashboard', '</span>',
        '<span', 'class="fi-sidebar-item-label"', 'Go to the website', '</span>',
        '<span', 'class="fi-sidebar-item-label"', 'Sending notifications', '</span>',
        '<span', 'class="fi-sidebar-item-label"', 'Users', '</span>',
        '<span', 'class="fi-sidebar-item-label"', 'Posts', '</span>',
        '<span', 'class="fi-sidebar-item-label"', 'Tags', '</span>',
    ];

    // Dashboard
    $this->actingAs($moderator)->get('/admin')
        ->assertSuccessful()
        ->assertSeeInOrder($sidePanelArray, false);

    // Sending notifications
    $this->actingAs($moderator)->get('/admin/send-notification')
        ->assertSuccessful()
        ->assertSee('Send a notification to users');

    // Users
    $this->actingAs($moderator)->get('/admin/users') // Users main
    ->assertSuccessful()
        ->assertSee('Users');
    $this->actingAs($moderator)->get('/admin/users/create') // Users create
    ->assertSuccessful()
        ->assertSee('Create User');
    $this->actingAs($moderator)->get('/admin/users/'.$moderator->id.'/edit') // Users edit
    ->assertSuccessful()
        ->assertSee('Edit ' . $moderator->name);

    // Posts
    $this->actingAs($moderator)->get('/admin/posts') // Posts main
    ->assertSuccessful()
        ->assertSee('Posts');
    $this->actingAs($moderator)->get('/admin/posts/create') // Posts create
    ->assertSuccessful()
        ->assertSee('Create Post');
    $this->actingAs($moderator)->get('/admin/posts/'.$post->id.'/edit') // Posts edit
    ->assertSuccessful()
        ->assertSee('Edit ' . $post->title)
        ->assertSee('Comments');

    // Tags
    $this->actingAs($moderator)->get('/admin/tags') // Tags main
    ->assertSuccessful()
        ->assertSee('Tags');
    $this->actingAs($moderator)->get('/admin/tags/create') // Tags create
    ->assertSuccessful()
        ->assertSee('Create Tag');
    $this->actingAs($moderator)->get('/admin/tags/'.$tag->id.'/edit') // Tags edit
    ->assertSuccessful()
        ->assertSee('Edit Tag');
});

test('non-admin-moderator user is redirected to dashboard', function () {
    $user = User::factory()->create();
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $this->withoutMiddleware(Authenticate::class);

    $this->actingAs($user)
        ->get('/admin/send-notification')
        ->assertRedirect('/dashboard');
    $this->actingAs($author)
        ->get('/admin/send-notification')
        ->assertRedirect('/dashboard');

    $this->actingAs($user)
        ->get('/admin/users')
        ->assertRedirect('/dashboard');
    $this->actingAs($author)
        ->get('/admin/users')
        ->assertRedirect('/dashboard');

    $this->actingAs($user)
        ->get('/admin/posts')
        ->assertRedirect('/dashboard');
    $this->actingAs($author)
        ->get('/admin/posts')
        ->assertRedirect('/dashboard');

    $this->actingAs($user)
        ->get('/admin/tags')
        ->assertRedirect('/dashboard');
    $this->actingAs($author)
        ->get('/admin/tags')
        ->assertRedirect('/dashboard');
});

// USERS
test('validation errors when create/edit user', function () {
    $user = User::factory()->create();
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN
    ]);
    $this->withoutMiddleware(Authenticate::class);
    $this->actingAs($admin);

    // CREATE
    livewire(CreateUser::class)
        ->call('create') // trying to send empty form
        ->assertHasFormErrors([
            'name' => 'required',
            'email' => 'required',
            'password' => 'required',
        ]);

    livewire(CreateUser::class)
        ->fillForm([
            'name' => 'Jo',  // validation error
            'email' => 'UPPERCASE@EMAIL.COM', // validation error
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'user',
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name' => 'min:3',
            'email' => 'lowercase',
        ]);

    livewire(CreateUser::class)
        ->fillForm([
            'name' => 'John Doe',
            'email' => 'correct@email.com',
            'password' => 'Password123!',
            'password_confirmation' => 'WrongPassword', // mismatch
            'role' => 'user',
        ])
        ->call('create')
        ->assertHasFormErrors([
            'password' => 'confirmed',
        ]);

    // EDIT
    livewire(EditUser::class, ['record' => $user->getKey()])
        ->fillForm([
            'name' => 'Updated Name',
            'email' => 'updated@email.com',
            'password' => '', // empty field allowed when editing
            'role' => 'user',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    livewire(EditUser::class, ['record' => $user->getKey()])
        ->fillForm([
            'password' => 'NewPassword123!', // not empty
            'password_confirmation' => '', // confirmation required !!!
        ])
        ->call('save')
        ->assertHasFormErrors(['password_confirmation' => 'required']);
});

test('hides the admin role from the list of options for moderators', function () {
    $moderator = User::factory()->create([
        'role' => UserRole::MODERATOR
    ]);
    $this->actingAs($moderator);

    livewire(CreateUser::class)
        ->assertFormFieldExists('role', function (Select $field) {
            // all user roles array
            $options = $field->getOptions();

            // conditions: moderator should not have admin
            expect($options)
                ->toHaveKeys(['user', 'author', 'moderator'])
                ->not->toHaveKey('admin'); // no admin

            return true;
        });
});

test('shows all roles for admins', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN
    ]);
    $this->actingAs($admin);

    livewire(CreateUser::class)
        ->assertFormFieldExists('role', function (Select $field) {
            $options = $field->getOptions();

            // conditions: admin should have all roles
            expect($options)->toHaveKeys(['user', 'author', 'moderator', 'admin']);

            return true;
        });
});

// POSTS
test('validation errors when create/edit post', function () {
    $user = User::factory()->create();
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN
    ]);
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $pdfFile = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');
    $largeImage = UploadedFile::fake()->image('big-photo.jpg')->size(3000);
    $validImage = UploadedFile::fake()->image('avatar.png')->size(500);
    $this->withoutMiddleware(Authenticate::class);
    $this->actingAs($admin);

    // CREATE
    livewire(CreatePost::class)
        ->call('create') // trying to send empty form
        ->assertHasFormErrors([
            'title' => 'required',
            'content' => 'required',
            'user_id' => 'required',
        ]);

    livewire(CreatePost::class)
        ->fillForm([
            'title' => 'Jo',  // validation error (less than 3 characters)
            'content' => 'qwerty asdfgh', // validation error (less than 20 characters)
            'is_published' => true,
            'image' => $pdfFile, // validation error (wrong file type)
            'user_id' => 'qwerty', // validation error (user does not exist)
        ])
        ->call('create')
        ->assertHasFormErrors([
            'title' => 'min:3',
            'content' => 'min:20',
            'image',
            'user_id',
        ]);

    livewire(CreatePost::class)
        ->fillForm([
            'image' => $largeImage, // validation error (too large image)
        ])
        ->call('create')
        ->assertHasFormErrors([
            'image',
        ]);

    livewire(CreatePost::class)
        ->fillForm([
            'title' => 'Valid Post Title',
            'content' => 'qwerty qwerty qwerty qwerty qwerty (Valid Post Text)',
            'is_published' => true,
            'image' => $validImage,
            'user_id' => $user->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    // Checking that the file has been physically saved to the specified directory ->directory('posts')
    // Filament generates a random name, so check the existence of files in the folder
    $files = Storage::disk('public')->files('posts');
    expect($files)->not->toBeEmpty();
});

test('can create a post with tags and image', function () {
    Storage::fake('public');

    // create author for post
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    // create tags for post
    $tags = Tag::factory()->count(3)->create();
    $tagIds = $tags->pluck('id')->toArray();
    // create image for post
    $validImage = UploadedFile::fake()->image('avatar.png')->size(500);

    $admin = User::factory()->create([
        'role' => UserRole::ADMIN
    ]);
    $this->actingAs($admin); // login as admin
    $this->get(PostResource::getUrl('create')); // open create page

    expect($tags)->toHaveCount(3);

    // test create post
    livewire(CreatePost::class)
        ->fillForm([
            'title' => 'New Amazing Post Title',
            'content' => 'This is a long content that satisfies the minimum 20 characters length rule.',
            'is_published' => true,
            'image' => $validImage,
            'user_id' => $author->id,
            'tags' => $tagIds, // array of tag IDs
        ])
        ->call('create') // call create action
        ->assertHasNoFormErrors(); // check for no errors

    // check post in database
    $this->assertDatabaseHas('posts', [
        'title' => 'New Amazing Post Title',
        'is_published' => true,
        'user_id' => $author->id,
    ]);

    // check the relationship with the tags via the pivot table
    $post = Post::query()->latest()->first();
    expect($post->tags)->toHaveCount(3)
        ->and($post->tags->pluck('id')->toArray())->toEqual($tagIds);

    // check image saved on disk
    Storage::disk('public')->assertExists($post->image);
});

test('validates the number of the selected tags when creating post', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN
    ]);
    $this->actingAs($admin);

    $tags = Tag::factory()->count(8)->create();
    $tagIds = $tags->pluck('id')->toArray();

    livewire(CreatePost::class)
        ->fillForm([
            'tags' => $tagIds, // send 8 tags
        ])
        ->call('create')
        ->assertHasFormErrors(['tags' => 'max'])
        // check error message
        ->assertSee('You cannot select more than 7 tags for one post.');
});

test('can create a new tag on the fly when creating post via the form inside select', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN
    ]);
    $this->actingAs($admin);
    $this->get(PostResource::getUrl('create'));

    // 1. Check that the tag does not exist
    $this->assertDatabaseMissing('tags', [
        'name' => 'Laravel13',
    ]);

    // 2. Test create tag when creating a new post
    livewire(CreatePost::class)
        // Mount action. First argument - exact field name from the schema, second - action name
        ->mountFormComponentAction('tags', 'createOption')
        // Fill the modal window data
        ->setFormComponentActionData([
            'name' => 'Laravel13',
        ])
        // Execute the action
        ->callMountedFormComponentAction();

//    livewire(CreatePost::class)
//        ->mountAction(
//            TestAction::make('createOption')
//                ->schemaComponent('data.tags')
//        )
//        ->fillForm([
//            'name' => 'Laravel13',
//        ], 'mountedActionForm') // mountedActionForm not working here ????
//        ->callMountedAction();


    // 3. Check that the tag was created
    $this->assertDatabaseHas('tags', [
        'name' => 'Laravel13',
    ]);
});

// TAGS
test('testing validate rules for tags when creating post', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN
    ]);
    $this->actingAs($admin);
    $this->get(TagResource::getUrl('create'));

    // CREATE
    livewire(CreateTag::class)
        ->call('create') // trying to send empty form
        ->assertHasFormErrors([
            'name' => 'required',
        ]);

    livewire(CreateUser::class)
        ->fillForm([
            'name' => 1,  // validation error
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name' => 'string',
        ]);

    livewire(CreateUser::class)
        ->fillForm([
            'name' => 'a',  // validation error
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name' => 'min:2',
        ]);

    $longName = str_repeat('qwerty', 10);
    livewire(CreateUser::class)
        ->fillForm([
            'name' => $longName,  // validation error
        ])
        ->call('create')
//        ->assertHasFormErrors([
//            'name'                // errors
//        ])
    ;
    $this->assertDatabaseMissing('tags', [
        'name' => $longName,
    ]);

    livewire(CreateTag::class)
        ->fillForm([
            'name' => 'Invalid#Tag!', // contains special characters # and ! - invalid
        ])
        ->call('create')
        // check not_regex error for name
        ->assertHasFormErrors(['name'])
        // check custom message
        ->assertSee('The tag name must contain only letters, numbers, and spaces.');

    $this->assertDatabaseMissing('tags', [
        'name' => 'Invalid#Tag!',
    ]);
});

test('successful tag creation', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN
    ]);
    $this->actingAs($admin);
    $this->get(TagResource::getUrl('create'));

    livewire(CreateTag::class)
        ->fillForm([
            'name' => 'Laravel 13', // letters, numbers, and spaces - valid
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('tags', [
        'name' => 'Laravel 13',
    ]);
});


