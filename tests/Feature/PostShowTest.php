<?php

use App\Enum\UserRole;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('requires authentication to view post', function () {
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);

    $this->get('/posts/{post}', ['post' => $post->id])->assertRedirect(route('login'));
});

test('authenticated author can view his own draft post', function () {
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $this->actingAs($author);
    $post = Post::factory()->create([
        'is_published' => false,
        'user_id' => $author->id,
    ]);

    $response = $this->get(route('posts.show', $post));

    $response->assertStatus(200)
        ->assertSee($post->title)
        ->assertSee('Draft');
});

test('authenticated user can not view not his own draft post', function () {
    $this->actingAs(User::factory()->create());
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->create([
        'is_published' => false,
        'user_id' => $author->id,
    ]);

    $response = $this->get(route('posts.show', $post));

    $response->assertStatus(403);
});

test('authenticated user can not view soft deleted post', function () {
    $this->actingAs(User::factory()->create());
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
        'deleted_at' => now()->subMonths(2),
    ]);

    $response = $this->get(route('posts.show', $post));

    $response->assertStatus(404);
});

test('authenticated user can see post details, author, tags and formatted date', function () {
    $users = User::factory()->count(10)->create();
    $user = $users[0];
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
        'content' => 'Paragraph 1\n\nParagraph 2',
        'image' => 'posts/MYjcAf3FI5FS.jpg',
        'created_at' => now()->subMonths(2),
    ]);
    for ($index=0; $index < 5; $index++) {
        $post->tags()->attach(Tag::factory()->create(['name' => 'Laravel_'.$index]));
    }
    foreach ($users as $u) {
        // Simulating that the user liked the post
        $post->reactions()->create(['user_id' => $u->id, 'is_like' => true]);
    }

    $this->actingAs($user);

    $response = $this->get(route('posts.show', $post));
    $postFromView = $response->viewData('post'); // fresh data from view ($post updated)

    //$expectedImgTag = '<img src="'.$post->image_url.'" alt="'.$post->title.'"';
    $authoredByArray = ['Authored by:', $author->name, '('.$author->email.')'];
    $createdAtArray = ['Created at:', $post->created_at->format('Y-m-d H:i:s'), '2 months ago'];
    $imageArray = ['<img', $post->image_url, 'alt="'.$post->title.'"'];
    $paragraphsArray = ['Paragraph 1', 'Paragraph 2'];

    $response->assertStatus(200)
        ->assertViewIs('posts.show')
        ->assertViewHas('post')
        ->assertViewHas('comments')
        ->assertSeeInOrder($authoredByArray, false)
        ->assertSeeInOrder($createdAtArray, false)
        ->assertSee($postFromView->likes_count)
        ->assertSee($post->title)
        ->assertSee($post->image)
        //->assertSee($expectedImgTag, false) // like this
        ->assertSeeInOrder($imageArray, false) // OR like this
        ->assertSeeInOrder($paragraphsArray, false)
        //->assertSee($post->comments->pluck('body')->implode(', '))
    ;

    // Check tags
    for ($index=0; $index < 5; $index++) {
        $response->assertSee('Laravel_'.$index);
    }

    expect($postFromView->userReaction?->is_like)->toBeTrue() // current user has liked the post
        ->and($postFromView->likes_count)->toBe(10); // total likes count
});

test('authenticated user can not see image block in post details if no image', function () {
    $this->actingAs(User::factory()->create());
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->withoutImage()->create([
        'is_published' => true,
        'user_id' => $author->id,
        //'image' => null, // or use withoutImage() factory state
    ]);

    $response = $this->get(route('posts.show', $post));

    $response->assertStatus(200)
        ->assertViewIs('posts.show')
        ->assertViewHas('post')
        ->assertSee($author->name)
        ->assertSee($post->created_at->format('Y-m-d H:i:s'))
        ->assertSee($post->title)
        ->assertDontSee($post->image);
});

test('authenticated user can see red heart if he has liked the post', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    // Simulating that the user liked the post
    $post->reactions()->create(['user_id' => $user->id, 'is_like' => true]);

    $response = $this->get(route('posts.show', $post));

    $notLikedHiddenArray = ['<svg', 'has-not-liked-by-current-user js-icon-outline size-5 hidden'];
    $hasLikedArray = ['<svg', 'has-liked-by-current-user js-icon-solid size-5 text-red-600'];

    $response->assertStatus(200)
        ->assertViewIs('posts.show')
        ->assertViewHas('post')
        ->assertSee($author->name)
        ->assertSee($post->created_at->format('Y-m-d H:i:s'))
        ->assertSee($post->title)
        ->assertSeeInOrder($notLikedHiddenArray, false) // hidden, user do not see this icon (gray contoured heart)
        ->assertSeeInOrder($hasLikedArray, false) // user see this icon (red solid heart)
        ->assertSee($post->likes_count);

    expect($post->userReaction?->is_like)->toBeTrue();
});

