<?php

use App\Enum\UserRole;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('requires authentication to view dashboard page', function () {
    $this->get('/dashboard')->assertRedirect(route('login'));
});

test('user and author cannot see the moderator or admin tables on the dashboard page', function () {
    $user = User::factory()->create();
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertStatus(200)
        ->assertViewIs('dashboard')
        ->assertSee('Personal activity statistics') // user stats
        ->assertSee('Recommended for you') // user stats
        ->assertDontSee('Soft deleted posts') // moderator stats
        ->assertDontSee('Soft deleted comments') // moderator stats
        ->assertDontSee('Low-rated comments') // moderator stats
        ->assertDontSee('Latest registered users') // admin stats
        ->assertDontSee('Last imported posts'); // admin stats

    $this->actingAs($author);

    $response = $this->get(route('dashboard'));
    $response->assertStatus(200)
        ->assertViewIs('dashboard')
        ->assertSee('Personal activity statistics') // author stats
        ->assertSee('Recommended for you') // author stats
        ->assertSee('Posts you created') // author stats
        ->assertDontSee('Soft deleted posts') // moderator stats
        ->assertDontSee('Soft deleted comments') // moderator stats
        ->assertDontSee('Low-rated comments') // moderator stats
        ->assertDontSee('Latest registered users') // admin stats
        ->assertDontSee('Last imported posts'); // admin stats
});

test('moderator cannot see the admin tables on the dashboard page', function () {
    $moderator = User::factory()->create([
        'role' => UserRole::MODERATOR
    ]);
    $this->actingAs($moderator);

    $response = $this->get(route('dashboard'));
    $response->assertStatus(200)
        ->assertViewIs('dashboard')
        ->assertSee('Soft deleted posts') // moderator stats
        ->assertSee('Soft deleted comments') // moderator stats
        ->assertSee('Low-rated comments') // moderator stats
        ->assertDontSee('Latest registered users') // admin stats
        ->assertDontSee('Last imported posts'); // admin stats
});

test('author can see only their own posts in table on the dashboard page', function () {
    $author1 = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $author2 = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post1 = Post::factory()->create([ // author1's post
        'title' => 'Test Post 1',
        'is_published' => true,
        'user_id' => $author1->id,
    ]);
    $post2 = Post::factory()->create([ // author2's post
        'title' => 'Test Post 2',
        'is_published' => true,
        'user_id' => $author2->id,
    ]);

    // users who liked author2's another posts
    $usersLiked = User::factory()->count(2)->create();

    // posts for likes - for "Recommended for you" table
    // This is necessary so that $post2 does not appear in this table.
    $posts = Post::factory()->count(5)->create([
        'is_published' => true,
        'user_id' => $author2->id,
    ]);
    foreach ($usersLiked as $userLiked) {
        foreach ($posts as $post) {
            Like::factory()->forPost($post)->create([
                'user_id' => $userLiked->id,
                'is_like' => true,
            ]);
        }
    }
    $this->actingAs($author1);

    $response = $this->get(route('dashboard'));

    $author1Array = [
        'Posts you created',
        $post1->title
    ];

    $response->assertStatus(200)
        ->assertViewIs('dashboard')
        ->assertSeeInOrder($author1Array, false) // author can see his own post among his posts
        ->assertDontSee('Test Post 2'); // author can not see someone else's post among his posts
});

test('user can see details of his personal activity statistics in the correct form', function () {
    $user = User::factory()->create();
    $commentUser = User::factory()->create();
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $posts = Post::factory()->count(3)->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    foreach ($posts as $post) {
        // 3 comments for each post - 9 comments in total
        Comment::factory()->count(3)->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'is_deleted' => false
        ]);

        // 1 like for each post - 3 likes for posts in total
        Like::factory()->forPost($post)->create([
            'user_id' => $user->id,
            'is_like' => true,
        ]);
    }
    // 7 comments for the first post - 7 comments in total
    $comments = Comment::factory()->count(7)->create([
        'post_id' => $posts[0]->id,
        'user_id' => $commentUser->id,
        'is_deleted' => false
    ]);
    // likes and dislikes for comments
    foreach ($comments as $index => $comment) {
        Like::factory()->forComment($comment)->create([
            'user_id' => $user->id,
            'is_like' => $index > 1, // first 2 comments are disliked the rest (5) are liked
        ]);
    }
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));

    // array for testing user personal activity statistics
    $userPersonalActivityStatisticsArray = [
        'Personal activity statistics',
        '<span>', 'Posts liked', '</span>', '3',
        '<span>', 'Comments left', '</span>', '9',
        '<span>', 'Received likes for comments', '</span>', '<span class="text-green-600">', '5', '</span>',
        '<span>', 'Received dislikes for comments', '</span>', '<span class="text-red-600">', '2', '</span>',
        '<span>', 'Total rating', '</span>', '<span class="text-green-600">', '3', '</span>',
        'Recommended for you',
    ];

    $response->assertStatus(200)
        ->assertViewIs('dashboard')
        ->assertSeeInOrder($userPersonalActivityStatisticsArray, false);
});

