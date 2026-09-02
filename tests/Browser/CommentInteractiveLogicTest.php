<?php

use App\Enum\UserRole;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Comment reply button testing.
test('reply to post comment', function () {
    $user = User::factory()->create();
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $commentAuthor = User::factory()->create();
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $comment = Comment::factory()->create([
        'post_id' => $post->id,
        'user_id' => $commentAuthor->id,
        'is_deleted' => false
    ]);
    $this->actingAs($user);

    visit(route('posts.show', $post))
        ->assertSee($author->name)
        ->assertSeeIn('[data-test="comment-base-edit-indicator"]', 'Please comment on this post')
        ->assertDontSee('You reply to the user:')
        ->assertMissing('[data-test="reply-comment-author-name"].hidden')
        ->assertSeeIn('[data-test="submit-comment-btn"]', 'Send comment')
        ->assertDontSee('Cancel')
        ->assertMissing('[data-test="cancel-reply-edit-btn"].hidden')
        ->assertSee($commentAuthor->name)
        ->assertSeeIn('[data-test="btn-reply-comment-'.$comment->id.'"]', 'Reply')
        ->assertSee($comment->body)
        // Reply to comment - checking focus on textarea, indicator and author name, reply button, cancel button
        ->click('@btn-reply-comment-' . $comment->id)
        ->assertScript('document.activeElement.matches("textarea#form_body")', true)
        ->assertSeeIn('[data-test="comment-base-edit-indicator"]', 'Write a reply to the comment')
        ->assertSee('You reply to the user: ' . $commentAuthor->name)
        ->assertSeeIn('[data-test="comment-reply-indicator"]', 'You reply to the user: ' . $commentAuthor->name)
        ->assertSeeIn('[data-test="reply-comment-author-name"]', $commentAuthor->name)
        ->assertSeeIn('[data-test="submit-comment-btn"]', 'Reply')
        ->assertSeeIn('[data-test="cancel-reply-edit-btn"]', 'Cancel')
        ->assertSee('Cancel')
        // Cancel click - no focus on textarea, no reply button (in form), no cancel button
        ->click('@cancel-reply-edit-btn')
        ->assertScript('document.activeElement.matches("textarea#form_body")', false)
        ->assertDontSee('You reply to the user:')
        ->assertMissing('[data-test="reply-comment-author-name"]')
        ->assertSeeIn('[data-test="submit-comment-btn"]', 'Send comment')
        ->assertMissing('[data-test="cancel-reply-edit-btn"]')
        ->assertMissing('Cancel')
        // // Reply to comment again, fill the textarea and submit
        ->click('@btn-reply-comment-' . $comment->id)
        ->type('[data-test="comment-textarea"]', 'This is my child reply comment.')
        ->click('@submit-comment-btn')
        // after submit reply, check route, path, see comment body and check that form state is reset
        ->assertRoute('posts.show', ['post' => $post])
        ->assertPathIs('/posts/' . $post->id)
        ->assertSee('This is my child reply comment.')
        ->assertDontSee('You reply to the user:')
        ->assertSeeIn('[data-test="submit-comment-btn"]', 'Send comment')
    ;

    expect($comment1 = $user->comment()->first()->toArray())->toMatchArray([
        'body' => 'This is my child reply comment.',
        'parent_id' => $comment->id,
        'post_id' => $post->id,
        'user_id' => $user->id,
    ])
        ->and($user->comment()->count())->toBe(1);
});

// Comment edit button testing.
test('user editing his own post comment', function () {
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $commentAuthor = User::factory()->create();
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $comment = Comment::factory()->create([
        'post_id' => $post->id,
        'user_id' => $commentAuthor->id,
        'is_deleted' => false
    ]);
    $this->actingAs($commentAuthor);

    visit(route('posts.show', $post))
        ->assertSee($author->name)
        ->assertSeeIn('[data-test="comment-base-edit-indicator"]', 'Please comment on this post')
        ->assertDontSee('Edit your comment')
        ->assertMissing('[data-test="reply-comment-author-name"].hidden')
        ->assertSeeIn('[data-test="submit-comment-btn"]', 'Send comment')
        ->assertDontSee('Cancel')
        ->assertMissing('[data-test="cancel-reply-edit-btn"].hidden')
        ->assertSee($commentAuthor->name)
        ->assertSeeIn('[data-test="btn-edit-comment-'.$comment->id.'"]', 'Edit')
        ->assertSee($comment->body)
        // Edit comment - checking focus on textarea, textarea filled by comment body, Save changes button, cancel button
        ->click('@btn-edit-comment-' . $comment->id)
        ->assertScript('document.activeElement.matches("textarea#form_body")', true)
        ->assertScript('document.querySelector("textarea#form_body").value', $comment->body)
        ->assertSeeIn('[data-test="comment-base-edit-indicator"]', 'Edit your comment')
        ->assertSeeIn('[data-test="submit-comment-btn"]', 'Save changes')
        ->assertSeeIn('[data-test="cancel-reply-edit-btn"]', 'Cancel')
        // Cancel click - no focus on textarea, empty textarea, no Save changes button, no cancel button
        ->click('@cancel-reply-edit-btn')
        ->assertScript('document.activeElement.matches("textarea#form_body")', false)
        ->assertScript('document.querySelector("textarea#form_body").value', '')
        ->assertDontSee('Edit your comment')
        ->assertSeeIn('[data-test="comment-base-edit-indicator"]', 'Please comment on this post')
        ->assertSeeIn('[data-test="submit-comment-btn"]', 'Send comment')
        ->assertMissing('[data-test="cancel-reply-edit-btn"]')
        ->assertMissing('Cancel')
        // Edit comment again, change comment body in the textarea and submit
        ->click('@btn-edit-comment-' . $comment->id)
        ->type('[data-test="comment-textarea"]', 'This is my edited comment.')
        ->click('@submit-comment-btn')
        // after submit editing, check route, path, see comment body and check that form state is reset
        ->assertRoute('posts.show', ['post' => $post])
        ->assertPathIs('/posts/' . $post->id)
        ->assertSee('This is my edited comment.')
        ->assertDontSee('Edit your comment')
        ->assertSeeIn('[data-test="submit-comment-btn"]', 'Send comment')
    ;

    expect($comment1 = $commentAuthor->comment()->first()->toArray())->toMatchArray([
        'body' => 'This is my edited comment.',
        'parent_id' => null,
        'post_id' => $post->id,
        'user_id' => $commentAuthor->id,
    ])
        ->and($commentAuthor->comment()->count())->toBe(1);
});

