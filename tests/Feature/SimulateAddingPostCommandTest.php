<?php

use App\Enum\UserRole;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
//use Illuminate\Support\Facades\Log;
//use Illuminate\Log\Logger;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
});

test('successful end‑to‑end news import with image and tag linking', function () {
    // Create author and tags
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);
    $tags = Tag::factory()->count(5)->create();

    // Simulate main page HTML
    $mainPageHtml = <<<'HTML'
        <div class="loop-card__content">
            <h3 class="loop-card__title">
                <a class="loop-card__title-link" href="https://techcrunch.com/test-article-path">Unique title 2026.</a>
            </h3>
        </div>
    HTML;

    // Simulate inner page HTML (article content + image)
    $innerPageHtml = <<<'HTML'
        <div class="entry-content wp-block-post-content">
            <p>The first paragraph of the news.</p>
        </div>
        <figure class="wp-block-post-featured-image">
            <img src="https://techcrunch.com/assets/hero-image.jpg" alt="Hero">
        </figure>
    HTML;

    // Simulating HTTP responses (stub)
    Http::fake([
        'https://techcrunch.com' => Http::response($mainPageHtml, 200), // main page
        'https://techcrunch.com/test-article-path' => Http::response($innerPageHtml, 200), // article body
        'https://techcrunch.com/assets/hero-image.jpg' => Http::response('fake-binary-image', 200), // image download
    ]);

    // Run the command with adding tags, but without specifying userId
    $this->artisan('simulate:adding-post', [
        'newsNum' => 1,
        '--addTags' => true,
    ])
        ->expectsOutput('News added successfully')
        ->assertExitCode(0);

    // Check if the post was created and stored to database
    $post = Post::query()->where('title', 'Unique title 2026.')->first();
    expect($post)->not->toBeNull()
        ->and($post->user_id)->toBe($author->id) // Checking that a random author has been selected.
        ->and($post->is_published)->toBeTrue()
        // Check if the image was uploaded and stored on disk
        ->and($post->image)->toStartWith('posts/')->toEndWith('.jpg');
    Storage::disk('public')->assertExists($post->image);

    // Check if the tags were linked (addTags method)
    expect($post->tags)->not->toBeEmpty();
});

test('returns an error if there are no authors in the database when userId = 0.', function () {
    // Create user, but not author (for example, admin)
    User::factory()->create(['role' => UserRole::ADMIN]);

    // Simulate main page HTML
    $mainPageHtml = <<<'HTML'
        <div class="loop-card__content">
            <h3 class="loop-card__title">
                <a class="loop-card__title-link" href="https://techcrunch.com/test-article-path">Unique title 2026.</a>
            </h3>
        </div>
    HTML;

    // Simulate inner page HTML (article content + image)
    $innerPageHtml = <<<'HTML'
        <div class="entry-content wp-block-post-content">
            <p>The first paragraph of the news.</p>
        </div>
        <figure class="wp-block-post-featured-image">
            <img src="https://techcrunch.com/assets/hero-image.jpg" alt="Hero">
        </figure>
    HTML;

    Http::fake([
        'https://techcrunch.com' => Http::response($mainPageHtml, 200), // main page
        'https://techcrunch.com/test-article-path' => Http::response($innerPageHtml, 200), // article body
        'https://techcrunch.com/assets/hero-image.jpg' => Http::response('fake-binary-image', 200), // image download
    ]);

    // Run the command without userId, no authors found
    $this->artisan('simulate:adding-post', ['newsNum' => 1])
        ->expectsOutput('No authors found')
        ->assertExitCode(1);
});

test('skips downloading the image with an invalid extension', function () {
    // Create author
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);

    // Simulate main page HTML
    $mainPageHtml = <<<'HTML'
        <div class="loop-card__content">
            <h3 class="loop-card__title">
                <a class="loop-card__title-link" href="https://techcrunch.com/test-article-path">Unique title 2026.</a>
            </h3>
        </div>
    HTML;

    // Simulate inner page HTML (article content + image)
    // An image with an unsupported .svg or .pdf extension.
    $innerPageHtml = <<<'HTML'
        <div class="entry-content wp-block-post-content">
            <p>The first paragraph of the news.</p>
        </div>
        <figure class="wp-block-post-featured-image">
            <img src="https://techcrunch.com/assets/hero-image.svg" alt="Hero">
        </figure>
    HTML;

    Http::fake([
        'https://techcrunch.com' => Http::response($mainPageHtml, 200), // main page
        'https://techcrunch.com/test-article-path' => Http::response($innerPageHtml, 200), // article body
        'https://techcrunch.com/assets/hero-image.jpg' => Http::response('fake-binary-image', 200), // image download
    ]);

    $this->artisan('simulate:adding-post', ['newsNum' => 1])
        ->expectsOutput('News added successfully')
        ->assertExitCode(0);

    $post = Post::query()->where('title', 'Unique title 2026.')->first();
    // The post should be created, but the image field should remain empty/null.
    expect($post->image)->toBeNull()
        ->and(Storage::disk('public')->files('posts'))->toBeEmpty(); // The disk should remain empty.
});

test('returns an error if the news is not found on the page', function () {
    // Create author
    $author = User::factory()->create([
        'role' => UserRole::AUTHOR
    ]);

    // Empty TechCrunch page (selectors won’t match)
    Http::fake([
        'https://techcrunch.com' => Http::response('<div>No news here</div>', 200),
    ]);

    $this->artisan('simulate:adding-post', ['newsNum' => 1])
        ->expectsOutput('No news found')
        ->assertExitCode(1);
});