test('user can see correct details of recommendation table in the correct form', function () {
    $user = User::factory()->create();
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    // users who liked author's posts (2 of them will not be seen in the table)
    $usersReactedToPosts = User::factory()->count(7)->create();
    // posts for likes - for "Recommended for you" table
    $posts = Post::factory()->count(7)->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    // likes for posts
    foreach ($posts as $index => $post) {
        for ($i = 0; $i < $index + 1; $i++) {
            Like::factory()->forPost($post)->create([
                'user_id' => $usersReactedToPosts[$i]->id,
                'is_like' => true,
            ]);
        }
    }
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));

    // array for testing user recommendation table
    $userRecommendationTableArray = [
        'Recommended for you',
        '<tr class="border-b border-t border-gray-600',
            '<td', $posts[6]->title, '</td>',
            '<td', '<span', 'text-red-200', '<svg ', '7', '</svg>', '</span>', '</td>',
        '</tr>',
        '<tr class="border-b border-t border-gray-600',
            '<td', $posts[5]->title, '</td>',
            '<td', '<span', 'text-red-200', '<svg ', '6', '</svg>', '</span>', '</td>',
        '</tr>',
        '<tr class="border-b border-t border-gray-600',
            '<td', $posts[4]->title, '</td>',
            '<td', '<span', 'text-red-200', '<svg ', '5', '</svg>', '</span>', '</td>',
        '</tr>',
        '<tr class="border-b border-t border-gray-600',
            '<td', $posts[3]->title, '</td>',
            '<td', '<span', 'text-red-200', '<svg ', '4', '</svg>', '</span>', '</td>',
        '</tr>',
        '<tr class="border-b border-t border-gray-600',
            '<td', $posts[2]->title, '</td>',
            '<td', '<span', 'text-red-200', '<svg ', '3', '</svg>', '</span>', '</td>',
        '</tr>',
    ];

    $response->assertStatus(200)
        ->assertViewIs('dashboard')
        ->assertSeeInOrder($userRecommendationTableArray, false)
        // 2 posts that will not be seen in the table
        ->assertDontSee($posts[1]->title)
        ->assertDontSee($posts[0]->title);
});

test('moderator can see soft-deleted posts in the correct form', function () {
    $moderator = User::factory()->create([
        'role' => UserRole::MODERATOR
    ]);
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    // posts that not deleted
    $postsOk = Post::factory()->count(3)->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    // soft-deleted posts
    $softDeletedPosts = Post::factory()->count(3)->create([
        'is_published' => true,
        'user_id' => $author->id,
        'created_at' => now()->subDays(2),
        'deleted_at' => now()->subDays(2),
    ]);
    $this->actingAs($moderator);

    $response = $this->get(route('dashboard'));

    $softDeletedPostsArray = [
        'Soft deleted posts',
        $softDeletedPosts[0]->title,
        $softDeletedPosts[1]->title,
        $softDeletedPosts[2]->title,
    ];

    $response->assertStatus(200)
        ->assertViewIs('dashboard')
        ->assertSeeInOrder($softDeletedPostsArray, false) // can see soft-deleted posts
        // 3 posts (not deleted) that will not be seen in the table
        ->assertDontSee($postsOk[0]->title)
        ->assertDontSee($postsOk[1]->title)
        ->assertDontSee($postsOk[2]->title);
});

test('moderator can see soft-deleted comments in the correct form', function () {
    $moderator = User::factory()->create([
        'role' => UserRole::MODERATOR
    ]);
    $commentUser = User::factory()->create();
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    // post for comments
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    // comments that not deleted
    $commentsOk = Comment::factory()->count(3)->create([
        'post_id' => $post->id,
        'user_id' => $commentUser->id,
        'is_deleted' => false
    ]);
    // soft-deleted comments
    $softDeletedComments = Comment::factory()->count(3)->create([
        'post_id' => $post->id,
        'user_id' => $commentUser->id,
        'is_deleted' => true
    ]);
    $this->actingAs($moderator);

    $response = $this->get(route('dashboard'));

    $softDeletedCommentsArray = [
        'Soft deleted comments:',
        Str::words($softDeletedComments[0]->body, 10),
        $commentUser->name,
        Str::words($softDeletedComments[1]->body, 10),
        $commentUser->name,
        Str::words($softDeletedComments[2]->body, 10),
        $commentUser->name,
    ];

    $response->assertStatus(200)
        ->assertViewIs('dashboard')
        ->assertSeeInOrder($softDeletedCommentsArray, false) // can see soft-deleted comments
        // 3 comments (not deleted) that will not be seen in the table
        ->assertDontSee(Str::words($commentsOk[0]->body, 10))
        ->assertDontSee(Str::words($commentsOk[1]->body, 10))
        ->assertDontSee(Str::words($commentsOk[2]->body, 10));
});