// Admin actions (Comment) button testing.
test('show/hide admin action window and toggles custom reason input', function () {
    $user = User::factory()->create();
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN
    ]);
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $commentAuthor = User::factory()->create();
    $post = Post::factory()->create([
        'content' => 'This is a test post.',
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $comment = Comment::factory()->create([
        'post_id' => $post->id,
        'user_id' => $commentAuthor->id,
        'parent_id' => null,
        'is_deleted' => false]);
    $comment1 = Comment::factory()->create([
        'post_id' => $post->id,
        'user_id' => $user->id,
        'parent_id' => $comment->id,
        'is_deleted' => false
    ]);
    $this->actingAs($admin);

    visit(route('posts.show', $post))
        ->assertPresent('[data-test="admin-actions-trigger-btn-' . $comment->id . '"]')
        ->assertVisible('[data-test="admin-actions-trigger-btn-' . $comment->id . '"]')
        // click admin actions button - show admin actions window with delete reason select and submit button and hidden custom reason input
        ->click('@admin-actions-trigger-btn-' . $comment->id)
        ->assertVisible('@delete-reason-select')
        ->assertVisible('@soft-delete-submit-btn')
        ->assertMissing('@custom-reason-input-' . $comment->id)
        ->select('@delete-reason-select', 'spam')
        ->assertMissing('@custom-reason-input-' . $comment->id)
        ->select('@delete-reason-select', 'other')
        ->assertVisible('@custom-reason-input-' . $comment->id)
        // click admin actions button again - hide admin actions window with delete reason select and submit button and hidden custom reason input
        ->click('@admin-actions-trigger-btn-' . $comment->id)
        ->assertMissing('@delete-reason-select')
        ->assertMissing('@soft-delete-submit-btn')
        ->assertMissing('@custom-reason-input-' . $comment->id)
        // click admin actions button again - show admin actions window with custom reason input
        ->click('@admin-actions-trigger-btn-' . $comment->id)
        ->assertVisible('@delete-reason-select')
        ->assertVisible('@soft-delete-submit-btn')
        ->assertVisible('@custom-reason-input-' . $comment->id)
        ->select('@delete-reason-select', 'spam')
        ->click('@soft-delete-submit-btn')
        // after submit soft deleting check that comment body changed to The message was deleted by the moderator. Reason:
        ->assertDontSee($comment->body)
        ->assertSee('The message was deleted by the moderator. Reason: Spam / Advertising')
        // click admin actions button again - show admin actions window with restore and complete delete buttons
        ->click('@admin-actions-trigger-btn-' . $comment->id)
        ->assertVisible('@restore-comment-' . $comment->id . '-btn')
        ->assertVisible('@delete-comment-' . $comment->id . '-btn')
        // click restore button - after restore check that comment body returned
        ->click('@restore-comment-' . $comment->id . '-btn')
        ->assertDontSee('The message was deleted by the moderator. Reason:')
        ->assertSee($comment->body)
        // soft delete again
        ->click('@admin-actions-trigger-btn-' . $comment->id)
        ->select('@delete-reason-select', 'spam')
        ->click('@soft-delete-submit-btn')
        // hard delete
        ->click('@admin-actions-trigger-btn-' . $comment->id)
        ->assertScript('(() => { window.confirm = () => false; return false; })()', false)
        ->click('@delete-comment-' . $comment->id . '-btn')
        ->assertScript('(() => { window.confirm = () => true; return true; })()', true)
        ->click('@delete-comment-' . $comment->id . '-btn')
        ->assertDontSee($comment->body)
        ->assertDontSee('The message was deleted by the moderator. Reason:')
        ->assertSee('No comments yet')
    ;
});