test('authenticated user can not see red heart if he has not liked the post', function () {
    $this->actingAs(User::factory()->create());
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);

    $response = $this->get(route('posts.show', $post));

    $notLikedArray = ['<svg', 'has-not-liked-by-current-user js-icon-outline size-5'];
    $hasLikedHiddenArray = ['<svg', 'has-liked-by-current-user js-icon-solid size-5 text-red-600 hidden'];

    $response->assertStatus(200)
        ->assertViewIs('posts.show')
        ->assertViewHas('post')
        ->assertSee($author->name)
        ->assertSee($post->created_at->format('Y-m-d H:i:s'))
        ->assertSee($post->title)
        ->assertSeeInOrder($notLikedArray, false) // user see this icon (gray contoured heart)
        ->assertSeeInOrder($hasLikedHiddenArray, false) // hidden, user do not see this icon(red solid heart)
        ->assertSee($post->likes_count);

    expect($post->userReaction?->is_like)->toBeNull();
});

test('user can not see single post like part if it is not published', function () {
    $this->actingAs(User::factory()->create(['role' => UserRole::ADMIN]));
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->withoutImage()->create([
        'is_published' => false,
        'user_id' => $author->id,
    ]);

    $response = $this->get(route('posts.show', $post));

    $response->assertStatus(200)
        ->assertViewIs('posts.show')
        ->assertViewHas('post')
        ->assertSee($author->name)
        ->assertDontSee('post-reaction-block')
        ->assertSee($post->title);
});

test('authenticated user can see comment form', function () {
    $this->actingAs(User::factory()->create());
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);

    $response = $this->get(route('posts.show', $post));

    $commentFormArray = [
        '<form',
        'action="'.route('comments.store', $post).'"',
        'method="POST"',
        'id="global_comment_form"',
        '<textarea',
        'id="form_body"',
        'name="body"',
        '<button type="submit" id="form_submit_btn"',
        '</form>',
    ];

    $response->assertStatus(200)
        ->assertViewIs('posts.show')
        ->assertSeeInOrder($commentFormArray, false);
});

test('allows authenticated user to leave a comment', function () {
    $user = User::factory()->create();
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);

    $response = $this->actingAs($user)
        ->from(route('posts.show', $post)) // must be added because return back() in controller returns to '/'
        ->post(route('comments.store', $post), [
            'body' => 'This is a test comment',
            'parent_id' => null,
            ]);

    $response->assertRedirect(route('posts.show', $post)) // redirect back to post page
        ->assertSessionHas('success', 'The comment was added successfully!'); // success flag in session

    // creating a database record with a comment linked to the current user and this post
    $this->assertDatabaseHas('comments', [
        'post_id' => $post->id,
        'user_id' => $user->id,
        'body' => 'This is a test comment',
    ]);
});

test('authenticated user can not send a too short or empty comment', function () {
    $user = User::factory()->create();
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);

    $response = $this->actingAs($user)
        ->from(route('posts.show', $post)) // must be added because return back() in controller returns to '/'
        ->post(route('comments.store', $post), [
            'body' => 'x',
            'parent_id' => null,
        ]);

    $response->assertRedirect(route('posts.show', $post));

    $response->assertSessionHasErrors([
        'body' => 'The body field must be at least 2 characters.'
    ]);
});

test('authenticated user can not send a too long comment', function () {
    $user = User::factory()->create();
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);

    $sentence = 'A eos cupiditate natus facilis facilis optio nisi officia. Architecto omnis assumenda ipsam laboriosam tempore voluptatem pariatur. ';
    // 115 characters, 20 times
    $longCommentBody = str_repeat($sentence, 20);

    $response = $this->actingAs($user)
        ->from(route('posts.show', $post)) // must be added because return back() in controller returns to '/'
        ->post(route('comments.store', $post), [
            'body' => $longCommentBody,
            'parent_id' => null,
        ]);

    $response->assertRedirect(route('posts.show', $post));

    $response->assertSessionHasErrors([
        'body' => 'The body field must not be greater than 2000 characters.'
    ]);
});

