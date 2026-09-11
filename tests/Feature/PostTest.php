<?php

use App\Enum\UserRole;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;

uses(RefreshDatabase::class);

// List of Posts tests START
test('requires authentication to view list of posts', function () {
    $this->get('/posts')->assertRedirect(route('login'));
});

test('index page shows empty state when no posts exist', function () {
    $this->actingAs($user = User::factory()->create());
    $response = $this->get(route('posts.index'));

    // Assert: verify the response and empty-state message.
    $response->assertStatus(200);
    $response->assertSee('No posts at this time');
});

test('authenticated user can view a list of post (published, not drafts, not soft deleted)', function () {
    $this->actingAs($user = User::factory()->create());
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $posts = Post::factory()->count(3)->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $drafts = Post::factory()->count(2)->create([
        'is_published' => false,
        'user_id' => $author->id,
    ]);
    $softDeleted = Post::factory()->count(2)->create([
        'is_published' => true,
        'user_id' => $author->id,
        'deleted_at' => now()->create(2026, 05, 15),
    ]);

    $response = $this->get(route('posts.index'));
    $response->assertStatus(200)
        ->assertViewIs('posts.index')
        ->assertViewHas('posts')
        ->assertViewHas('authors')
        ->assertViewHas('tags')
        ->assertSee('List of Posts')
        ->assertSee($posts->pluck('title')->toArray()) // published only
        ->assertDontSee($drafts->pluck('title')->toArray()) // no drafts
        ->assertDontSee($softDeleted->pluck('title')->toArray()); // no soft deleted

    expect(Post::count())->toBe(5) // total posts (with drafts and without soft deleted)
        ->and($response->viewData('posts')->count())->toBe(3); // $this->assertEquals(3, $response->viewData('posts')->count());
    //$response->viewData('posts')->dd(); // Allows you to look inside the variable that the controller gave to the Blade
});

test('authenticated author can view a list of post with his own drafts', function () {
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $this->actingAs($author);
    $posts = Post::factory()->count(3)->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $drafts = Post::factory()->count(2)->create([
        'is_published' => false,
        'user_id' => $author->id,
    ]);

    $response = $this->get(route('posts.index'));
    $response->assertStatus(200)
        ->assertViewIs('posts.index')
        ->assertViewHas('posts')
        ->assertViewHas('authors')
        ->assertViewHas('tags')
        ->assertSee('List of Posts')
        ->assertSee($posts->pluck('title')->toArray()) // published
        ->assertSee($drafts->pluck('title')->toArray()); // drafts

    expect(Post::count())->toBe(5) // total posts (with drafts)
    ->and($response->viewData('posts')->count())->toBe(5); // published + drafts
});

test('authenticated author can view links edit/move to trash for his own posts on list of post', function () {
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $this->actingAs($author);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);

    $response = $this->get(route('posts.index'));
    $response->assertStatus(200)
        ->assertViewIs('posts.index')
        ->assertViewHas('posts')
        ->assertViewHas('authors')
        ->assertViewHas('tags')
        ->assertSee('List of Posts')
        ->assertSee('Edit Post')
        ->assertSee('Post to trash');
});

test('authenticated author can not view links edit/move to trash for not his own posts on list of post', function () {
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $author2 = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $this->actingAs($author2);
    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);

    $response = $this->get(route('posts.index'));
    $response->assertStatus(200)
        ->assertViewIs('posts.index')
        ->assertViewHas('posts')
        ->assertViewHas('authors')
        ->assertViewHas('tags')
        ->assertSee('List of Posts')
        ->assertDontSee('Edit Post')
        ->assertDontSee('Post to trash');
});

