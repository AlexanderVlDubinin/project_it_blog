<?php

use App\Enum\UserRole;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('dynamic pagination («Show more» button) loads data correctly', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN
    ]);
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    // imported posts (imported from external sources)
    $importedPosts = Post::factory()->count(10)->create([
        'is_published' => true,
        'user_id' => $author->id,
        'source_type' => 'https://techcrunch.com'
    ]);
    $this->actingAs($admin);

    visit(route('dashboard'))
        ->assertPresent('h2.dashboard-page-title') // dashboard page checks start
        ->assertSee('Dashboard')
        ->assertSee('Latest registered users')
        ->assertSee('Last imported posts') // dashboard page checks end
        ->assertCount('.recent-imported-posts-tr', 6) // initial number of imported posts
        ->assertSee('Total: 10') // total number of imported posts
        ->assertSee('Show more') // show more button
        ->click('@recent-imported-posts-show-more-btn') // click on show more button
        ->assertCount('.recent-imported-posts-tr', 10) // final number of imported posts
        ->assertDontSee('Total:') // no total posts more because all posts are shown
        ->assertDontSee('Show more') // no show more button because all posts are shown
    ;
});

test('clicking on «Moderate» button (for users) redirects to user edit page in admin panel', function () {
    $admin = User::factory()->create([
        'password' => 'password',
        'role' => UserRole::ADMIN
    ]);
    $users = User::factory()->count(10)->create();
    $targetUser = $users[5];

//    $this->actingAs($admin);
//    visit(route('dashboard'))
//        ->assertPresent('h2.dashboard-page-title')
//        ->assertSee('Dashboard')
//        ->assertSee('Latest registered users')
//        ->assertSee('Last imported posts')
//        ->assertCount('.latest-registered-users-tr', 10)
//        ->click('@latest-registered-users-moderate-user-' . $users[5]->id . '-btn')
//        ->wait(1)
//        ->assertPathIs('/admin/users/' . $users[5]->id . '/edit')
//        ->assertSee('Edit ' . $users[5]->name)
//    ;


    // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    // IN CASE OF PROBLEMS WITH GOING TO FILAMENT WITH A LINK/BUTTON WITH target="_blank"
    // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!


    // Authorization
    visit('/admin/login') // path to the Filename login
        ->fill('[id="form.email"]', $admin->email)
        ->fill('[id="form.password"]', 'password')
        ->click('button[type="submit"]'); // Click on the Login button

    // Switching to the Dashboard
    $page = visit(route('dashboard'))->wait(1);

    // Check if the Dashboard page is loaded + actions on the Dashboard page
    $page
        ->assertPresent('h2.dashboard-page-title')
        ->assertSee('Dashboard')
        ->assertSee('Latest registered users')
        ->assertSee('Last imported posts')
        ->assertCount('.latest-registered-users-tr', 10);

    // The selector of the "Moderate" button
    $selector = '[data-test="latest-registered-users-moderate-user-' . $targetUser->id . '-btn"]';
    // Calling evaluate() directly from the native Playwright object of the page
    // JavaScript trick: remove target="_blank" from a specific button before clicking
    // The evaluate() method executes native JS in the context of the current page
    $page->page()->evaluate("selector => {
        const link = document.querySelector(selector);
        if (link) {
            link.removeAttribute('target');
        }
    }", $selector);

    // Click on the button — now it opens in the same tab
    $page->click('@latest-registered-users-moderate-user-' . $targetUser->id . '-btn')
        ->wait(1);

    // Final checks
    $page->assertPathIs('/admin/users/' . $targetUser->id . '/edit')
        ->assertSee('Edit ' . $targetUser->name);
});

test('clicking on «Moderate» button (for posts) redirects to post edit page in admin panel', function () {
    $admin = User::factory()->create([
        'password' => 'password',
        'role' => UserRole::ADMIN
    ]);
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $importedPosts = Post::factory()->count(3)->create([
        'is_published' => true,
        'user_id' => $author->id,
        'source_type' => 'https://techcrunch.com'
    ]);
    $targetPost = $importedPosts[1];

    // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    // IN CASE OF PROBLEMS WITH GOING TO FILAMENT WITH A LINK/BUTTON WITH target="_blank"
    // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

    visit('/admin/login')
        ->fill('[id="form.email"]', $admin->email)
        ->fill('[id="form.password"]', 'password')
        ->click('button[type="submit"]');

    $page = visit(route('dashboard'))->wait(1);
    $page
        ->assertPresent('h2.dashboard-page-title')
        ->assertSee('Dashboard')
        ->assertSee('Latest registered users')
        ->assertSee('Last imported posts')
        ->assertCount('.recent-imported-posts-tr', 3);

    $selector = '[data-test="recent-imported-posts-moderate-post-' . $targetPost->id . '-btn"]';
    $page->page()->evaluate("selector => {
        const link = document.querySelector(selector);
        if (link) {
            link.removeAttribute('target');
        }
    }", $selector);

    $page->click('@recent-imported-posts-moderate-post-' . $targetPost->id . '-btn')
        ->wait(1);

    $page->assertPathIs('/admin/posts/' . $targetPost->id . '/edit')
        ->assertSee('Edit ' . $targetPost->title);
});

test('clicking on «Open» button (for comments) redirects to post page with anchor to comment', function () {
    $moderator = User::factory()->create([
        'password' => 'password',
        'role' => UserRole::MODERATOR
    ]);
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $commentUser = User::factory()->create();
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $comments = Comment::factory()->count(7)->create([
        'post_id' => $post->id,
        'user_id' => $commentUser->id,
        'is_deleted' => true,
    ]);

    $this->actingAs($moderator);

    // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    // IN CASE OF PROBLEMS WITH GOING TO ANOTHER TAB WITH A LINK/BUTTON WITH target="_blank"
    // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

    $page = visit(route('dashboard'))->wait(1);
    $page->assertPresent('h2.dashboard-page-title')
        ->assertSee('Dashboard')
        ->assertSee('Soft deleted posts')
        ->assertSee('Soft deleted comments')
        ->assertSee('Low-rated comments')
        ->assertCount('.recent-soft-deleted-comments-tr', 5);

    $selector = '[data-test="recent-soft-deleted-comments-open-post-' . $comments[4]->id . '-btn"]';
    $page->page()->evaluate("selector => {
        const link = document.querySelector(selector);
        if (link) {
            link.removeAttribute('target');
        }
    }", $selector);

    $page->click('@recent-soft-deleted-comments-open-post-' . $comments[4]->id . '-btn')
        ->wait(1);

    $page->assertPathIs('/posts/' . $post->id)
        ->assertFragmentIs('comment-' . $comments[4]->id) // Checking the anchor separately
        ->assertSee($post->title)
        ->assertSee($commentUser->name)
        ->assertSee($comments[4]->display_body);
});