test('moderator can see low-rated comments in the correct form', function () {
    $moderator = User::factory()->create([
        'role' => UserRole::MODERATOR
    ]);
    $commentUser = User::factory()->create();
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    // users that reacted to comments
    $usersReactedToComments = User::factory()->count(8)->create();
    // post for comments
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    // comments for likes and dislikes
    $comments = Comment::factory()->count(6)->create([
        'post_id' => $post->id,
        'user_id' => $commentUser->id,
        'is_deleted' => false
    ]);
    // adding likes and dislikes to comments
    foreach ($comments as $index => $comment) {
        // likes
        if ($index < 3) {
            Like::factory()->forComment($comment)->create([
                'user_id' => $usersReactedToComments[$index]->id,
                'is_like' => true,
            ]);
        }
        // dislikes
        else {
            for ($i = 0; $i < $index + 3; $i++) {
                Like::factory()->forComment($comment)->create([
                    'user_id' => $usersReactedToComments[$i]->id,
                    'is_like' => false,
                ]);
            }
        }
    }
    $this->actingAs($moderator);

    $response = $this->get(route('dashboard'));

    $lowRatedCommentsArray = [
        'Low-rated comments:',
        Str::words($comments[5]->body, 10),
        '<span class="text-red-500">', '-8', '</span>',
        $commentUser->name,
        Str::words($comments[4]->body, 10),
        '<span class="text-red-500">', '-7', '</span>',
        $commentUser->name,
        Str::words($comments[3]->body, 10),
        '<span class="text-red-500">', '-6', '</span>',
        $commentUser->name,
    ];

    $response->assertStatus(200)
        ->assertViewIs('dashboard')
        ->assertSeeInOrder($lowRatedCommentsArray, false) // can see low-rated comments
        // 3 comments (not low-rated, with likes) that will not be seen in the table
        ->assertDontSee(Str::words($comments[0]->body, 10))
        ->assertDontSee(Str::words($comments[1]->body, 10))
        ->assertDontSee(Str::words($comments[2]->body, 10));
});

test('admin can see last registered users in the correct form', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN
    ]);
    // create latest registered users
    $latestRegisteredUsers = [];
    for ($i = 20; $i >= 1; $i--) {
        $latestRegisteredUsers[$i] = User::factory()->create(['created_at' => now()->subHours($i*6)]);
    }
    $this->actingAs($admin);

    $response = $this->get(route('dashboard'));

    $latestRegisteredUsersArray = [
        'Latest registered users:',
        $latestRegisteredUsers[1]->email, now()->subHours(6)->format('d.m.Y H:i'),
        $latestRegisteredUsers[2]->email, now()->subHours(12)->format('d.m.Y H:i'),
        $latestRegisteredUsers[3]->email, now()->subHours(18)->format('d.m.Y H:i'),
        $latestRegisteredUsers[4]->email, now()->subHours(24)->format('d.m.Y H:i'),
        $latestRegisteredUsers[5]->email, now()->subHours(30)->format('d.m.Y H:i'),
        $latestRegisteredUsers[6]->email, now()->subHours(36)->format('d.m.Y H:i'),
        $latestRegisteredUsers[7]->email, now()->subHours(42)->format('d.m.Y H:i'),
        $latestRegisteredUsers[8]->email, now()->subHours(48)->format('d.m.Y H:i'),
        $latestRegisteredUsers[9]->email, now()->subHours(54)->format('d.m.Y H:i'),
        $latestRegisteredUsers[10]->email, now()->subHours(60)->format('d.m.Y H:i'),
        'Show more',
        'Last imported posts',
    ];

    $response->assertStatus(200)
        ->assertViewIs('dashboard')
        ->assertSeeInOrder($latestRegisteredUsersArray, false) // can see latest registered users
        // 3 users that will not be seen in the table because (need clicking "Show more" button to see)
        ->assertDontSee($latestRegisteredUsers[11]->email)
        ->assertDontSee($latestRegisteredUsers[12]->email)
        ->assertDontSee($latestRegisteredUsers[13]->email);
});

test('admin can see imported posts in the correct form', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN
    ]);
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    // usual posts (created by author manually)
    $userPosts = Post::factory()->count(3)->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    // imported posts (imported from external sources)
    $importedPosts = [];
    for ($i = 3; $i >= 1; $i--) {
        $importedPosts[$i] = Post::factory()->create([
            'is_published' => true,
            'user_id' => $author->id,
            'created_at' => now()->subDays($i),
            'source_type' => 'https://techcrunch.com'
        ]);
    }
    $this->actingAs($admin);

    $response = $this->get(route('dashboard'));

    $importedPostsArray = [
        'Last imported posts:',
        $importedPosts[1]->title,
        'https://techcrunch.com',
        $importedPosts[2]->title,
        'https://techcrunch.com',
        $importedPosts[3]->title,
        'https://techcrunch.com',
    ];

    $response->assertStatus(200)
        ->assertViewIs('dashboard')
        ->assertSeeInOrder($importedPostsArray, false) // can see imported posts
        // can not see usual posts
        ->assertDontSee($userPosts[0]->title)
        ->assertDontSee($userPosts[1]->title)
        ->assertDontSee($userPosts[2]->title);
});