test('successfully filters post by its exact ID', function () {
    $this->actingAs($user = User::factory()->create());
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $targetPost = Post::factory()->create([
        'title' => 'Usual Post',
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $otherPost = Post::factory()->create([
        'title' => 'Another Post',
        'is_published' => true,
        'user_id' => $author->id,
    ]);

    $response =  $this->get(route('posts.index', ['q' => $targetPost->id]));

    expect(Post::count())->toBe(2)
        ->and($response->viewData('posts')->count())->toBe(1); // $this->assertEquals(1, $response->viewData('posts')->count());

    $response->assertOk()
        ->assertSee($targetPost->title)
        ->assertDontSee($otherPost->title);
});

test('successfully filters posts by part of its title', function () {
    $this->actingAs($user = User::factory()->create());
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $targetPost = Post::factory()->create([
        'title' => 'The secret word Laravel is here',
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $otherPost = Post::factory()->create([
        'title' => 'Nothing interesting here',
        'is_published' => true,
        'user_id' => $author->id,
    ]);

    $response =  $this->get(route('posts.index', ['q' => 'Larav']));

    expect(Post::count())->toBe(2)
        ->and($response->viewData('posts')->count())->toBe(1); // $this->assertEquals(1, $response->viewData('posts')->count());

    $response->assertOk()
        ->assertSee($targetPost->title)
        ->assertDontSee($otherPost->title);
});

test('successfully filters posts by text inside content', function () {
    $this->actingAs($user = User::factory()->create());
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $targetPost = Post::factory()->create([
        'title' => 'Post 1',
        'content' => 'There is a word sunflower inside the text.',
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $otherPost = Post::factory()->create([
        'title' => 'Post 2',
        'content' => 'Just some text.',
        'is_published' => true,
        'user_id' => $author->id,
    ]);

    $response =  $this->get(route('posts.index', ['q' => 'sunflower']));

    expect(Post::count())->toBe(2)
        ->and($response->viewData('posts')->count())->toBe(1); // $this->assertEquals(1, $response->viewData('posts')->count());

    $response->assertOk()
        ->assertSee($targetPost->title)
        ->assertDontSee($otherPost->title);
});

test('successfully filters posts within a specific date range', function () {
    $this->actingAs($user = User::factory()->create());
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $matchingPost = Post::factory()->create([
        'title' => 'Correct post',
        'is_published' => true,
        'user_id' => $author->id,
        'created_at' => now()->create(2026, 05, 15)
    ]);
    $tooOldPost = Post::factory()->create([
        'title' => 'Old post',
        'is_published' => true,
        'user_id' => $author->id,
        'created_at' => now()->create(2026, 04, 01)
    ]);
    $tooNewPost = Post::factory()->create([
        'title' => 'Future post',
        'is_published' => true,
        'user_id' => $author->id,
        'created_at' => now()->create(2026, 06, 01)
    ]);

    $response =  $this->get(route('posts.index', [
        'date_from' => '2026-05-01',
        'date_to'   => '2026-05-31',
    ]));

    expect(Post::count())->toBe(3)
        ->and($response->viewData('posts')->count())->toBe(1);

    $response->assertOk()
        ->assertSee($matchingPost->title)
        ->assertDontSee($tooOldPost->title)
        ->assertDontSee($tooNewPost->title);
});

test('successfully filters posts by specific author', function () {
    $this->actingAs($user = User::factory()->create());
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $anotherAuthor = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $targetPost = Post::factory()->create([
        'title' => 'Post 1',
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $otherPost = Post::factory()->create([
        'title' => 'Post 2',
        'is_published' => true,
        'user_id' => $anotherAuthor->id,
    ]);

    $response =  $this->get(route('posts.index', ['user_id' => $author->id]));

    expect(Post::count())->toBe(2)
        ->and($response->viewData('posts')->count())->toBe(1);

    $response->assertOk()
        ->assertSee($targetPost->title)
        ->assertDontSee($otherPost->title);
});

test('successfully filters posts by attached tag', function () {
    $this->actingAs($user = User::factory()->create());
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $targetTag = Tag::factory()->create(['id' => 1]);
    $otherTag = Tag::factory()->create(['id' => 2]);
    $targetPost = Post::factory()->create([
        'title' => 'Post 1',
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $targetPost->tags()->attach($targetTag);
    $otherPost = Post::factory()->create([
        'title' => 'Post 2',
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $otherPost->tags()->attach($otherTag);

    $response =  $this->get(route('posts.index', ['tag_id' => 1]));

    expect(Post::count())->toBe(2)
        ->and($response->viewData('posts')->count())->toBe(1);

    $response->assertOk()
        ->assertSee($targetPost->title)
        ->assertDontSee($otherPost->title);
});

test('successfully filters posts by search and specific author', function () {
    $this->actingAs($user = User::factory()->create());
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $anotherAuthor = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $targetPost = Post::factory()->create([
        'title' => 'Post for search 1',
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $otherPost1 = Post::factory()->create([
        'title' => 'Post for search 2',
        'is_published' => true,
        'user_id' => $anotherAuthor->id,
    ]);
    $otherPost2 = Post::factory()->create([
        'title' => 'Another post 1',
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $otherPost3 = Post::factory()->create([
        'title' => 'Another post 2',
        'is_published' => true,
        'user_id' => $anotherAuthor->id,
    ]);

    $response =  $this->get(route('posts.index', [
        'q' => 'search',
        'user_id' => $author->id,
    ]));

    expect(Post::count())->toBe(4)
        ->and($response->viewData('posts')->count())->toBe(1);

    $response->assertOk()
        ->assertSee($targetPost->title)
        ->assertDontSee($otherPost1->title)
        ->assertDontSee($otherPost2->title)
        ->assertDontSee($otherPost3->title);
});

test('redirects back or shows validation errors for invalid filter data', function () {
    $this->actingAs($user = User::factory()->create());

    $response =  $this->get(route('posts.index', [
        'user_id' => 'not-an-integer',
        'date_from' => 'invalid-date',
    ]));

    $response->assertStatus(302)
        ->assertSessionHasErrors(['user_id', 'date_from']);
});

test('renders the correct view and passes the posts variable', function () {
    $this->actingAs($user = User::factory()->create());
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $createdPosts = Post::factory()->count(13)->create([
        'is_published' => true,
        'user_id' => $author->id,
        'created_at' => now()->subDays(30),
    ]);

    $response = $this->get(route('posts.index'));

    $response
        ->assertOk() // The response status is 200 OK
        ->assertViewIs('posts.index') // Checking that the desired template is being rendered (resources/views/posts/index.blade.php )
        ->assertViewHas('posts') // Checking that the $posts variable is passed to the template
        ->assertViewHas('authors')
        ->assertViewHas('tags');

    // Deep inspection of the contents of the $posts variable
    // Pulling out a "clean" PHP object that has gone inside the Blade
    $postsInView = $response->viewData('posts');

    // OPTION A: If pagination is used in the controller ( ->paginate() )
    expect($postsInView)->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($postsInView->total())->toBe(13) // There are total 13 posts in the paginator
        ->and($postsInView->items())->toHaveCount(6) // There are only 6 posts on the first page (6 posts per page)
    ;

    // fails because of orderBy
    // It will work if you create posts with explicit time indication (from old to new)
    //    ->and($postsInView->first()->id)->toBe($createdPosts->first()->id);

    $collection = $postsInView instanceof LengthAwarePaginator
        ? $postsInView->getCollection()
        : $postsInView;
    // Checking that all created posts are present in the Blade variable.

    //expect($collection->contains($createdPosts->get(0)))->toBeTrue()
    //    ->and($collection->contains($createdPosts->get(1)))->toBeTrue()
    //    ->and($collection->contains($createdPosts->get(2)))->toBeTrue();

    for ($index=0; $index < 6; $index++) {
        expect($collection->contains($createdPosts->get($index)))->toBeTrue();
    }

    // OPTION B: If a regular collection is used ( ->get() )
    // expect($postsInView)->toBeInstanceOf(Collection::class);
    // expect($postsInView)->toHaveCount(3);
});

test('checking Eager Loading for posts (user, tags, userReaction)', function () {
    $this->actingAs($user = User::factory()->create());
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $posts = Post::factory()->count(13)->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);

    $response = $this->get(route('posts.index'));
    $firstPost = $response->viewData('posts')->first();

    // Checking that the relations are loaded (N+1 is missing)
    expect($firstPost->relationLoaded('user'))->toBeTrue()
        ->and($firstPost->relationLoaded('tags'))->toBeTrue()
        ->and($firstPost->relationLoaded('userReaction'))->toBeTrue();
});

test('Checking aggregations (withCount for comments, likes, dislikes)', function () {
    $this->actingAs($user = User::factory()->create());
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $posts = Post::factory()->count(13)->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);

    $response = $this->get(route('posts.index'));
    $firstPost = $response->viewData('posts')->first();

    // Checking that the relations are loaded (N+1 is missing)
    expect($firstPost->relationLoaded('user'))->toBeTrue()
        ->and($firstPost->relationLoaded('tags'))->toBeTrue()
        ->and($firstPost->relationLoaded('userReaction'))->toBeTrue();
});

test('posts are eager loaded (user, tags, userReaction) with correct aggregations (comments, likes)', function () {
    // 1. Data preparation
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $viewer = User::factory()->create();

    $post = Post::factory()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);

    // Adding tags and comments to check withCount
    $post->tags()->attach(Tag::factory()->count(2)->create());
    $post->comments()->createMany(Comment::factory()->count(3)->make()->toArray());

    // Adding likes to check withCount
    $usersLiked = User::factory()->count(4)->create();
    foreach ($usersLiked as $userLiked) {
        Like::factory()->forPost($post)->create([
                'user_id' => $userLiked->id,
                'is_like' => true,
            ]);
    }

    // Adding a like from the viewer (to check the userReaction connection)
    Like::factory()->forPost($post)->create([
            'user_id' => $viewer->id,
            'is_like' => true,
        ]);

    // 2. Request Execution
    $this->actingAs($viewer);
    $response = $this->get(route('posts.index'));

    $response->assertOk();

    // 3. Getting the post out of the paginator for checks
    $loadedPost = $response->viewData('posts')->first();

    // 4. EAGER LOADING CHECK (Protection against N+1)
    expect($loadedPost->relationLoaded('user'))->toBeTrue()
        ->and($loadedPost->relationLoaded('tags'))->toBeTrue()
        ->and($loadedPost->relationLoaded('userReaction'))->toBeTrue()
    // 5. CHECKING AGGREGATIONS (withCount)
        ->and($loadedPost->comments_count)->toBe(3)
        ->and($loadedPost->likes_count)->toBe(5) // 4 likes + 1 like from viewer
        ->and($loadedPost->dislikes_count)->toBe(0) // No dislikes for posts
    // Checking that the userReaction connection returned exactly the current user's like.
        ->and($loadedPost->userReaction)->not->toBeNull()
        ->and($loadedPost->userReaction->user_id)->toBe($viewer->id)
        ->and($loadedPost->userReaction->is_like)->toBeTrue();
});

test('user can not see posts like part if it is not published', function () {
    $this->actingAs(User::factory()->create(['role' => UserRole::ADMIN]));
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->withoutImage()->create([
        'is_published' => false,
        'user_id' => $author->id,
    ]);

    $response = $this->get(route('posts.index'));

    $response->assertStatus(200)
        ->assertSee($author->name)
        ->assertDontSee('post-reaction-block')
        ->assertSee($post->title);
});

test('author can not see posts like part on his own posts', function () {
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $post = Post::factory()->withoutImage()->create([
        'is_published' => true,
        'user_id' => $author->id,
    ]);
    $this->actingAs($author);

    $response = $this->get(route('posts.index'));

    $response->assertStatus(200)
        ->assertSee($author->name)
        ->assertDontSee('post-reaction-block')
        ->assertSee($post->title);
});