test('notifies if the comments do not exist', function () {
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $this->actingAs(User::factory()->create());

    $response = $this->get(route('posts.show', $post));

    $response->assertStatus(200)
        ->assertDontSee('class="comments-tree"')
        ->assertSee('No comments yet.');
});

test('renders comments in a tree structure and child comments are rendered with indentation', function () {
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $this->actingAs(User::factory()->create());

    // Creating a parent comment and a reply to it
    $parentComment = Comment::factory()->create([
        'post_id' => $post->id,
        'parent_id' => null,
        'body' => 'Parent Text',
        'is_deleted' => false
    ]);
    $childComment = Comment::factory()->create([
        'post_id' => $post->id,
        'parent_id' => $parentComment->id,
        'body' => 'Child Text',
        'is_deleted' => false
    ]);

    $response = $this->get(route('posts.show', $post));

    $commentsArray = [
        'class="comments-tree"',
        'id="comment-'.$parentComment->id.'"',
        'class="comment-header"',
        'class="comment-body',
        'Parent Text',
        'class="comment-replies"',
        'id="comment-'.$childComment->id.'"',
        'ml-7.5', // child comments are rendered with indentation
        'class="comment-header"',
        'class="comment-body',
        'Child Text'
    ];

    $response->assertStatus(200)
        ->assertSeeInOrder($commentsArray, false);
});

test('renders deeply nested (parent-child-grandchild) comments in correct tree sequence', function () {
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    // Level 1: Root comment
    $parent = Comment::factory()->create([
        'post_id' => $post->id,
        'parent_id' => null,
        'body' => 'Level 1: Root',
        'is_deleted' => false
    ]);
    // Level 2: Answer for root
    $child = Comment::factory()->create([
        'post_id' => $post->id,
        'parent_id' => $parent->id,
        'body' => 'Level 2: Child',
        'is_deleted' => false
    ]);
    // Level 3: Answer for answer (Grandchild)
    $grandchild = Comment::factory()->create([
        'post_id' => $post->id,
        'parent_id' => $child->id,
        'body' => 'Level 3: Grandchild',
        'is_deleted' => false
    ]);
    $this->actingAs(User::factory()->create());

    $response = $this->get(route('posts.show', $post));

    // Checking that all levels are rendered strictly one after the other in a hierarchical order.
    $response->assertSeeInOrder([
        'Level 1: Root',
        'Level 2: Child',
        'Level 3: Grandchild'
    ], false);
});

test('root comments are displayed in descending chronological order', function () {
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    //Comment::factory()->create(['post_id' => $post->id, 'parent_id' => null, 'body' => 'Comment #5', 'created_at' => now()->subMinutes(5), 'is_deleted' => false]);
    //Comment::factory()->create(['post_id' => $post->id, 'parent_id' => null, 'body' => 'Comment #4', 'created_at' => now()->subMinutes(4), 'is_deleted' => false]);
    //Comment::factory()->create(['post_id' => $post->id, 'parent_id' => null, 'body' => 'Comment #3', 'created_at' => now()->subMinutes(3), 'is_deleted' => false]);
    //Comment::factory()->create(['post_id' => $post->id, 'parent_id' => null, 'body' => 'Comment #2', 'created_at' => now()->subMinutes(2), 'is_deleted' => false]);
    //Comment::factory()->create(['post_id' => $post->id, 'parent_id' => null, 'body' => 'Comment #1', 'created_at' => now()->subMinutes(1), 'is_deleted' => false]);
    for ($index=5; $index >= 1; $index--) {
        Comment::factory()->create([
            'post_id' => $post->id,
            'parent_id' => null,
            'body' => 'Comment #'.$index,
            'created_at' => now()->subMinutes($index),
            'is_deleted' => false
        ]);
    }
    $this->actingAs(User::factory()->create());

    $response = $this->get(route('posts.show', $post));

    $commentsOrderArray = [];
//    $commentsOrderArray = [
//        'Comment #1',
//        'Comment #2',
//        'Comment #3',
//        'Comment #4',
//        'Comment #5',
//    ];
    for ($index=1; $index <= 5; $index++) {
        $commentsOrderArray[] = 'Comment #'.$index;
    }

    $response->assertStatus(200)
        ->assertSeeInOrder($commentsOrderArray, false);
});

