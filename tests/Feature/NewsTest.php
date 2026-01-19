<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\News;

use function Pest\Laravel\get;

beforeEach(function () {
    Category::factory()->create(['type' => 'news', 'name' => 'Berita', 'slug' => 'berita']);
    Category::factory()->create(['type' => 'news', 'name' => 'Pengumuman', 'slug' => 'pengumuman']);
});

test('news index page loads successfully', function () {
    News::factory()->published()->count(3)->create();

    get('/news')
        ->assertStatus(200)
        ->assertInertia(function ($page) {
            expect($page->component())->toBe('news/index')
                ->and($page->props)->toHaveKeys([
                    'news',
                    'featuredNews',
                    'categories',
                    'filters',
                ]);
        });
});

test('news index paginates results', function () {
    News::factory()->published()->count(15)->create();

    get('/news')
        ->assertStatus(200)
        ->assertInertia(function ($page) {
            expect($page->props['news']['data'])->toHaveCount(12)
                ->and($page->props['news']['total'])->toBe(15);
        });
});

test('news index filters by category', function () {
    $category = Category::where('slug', 'berita')->first();

    News::factory()->published()->count(3)->create([
        'category_id' => $category->id,
        'title' => 'Berita Article',
    ]);

    News::factory()->published()->count(2)->create([
        'category_id' => Category::where('slug', 'pengumuman')->first()->id,
        'title' => 'Pengumuman Article',
    ]);

    get('/news?category=berita')
        ->assertStatus(200)
        ->assertInertia(function ($page) {
            expect($page->props['news']['data'])->each->title->toContain('Berita');
        });
});

test('news index searches by title', function () {
    News::factory()->published()->create([
        'title' => 'Test Search Term',
        'excerpt' => 'This is about testing',
    ]);

    News::factory()->published()->create([
        'title' => 'Other Article',
        'excerpt' => 'Completely different',
    ]);

    get('/news?search=Test')
        ->assertStatus(200)
        ->assertInertia(function ($page) {
            expect($page->props['news']['data'])->toHaveCount(1)
                ->and($page->props['news']['data'][0]['title'])->toBe('Test Search Term');
        });
});

test('news show page displays published article', function () {
    $news = News::factory()->published()->create([
        'title' => 'Test Article',
        'slug' => 'test-article',
        'content' => '<p>This is test content</p>',
    ]);

    get("/news/{$news->slug}")
        ->assertStatus(200)
        ->assertInertia(function ($page) {
            expect($page->component())->toBe('news/show')
                ->and($page->props)->toHaveKeys([
                    'news',
                    'relatedNews',
                    'latestNews',
                ]);
        });
});

test('news show page increments view count', function () {
    $news = News::factory()->published()->create([
        'slug' => 'test-article',
        'views_count' => 100,
    ]);

    get("/news/{$news->slug}");

    expect($news->fresh()->views_count)->toBe(101);
});

test('news show page returns 404 for unpublished articles', function () {
    $news = News::factory()->create([
        'slug' => 'draft-article',
        'status' => 'draft',
    ]);

    get("/news/{$news->slug}")->assertStatus(404);
});

test('news show page returns 404 for scheduled articles', function () {
    $news = News::factory()->create([
        'slug' => 'scheduled-article',
        'status' => 'published',
        'published_at' => now()->addDay(),
    ]);

    get("/news/{$news->slug}")->assertStatus(404);
});

test('news category page filters by category', function () {
    $category = Category::where('slug', 'berita')->first();

    News::factory()->published()->count(3)->create([
        'category_id' => $category->id,
    ]);

    get("/news/category/{$category->slug}")
        ->assertStatus(200)
        ->assertInertia(function ($page) use ($category) {
            expect($page->props['filters']['category'])->toBe($category->slug);
        });
});

test('news tag page filters by tag', function () {
    News::factory()->published()->create([
        'title' => 'Article with Tag',
        'tags' => ['test-tag', 'another-tag'],
    ]);

    News::factory()->published()->create([
        'title' => 'Article without Tag',
        'tags' => ['different-tag'],
    ]);

    get('/news/tag/test-tag')
        ->assertStatus(200)
        ->assertInertia(function ($page) {
            expect($page->props['filters']['tag'])->toBe('test-tag')
                ->and($page->props['news']['data'])->toHaveCount(1)
                ->and($page->props['news']['data'][0]['title'])->toBe('Article with Tag');
        });
});

test('rss feed generates valid xml', function () {
    News::factory()->published()->count(3)->create();

    get('/news/rss')
        ->assertStatus(200)
        ->assertHeader('content-type', 'application/xml+xml; charset=UTF-8')
        ->assertSee('<rss version="2.0"', false)
        ->assertSee('<channel>', false)
        ->assertSee('</channel>', false)
        ->assertSee('</rss>', false);
});

test('rss feed includes published news items', function () {
    $news = News::factory()->published()->create([
        'title' => 'RSS Test Article',
        'slug' => 'rss-test-article',
    ]);

    get('/news/rss')
        ->assertStatus(200)
        ->assertSee($news->title)
        ->assertSee($news->slug);
});

test('rss feed does not include draft news items', function () {
    News::factory()->create([
        'title' => 'Draft Article',
        'status' => 'draft',
    ]);

    get('/news/rss')
        ->assertStatus(200)
        ->assertDontSee('Draft Article');
});

test('featured news are displayed on index page', function () {
    News::factory()->published()->featured()->count(3)->create([
        'title' => 'Featured Article',
    ]);

    get('/news')
        ->assertStatus(200)
        ->assertInertia(function ($page) {
            expect($page->props['featuredNews'])->toHaveCount(3)
                ->and($page->props['featuredNews'])->each->is_featured->toBeTrue();
        });
});

test('news with category displays related news', function () {
    $category = Category::where('slug', 'berita')->first();

    $news = News::factory()->published()->create([
        'category_id' => $category->id,
    ]);

    News::factory()->published()->count(3)->create([
        'category_id' => $category->id,
    ]);

    get("/news/{$news->slug}")
        ->assertStatus(200)
        ->assertInertia(function ($page) {
            expect($page->props['relatedNews'])->toHaveCount(3);
        });
});
