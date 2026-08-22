<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

readonly class AddingPost
{
    public function beforeImportNews(int $userId): array
    {
        $messageBefore = 'Checking parameters before import news: userId = ' . $userId . '. ';

        if ($userId) {
            $user = User::query()
                ->where('id', $userId)
                ->where('role', 'author')
                ->first();

            if (!$user) {
                return ['status' => 'error', 'message' => $messageBefore . 'User not found or not author.', 'user_id' => null];
            }

            $userId = (int)$user->id;
        } else {
            $userId = 0;
        }

        return ['status' => 'success', 'message' => $messageBefore . 'User OK.', 'user_id' => $userId];
    }

    public function importNews(int $userId, string $logChannel, int $newsNum = 1, bool $addTags = false, bool $dryRun = false): array
    {
        // 1. Getting the HTML code of the page
        $url = 'https://techcrunch.com';
        $response = Http::get($url);

        if ($response->failed()) {
            Log::channel($logChannel)->error('Failed to get HTML code: ' . $response->status());
            return ['status' => 'error', 'message' => 'Failed to get HTML code'];
        }

        $html = $response->body();

        Log::channel($logChannel)->info('Start getting headers');

        // 2. Initialize DomCrawler
        $crawler = new Crawler($html);

        $news = [];

        // 3. Search for articles and collect data
        $crawler->filter('div.loop-card__content h3.loop-card__title > a.loop-card__title-link') // get all headers with specific selectors
        ->each(function (Crawler $node, $i) use (&$news, $newsNum) {
            if (count($news) >= $newsNum) {
                return;
            }
            $news[] = [
                'title' => $node->text(), // news title
                'link' => $node->attr('href'), // news href
            ];
        });

        //Filter out empty results if some blocks are not parsed.
        $news = array_filter($news, fn($item) => !empty($item['title']));

        Log::channel($logChannel)->info(sprintf('Start getting texts for %d headers', count($news)));

        foreach ($news as $key => &$new) {
            $title = $new['title'];
            $isPostExists = Post::query()->where('title', $title)->exists();
            if ($isPostExists) {
                unset($news[$key]);
                continue;
            }

            $link = str_starts_with($new['link'], 'http')
                ? $new['link']
                : $url.$new['link'];
            $innerResponse = Http::get($link);
            $crawlerInner = new Crawler($innerResponse->body());

            $containerInner = $crawlerInner->filter('div.entry-content.wp-block-post-content'); // get content

            if ($containerInner->count() === 0) {
                unset($news[$key]);
                continue;
            }

            //------------------------------------------------------------------------------------------------------
            // Without cleaning up the excess (videos, scripts...) and without splitting the text into paragraphs
            // $new['content'] = $containerInner->text();
            //------------------------------------------------------------------------------------------------------

            // --- Collecting text with saving paragraphs (WITHOUT VIDEO) ---
            // Select all <p> and <li> inside the article, BUT EXCLUDE those that are inside the video blocks (wp-block-embed jw-player-inline-promo wp-block-techcrunch-jw-player-embed)
            $paragraphs = $crawlerInner->filter('div.entry-content.wp-block-post-content p, div.entry-content.wp-block-post-content li')
                ->each(function (Crawler $node) {
                    // Additional check for technical tags
                    if (in_array($node->nodeName(), ['script', 'style', 'iframe', 'video'])) {
                        return null;
                    }

                    // Check whether the paragraph is INSIDE the video or promo block.
                    // The closest() method searches for the first suitable tag up the tree.
                    $classesToIgnore = [
                        '.wp-block-embed',
                        '.jetpack-video-wrapper',
                        '.jw-player-inline-promo',
                        '.wp-block-techcrunch-jw-player-embed'
                    ];
                    foreach ($classesToIgnore as $class) {
                        if ($node->closest($class) !== null) {
                            return null; // If the parent is found, it is a garbage block, skip it.
                        }
                    }

                    return trim($node->text());
                });

            // Clearing of empty lines
            $paragraphs = array_filter($paragraphs);

            if (!empty($paragraphs)) {
                // Option A: If necessary to clean text, but divided into paragraphs (double line breaks)
                $new['content'] = implode("\n\n", $paragraphs);

                // Option B: If necessary to save HTML paragraphs <p>text</p> for output on the site via {!! $post->content !!}
                // $new['content'] = implode('', array_map(fn($p) => "<p>{$p}</p>", $paragraphs));
            } else {
                unset($news[$key]);
                continue;
            }

            $containerInnerImage = $crawlerInner->filter('figure.wp-block-post-featured-image'); // get image src
            if ($containerInnerImage->count()) {
                $new['imageSrc'] = $containerInnerImage->filter('img')->count() ? $containerInnerImage->filter('img')->attr('src') : '';
            }

            unset($new['link']);
        }
        unset($new); // just in case

        // 4. Add news to database
        if (!$dryRun) {
            $saveDBResult = $this->saveNewsToDatabase($logChannel, $news, $userId, $addTags);
        } else {
            $saveDBResult = ['status' => 'success', 'message' => 'A dry run has been made'];
        }

        Log::channel($logChannel)->info('End importing news');

        return $saveDBResult;
    }

    private function saveNewsToDatabase(string $logChannel, array $news, int $userId, bool $addTags): array
    {
        if (!empty($news)) {
            Log::channel($logChannel)->info('Start save news to Database');

            $newsCount = count($news);
            if (!$userId) {
                $userIds = User::query()->inRandomOrder()->where('role', 'author')->pluck('id');

                // Getting the required number of author IDs (corresponding to the number of news)
                // with repeats if the number of authors is less than the number of news
                $randomIds = $userIds->count() > 0
                    ? collect(range(1, $newsCount))->map(fn() => $userIds->random())->all()
                    : [];
            } else {
                $randomIds = [$userId];
            }

            if (empty($randomIds)) {
                Log::channel($logChannel)->error('No authors found');
                return ['status' => 'error', 'message' => 'No authors found'];
            }

            foreach ($news as $key => $new) {
                $src = $new['imageSrc'] ?? '';
                unset($new['imageSrc']);

                $new['user_id'] = $userId ? $randomIds[0] : $randomIds[$key];
                $new['is_published'] = true;

                $post = Post::query()->create($new);

                if (!empty($src)) {
                    $responseImage = Http::get($src);
                    if ($responseImage->successful()) {
                        // Extracting the extension
                        $extension = pathinfo(parse_url($src, PHP_URL_PATH), PATHINFO_EXTENSION);
                        $extension = strtolower($extension);

                        // Checking for compliance with the allowed extensions
                        if (!in_array($extension, ['jpeg', 'jpg', 'png', 'gif', 'webp'])) {
                            continue;
                        }

                        $tempName = Str::random(12) . '.' . $extension;
                        Storage::disk('public')->put('posts/' . $tempName, $responseImage->body());

                        $post->image = 'posts/' . $tempName;
                    }
                }

                if ($addTags) {
                    $this->addTags($post);
                }

                $post->save();
            }
        } else {
            Log::channel($logChannel)->error('No news found');
            return ['status' => 'error', 'message' => 'No news found'];
        }

        Log::channel($logChannel)->info('End save news to Database');

        return ['status' => 'success', 'message' => 'News added successfully'];
    }

    private function addTags(Post $post): void
    {
        $tagIds = Tag::query()->pluck('id')->toArray();

        $tagIdsNumber = count($tagIds);
        if ($tagIdsNumber) {
            $tagNumber = mt_rand(2, 7);

            if ($tagIdsNumber <= $tagNumber) {
                $postTagIds = $tagIds;
            } else {
                $randomKeys = array_rand($tagIds, $tagNumber);
                $postTagIds = array_map(fn($key) => $tagIds[$key], (array) $randomKeys);
            }

            $post->tags()->sync($postTagIds);
        }
    }
}