test('if there are less than 5 root comments, it does not display pagination, otherwise it displays', function () {
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $this->actingAs(User::factory()->create());

    $response = $this->get(route('posts.show', $post));

    $response->assertStatus(200)
        ->assertDontSee('class="custom-pagination');

    // Creating the oldest comment (it should go to page 2)
    $oldestComment = Comment::factory()->create([
        'post_id' => $post->id,
        'parent_id' => null,
        'body' => 'Oldest Root Comment',
        'created_at' => now()->subDays(2),
        'is_deleted' => false
    ]);

    // Creating 5 more fresh root comments (they will occupy the 1st page)
    $freshComments = Comment::factory()->count(5)->create([
        'post_id' => $post->id,
        'parent_id' => null,
        'created_at' => now(),
        'is_deleted' => false
    ]);

    $response = $this->get(route('posts.show', $post));

    // Checking that there are exactly 5 elements on the first page in the ViewData('comments')
    expect($response->viewData('comments')->items())->toHaveCount(5);

    $response->assertStatus(200)
        ->assertSeeInOrder(['class="comments-tree"', 'class="custom-pagination'], false)
        // Checking for pagination links with the #comments_section_start fragment
        // Laravel generates links like: page=2#comments_section_start
        ->assertSee('page=2#comments_section_start', false)
        ->assertDontSee('Oldest Root Comment');
});

test('child comments are exempt from root pagination limits', function () {
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    // Creating 1 root comment
    $parent = Comment::factory()->create([
        'post_id' => $post->id,
        'parent_id' => null,
        'body' => 'The Only Root',
        'is_deleted' => false
    ]);
    // Creating 6 child responses (more than the pagination limit of 5)
    $children = Comment::factory()->count(6)->create([
        'post_id' => $post->id,
        'parent_id' => $parent->id,
        'is_deleted' => false
    ]);
    $this->actingAs(User::factory()->create());

    $response = $this->get(route('posts.show', $post));

    $response->assertOk();

    // Checking the paginator: the page should have only 1 root element.
    expect($response->viewData('comments')->items())->toHaveCount(1);

    // Checking that all 6 child comments are physically visible on the page.
    foreach ($children as $child) {
        $response->assertSee($child->body);
    }
});

test('does not leak comments from other posts', function () {
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $postA = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $postB = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    // Current post comment
    $commentA = Comment::factory()->create([
        'post_id' => $postA->id,
        'parent_id' => null,
        'body' => 'Comment for Post A',
        'is_deleted' => false
    ]);
    // Another post comment
    $commentB = Comment::factory()->create([
        'post_id' => $postB->id,
        'parent_id' => null,
        'body' => 'Comment for Post B',
        'is_deleted' => false
    ]);
    $this->actingAs(User::factory()->create());

    $response = $this->get(route('posts.show', $postA)); // View Post A

    $response->assertOk()
        ->assertSee($commentA->body)
        ->assertDontSee($commentB->body);
});

test('sends CommentReplied notification to parent comment author with custom data', function () {
    $parentAuthor = User::factory()->create();
    $replyAuthor = User::factory()->create(['name' => 'John Doe']);
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $parentComment = Comment::factory()->create([
        'post_id' => $post->id,
        'user_id' => $parentAuthor->id,
        'is_deleted' => false
    ]);

    // Reply to the parent comment
    $this->actingAs($replyAuthor)->post(route('comments.store', $post), [
        'body' => 'This is my reply text',
        'parent_id' => $parentComment->id,
    ]);

    // Check that the notification was sent to the parent author
    $this->assertDatabaseHas('notifications', [
        'notifiable_id' => $parentAuthor->id,
    ]);

    // get the parentAuthor notification
    $notification = $parentAuthor->notifications()->first();
    // Check that the notification has the correct data
    expect($notification->data['data']['type'])->toBe('comment_reply')
        ->and($notification->data['data']['post_id'])->toBe($post->id)
        ->and($notification->data['body'])->toContain('John Doe answered');
});

test('does not send notification if user somehow replies to their own comment', function () {
    $user = User::factory()->create();
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $parentComment = Comment::factory()->create([
        'post_id' => $post->id,
        'user_id' => $user->id,
        'is_deleted' => false
    ]);

    // user answers their own comment
    $this->actingAs($user)->post(route('comments.store', $post), [
        'body' => 'Self reply',
        'parent_id' => $parentComment->id,
    ]);

    // Check that no notification was sent
    expect($user->notifications)->toHaveCount(0);
});

test('displays the name of the comment author and correct date', function () {
    $user = User::factory()->create();
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
        'deleted_at' => null,
    ]);
    $comment = Comment::factory()->create([
        'post_id' => $post->id,
        'user_id' => $user->id,
        'created_at' => now()->subMonths(2),
        'is_deleted' => false
    ]);
    $this->actingAs($user);

    $response = $this->get(route('posts.show', $post));

    $commentsArray = [
        'class="comments-tree"',
        'id="comment-'.$comment->id.'"',
        'class="comment-header"',
        '<strong class="text-indigo-400"',
        $user->name,
        '<small class="text-gray-600 dark:text-gray-400">',
        $comment->created_at->format('Y-m-d H:i:s'),
        '2 months ago',
    ];

    $response->assertStatus(200)
        ->assertSeeInOrder($commentsArray, false);
});

test('displays Anonymous if comment author account is deleted', function () {
    $postAuthor = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $postAuthor->id,
    ]);
    $comment = Comment::factory()->create([
        'post_id' => $post->id,
        'user_id' => null,
        'is_deleted' => false
    ]);
    $this->actingAs(User::factory()->create());

    $response = $this->get(route('posts.show', $post));

    $commentsArray = [
        'class="comments-tree"',
        'id="comment-'.$comment->id.'"',
        'class="comment-header"',
        '<strong class="text-indigo-400"',
        'Anonymous',
    ];

    $response->assertStatus(200)
        ->assertSeeInOrder($commentsArray, false);
});

test('renders correct rating value and styles for 0 rating', function () {
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $comment = Comment::factory()->create([
        'post_id' => $post->id,
        'is_deleted' => false
    ]);
    $usersReactedToComments = User::factory()->count(4)->create();
    // Imitate 2 likes and 2 dislike (rating 0)
    $comment->reactions()->createMany([
        ['user_id' => $usersReactedToComments[0]->id, 'is_like' => true],
        ['user_id' => $usersReactedToComments[1]->id, 'is_like' => true],
        ['user_id' => $usersReactedToComments[2]->id, 'is_like' => false],
        ['user_id' => $usersReactedToComments[3]->id, 'is_like' => false],
    ]);
    $this->actingAs(User::factory()->create());

    $response = $this->get(route('posts.show', $post));

    $commentRatingArray = [
        '<span class="js-comment-rating',
        'text-gray-400',
        '0',
    ];

    $response->assertStatus(200)
        ->assertSeeInOrder($commentRatingArray, false);
});

test('renders correct rating value and styles for positive rating', function () {
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $comment = Comment::factory()->create([
        'post_id' => $post->id,
        'is_deleted' => false
    ]);
    $usersReactedToComments = User::factory()->count(4)->create();
    // Imitate 3 likes and 1 dislike (rating +2)
    $comment->reactions()->createMany([
        ['user_id' => $usersReactedToComments[0]->id, 'is_like' => true],
        ['user_id' => $usersReactedToComments[1]->id, 'is_like' => true],
        ['user_id' => $usersReactedToComments[2]->id, 'is_like' => true],
        ['user_id' => $usersReactedToComments[3]->id, 'is_like' => false],
    ]);
    $this->actingAs(User::factory()->create());

    $response = $this->get(route('posts.show', $post));

    $commentRatingArray = [
        '<span class="js-comment-rating',
        'text-green-600',
        '+2',
    ];

    $response->assertStatus(200)
        ->assertSeeInOrder($commentRatingArray, false);
});

test('renders correct rating value and styles for negative rating', function () {
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $comment = Comment::factory()->create([
        'post_id' => $post->id,
        'is_deleted' => false
    ]);
    $usersReactedToComments = User::factory()->count(4)->create();
    // Imitate 1 likes and 3 dislike (rating -2)
    $comment->reactions()->createMany([
        ['user_id' => $usersReactedToComments[0]->id, 'is_like' => true],
        ['user_id' => $usersReactedToComments[1]->id, 'is_like' => false],
        ['user_id' => $usersReactedToComments[2]->id, 'is_like' => false],
        ['user_id' => $usersReactedToComments[3]->id, 'is_like' => false],
    ]);
    $this->actingAs(User::factory()->create());

    $response = $this->get(route('posts.show', $post));

    $commentRatingArray = [
        '<span class="js-comment-rating',
        'text-red-500',
        '-2',
    ];

    $response->assertStatus(200)
        ->assertSeeInOrder($commentRatingArray, false);
});

test('renders like/dislike icons not filled and with currentColor if current user has not reacted', function () {
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $comment = Comment::factory()->create([
        'post_id' => $post->id,
        'is_deleted' => false
    ]);
    $usersReactedToComments = User::factory()->count(4)->create();
    $comment->reactions()->createMany([
        ['user_id' => $usersReactedToComments[0]->id, 'is_like' => true],
        ['user_id' => $usersReactedToComments[1]->id, 'is_like' => true],
        ['user_id' => $usersReactedToComments[2]->id, 'is_like' => true],
        ['user_id' => $usersReactedToComments[3]->id, 'is_like' => false],
    ]);
    $this->actingAs(User::factory()->create());

    $response = $this->get(route('posts.show', $post));

    $commentLikeDislikeArray = [
        '<button type="button"',
        'class="js-reaction-btn',
        'hover:text-green-600',
        '<svg',
        'fill="none" stroke="currentColor"',
        '<button type="button"',
        'class="js-reaction-btn',
        'hover:text-red-600',
        '<svg',
        'fill="none" stroke="currentColor"',
    ];

    $response->assertStatus(200)
        ->assertSee('js-reaction-btn cursor-pointer flex items-center gap-1.5 font-medium transition-colors duration-150 hover:text-green-600')
        ->assertDontSee('js-reaction-btn cursor-pointer flex items-center gap-1.5 font-medium transition-colors duration-150 hover:text-green-600 text-green-600')
        ->assertSee('js-reaction-btn cursor-pointer flex items-center gap-1.5 font-medium transition-colors duration-150 hover:text-red-600')
        ->assertDontSee('js-reaction-btn cursor-pointer flex items-center gap-1.5 font-medium transition-colors duration-150 hover:text-red-600 text-red-600')
        ->assertSeeInOrder($commentLikeDislikeArray, false);
});

test('renders like icon filled green if current user has reacted (dislike - not filled and with currentColor)', function () {
    $user = User::factory()->create();
    $commentUser = User::factory()->create();
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $comment = Comment::factory()->create([
        'post_id' => $post->id,
        'user_id' => $commentUser->id,
        'is_deleted' => false
    ]);
    $usersReactedToComments = User::factory()->count(3)->create();
    $comment->reactions()->createMany([
        ['user_id' => $user->id, 'is_like' => true],
        ['user_id' => $usersReactedToComments[0]->id, 'is_like' => true],
        ['user_id' => $usersReactedToComments[1]->id, 'is_like' => true],
        ['user_id' => $usersReactedToComments[2]->id, 'is_like' => false],
    ]);
    $this->actingAs($user);

    $response = $this->get(route('posts.show', $post));

    $commentLikeDislikeArray = [
        '<button type="button"',
        'class="js-reaction-btn',
        'hover:text-green-600 text-green-600',
        '<svg',
        'fill="currentColor" stroke="currentColor"',
        '<button type="button"',
        'class="js-reaction-btn',
        'hover:text-red-600',
        '<svg',
        'fill="none" stroke="currentColor"',
    ];

    $response->assertStatus(200)
        ->assertSee('js-reaction-btn cursor-pointer flex items-center gap-1.5 font-medium transition-colors duration-150 hover:text-green-600 text-green-600')
        ->assertSee('js-reaction-btn cursor-pointer flex items-center gap-1.5 font-medium transition-colors duration-150 hover:text-red-600')
        ->assertDontSee('js-reaction-btn cursor-pointer flex items-center gap-1.5 font-medium transition-colors duration-150 hover:text-red-600 text-red-600')
        ->assertSeeInOrder($commentLikeDislikeArray, false);
});

test('renders dislike icons filled red if current user has reacted (like - not filled and with currentColor)', function () {
    $user = User::factory()->create();
    $commentUser = User::factory()->create();
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $comment = Comment::factory()->create([
        'post_id' => $post->id,
        'user_id' => $commentUser->id,
        'is_deleted' => false
    ]);
    $usersReactedToComments = User::factory()->count(3)->create();
    $comment->reactions()->createMany([
        ['user_id' => $usersReactedToComments[0]->id, 'is_like' => true],
        ['user_id' => $usersReactedToComments[1]->id, 'is_like' => true],
        ['user_id' => $usersReactedToComments[2]->id, 'is_like' => true],
        ['user_id' => $user->id, 'is_like' => false],
    ]);
    $this->actingAs($user);

    $response = $this->get(route('posts.show', $post));

    $commentLikeDislikeArray = [
        '<button type="button"',
        'class="js-reaction-btn',
        'hover:text-green-600',
        '<svg',
        'fill="none" stroke="currentColor"',
        '<button type="button"',
        'class="js-reaction-btn',
        'hover:text-red-600 text-red-600',
        '<svg',
        'fill="currentColor" stroke="currentColor"',
    ];

    $response->assertStatus(200)
        ->assertSee('js-reaction-btn cursor-pointer flex items-center gap-1.5 font-medium transition-colors duration-150 hover:text-green-600')
        ->assertDontSee('js-reaction-btn cursor-pointer flex items-center gap-1.5 font-medium transition-colors duration-150 hover:text-green-600 text-green-600')
        ->assertSee('js-reaction-btn cursor-pointer flex items-center gap-1.5 font-medium transition-colors duration-150 hover:text-red-600 text-red-600')
        ->assertSeeInOrder($commentLikeDislikeArray, false);
});

test('user can not see comment like part if comment is soft deleted', function () {
    $user = User::factory()->create();
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $comment = Comment::factory()->create([
        'post_id' => $post->id,
        'is_deleted' => true,
    ]);
    $this->actingAs($user);

    $response = $this->get(route('posts.show', $post));

    $response->assertStatus(200)
        ->assertSee($post->title)
        ->assertDontSee('comment-reaction-block');
});

test('authenticated user can like a comment when no reaction exists', function () {
    $user = User::factory()->create();
    $comment = Comment::factory()->create([
        'is_deleted' => false
    ]);

    $this->actingAs($user);

    // Sending JSON POST request
    $response = $this->postJson('/reactions/toggle', [
        'type' => 'comment',
        'id' => $comment->id,
        'is_like' => true, // или '1', в зависимости от вашей валидации
    ]);

    // Checking successful JSON response (status is 200 or 201)
    $response->assertOk();

    // Checking that there is like in the DB
    $this->assertDatabaseHas('likes', [
        'user_id' => $user->id,
        'likeable_id' => $comment->id,
        'likeable_type' => Comment::class,
        'is_like' => true,
    ]);
});

test('authenticated user removes his like by clicking it again', function () {
    $user = User::factory()->create();
    $comment = Comment::factory()->create([
        'is_deleted' => false
    ]);

    // Create existing like of this user
    Like::factory()->forComment($comment)->create([
        'user_id' => $user->id,
        'is_like' => true,
    ]);

    $this->actingAs($user);

    // User clicking this LIKE again (is_like: true)
    $response = $this->postJson('/reactions/toggle', [
        'type' => 'comment',
        'id' => $comment->id,
        'is_like' => true,
    ]);

    $response->assertOk();

    // Checking that this like is DELETED from DB
    $this->assertDatabaseMissing('likes', [
        'user_id' => $user->id,
        'likeable_id' => $comment->id,
        'likeable_type' => Comment::class,
    ]);
});

test('authenticated user switches reaction from dislike to like', function () {
    $user = User::factory()->create();
    $comment = Comment::factory()->create([
        'is_deleted' => false
    ]);

    // Step 1: The user INITIALLY has a DISLIKE
    Like::factory()->forComment($comment)->create([
        'user_id' => $user->id,
        'is_like' => false,
    ]);

    $this->actingAs($user);

    // Step 2: The user clicks on the LIKE (is_like: true)
    $response = $this->postJson('/reactions/toggle', [
        'type' => 'comment',
        'id' => $comment->id,
        'is_like' => true,
    ]);

    $response->assertOk();

    // Step 3: Checking that the old dislike is gone
    $this->assertDatabaseMissing('likes', [
        'user_id' => $user->id,
        'likeable_id' => $comment->id,
        'likeable_type' => Comment::class,
        'is_like' => false,
    ]);

    // Step 4: Checking that a new like has been successfully created
    $this->assertDatabaseHas('likes', [
        'user_id' => $user->id,
        'likeable_id' => $comment->id,
        'likeable_type' => Comment::class,
        'is_like' => true,
    ]);
});

test('renders soft deleted comment gray and with specific message instead of body', function () {
    $user = User::factory()->create();
    $anotherUser = User::factory()->create();
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $comment1 = Comment::factory()->create([
        'post_id' => $post->id,
        'user_id' => $user->id,
        'is_deleted' => true,
        'deletion_reason' => 'Spam / Advertising',
        'created_at' => now()->subMonths(2)
    ]);
    $comment2 = Comment::factory()->create([
        'post_id' => $post->id,
        'user_id' => $anotherUser->id,
        'is_deleted' => true,
        'deletion_reason' => 'Insults / Aggression',
        'created_at' => now()->subMonths(1)
    ]);
    $this->actingAs($user);

    $response = $this->get(route('posts.show', $post));


    $commentsArray = [
        'class="comments-tree"',

        'id="comment-'.$comment2->id.'"',
        'class="comment-header"',
        '<strong class="text-gray-600 dark:text-gray-400"',
        $anotherUser->name,
        'class="comment-body',
        '<p class="text-gray-600 dark:text-gray-400"',
        'The message was deleted by the moderator. Reason: Insults / Aggression',

        'id="comment-'.$comment1->id.'"',
        'class="comment-header"',
        '<strong class="text-gray-600 dark:text-gray-400"',
        $user->name,
        'class="comment-body',
        '<p class="text-gray-600 dark:text-gray-400"',
        'The message was deleted by the moderator. Reason: Spam / Advertising',
    ];

    $response->assertStatus(200)
        ->assertDontSee('onclick="prepareReply')
        ->assertDontSee('onclick="prepareEdit')
        ->assertSeeInOrder($commentsArray, false);
});

test('regular users can not see admin actions button and is denied soft deleting comments', function () {
    $user = User::factory()->create();
    $anotherUser = User::factory()->create();
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $comment = Comment::factory()->create([
        'post_id' => $post->id,
        'user_id' => $anotherUser->id,
        'is_deleted' => false
    ]);
    $this->actingAs($user);

    $response = $this->get(route('posts.show', $post));

    $response->assertStatus(200)->assertDontSee('admin-actions-trigger-btn');

    // Trying to send a deletion request
    $response = $this->actingAs($user)
        ->put(route('admin.comments.delete', $comment), [
            'reason_key' => 'spam'
        ]);

    $response->assertStatus(403);
    expect($comment->fresh()->where('is_deleted', true)->first())->toBeNull(); // Comment not deleted
});

test('regular users can see admin actions button and is able to soft deleting his own comments', function () {
    $user = User::factory()->create();
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $comment = Comment::factory()->create([
        'post_id' => $post->id,
        'user_id' => $user->id,
        'is_deleted' => false
    ]);
    $this->actingAs($user);

    $response = $this->get(route('posts.show', $post));

    $response->assertStatus(200)->assertSee('admin-actions-trigger-btn');
    expect($comment->fresh()->where('is_deleted', true)->count())->toBe(0);

    // Trying to send a deletion request
    $response = $this->actingAs($user)
        ->put(route('admin.comments.delete', $comment), [
            'reason_key' => 'self_delete'
        ]);

    $response->assertDontSee('admin-actions-trigger-btn');
    expect($comment->fresh()->where('is_deleted', true)->count())->toBe(1); // Comment deleted
});

test('admin can see admin actions button and is able to soft deleting comments', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN
    ]);
    $user = User::factory()->create();
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $comment = Comment::factory()->create([
        'post_id' => $post->id,
        'user_id' => $user->id,
        'is_deleted' => false
    ]);
    $this->actingAs($admin);

    $response = $this->get(route('posts.show', $post));

    $response->assertStatus(200)->assertSee('admin-actions-trigger-btn');

    expect($comment->fresh()->where('is_deleted', true)->first())->toBeNull(); // no deleted comment

    // Trying to send a deletion request
    $response = $this->actingAs($admin)
        ->put(route('admin.comments.delete', $comment), [
            'reason_key' => 'spam'
        ]);

    expect($comment->fresh()->where('is_deleted', true)->count())->toBe(1);
});

test('regular users can not see admin actions button when his comment soft deleted & is denied restore and hard deleting', function () {
    $user = User::factory()->create();
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $comment = Comment::factory()->create([
        'post_id' => $post->id,
        'user_id' => $user->id,
        'is_deleted' => true
    ]);
    $this->actingAs($user);

    $response = $this->get(route('posts.show', $post));

    $response->assertStatus(200)->assertDontSee('admin-actions-trigger-btn');
    expect($comment->fresh()->where('is_deleted', true)->count())->toBe(1); // Comment deleted

    // Trying to send a restore request (somehow)
    $response = $this->actingAs($user)
        ->put(route('admin.comments.restore', $comment));

    $response->assertStatus(403);
    expect($comment->fresh()->where('is_deleted', true)->count())->toBe(1); // Comment still deleted

    // Trying to send a hard delete request (somehow)
    $response = $this->actingAs($user)
        ->delete(route('admin.comments.destroy', $comment));

    $response->assertStatus(403);
    expect($comment->fresh()->where('is_deleted', true)->count())->toBe(1); // Comment still soft deleted
});



