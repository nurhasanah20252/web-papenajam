<?php

use App\Enums\NewsStatus;
use App\Models\Category;
use App\Models\News;
use App\Models\User;

test('news has fillable attributes', function () {
    $news = News::factory()->make([
        'title' => 'Test News',
        'slug' => 'test-news',
        'content' => 'Test content',
        'status' => NewsStatus::Published,
    ]);

    expect($news->title)->toBe('Test News')
        ->and($news->slug)->toBe('test-news')
        ->and($news->content)->toBe('Test content')
        ->and($news->status)->toBe(NewsStatus::Published);
});

test('news belongs to author', function () {
    $user = User::factory()->create();
    $news = News::factory()->create(['author_id' => $user->id]);

    expect($news->author)->toBeInstanceOf(User::class)
        ->and($news->author->id)->toBe($user->id);
});

test('news belongs to category', function () {
    $category = Category::factory()->create();
    $news = News::factory()->create(['category_id' => $category->id]);

    expect($news->category)->toBeInstanceOf(Category::class)
        ->and($news->category->id)->toBe($category->id);
});

test('news casts published_at to datetime', function () {
    $news = News::factory()->create([
        'published_at' => '2024-01-01 12:00:00',
    ]);

    expect($news->published_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class)
        ->and($news->published_at->format('Y-m-d H:i:s'))->toBe('2024-01-01 12:00:00');
});

test('news has published scope', function () {
    News::factory()->create(['status' => NewsStatus::Draft]);
    News::factory()->create(['status' => NewsStatus::Published, 'published_at' => now()]);
    News::factory()->create(['status' => NewsStatus::Archived]);

    $publishedNews = News::published()->get();

    expect($publishedNews)->toHaveCount(1)
        ->and($publishedNews->first()->status)->toBe(NewsStatus::Published);
});

test('news has featured scope', function () {
    News::factory()->create(['is_featured' => false]);
    News::factory()->create(['is_featured' => true]);
    News::factory()->create(['is_featured' => false]);

    $featuredNews = News::featured()->get();

    expect($featuredNews)->toHaveCount(1);
});

test('news has latest scope', function () {
    News::factory()->create(['published_at' => now()->subDays(2)]);
    News::factory()->create(['published_at' => now()->subDays(1)]);
    News::factory()->create(['published_at' => now()]);

    $latestNews = News::latest()->get();

    expect($latestNews->first()->published_at->greaterThan($latestNews->last()->published_at))->toBeTrue();
});

test('news has trending scope', function () {
    News::factory()->create(['view_count' => 10]);
    News::factory()->create(['view_count' => 100]);
    News::factory()->create(['view_count' => 50]);

    $trendingNews = News::trending()->get();

    expect($trendingNews->first()->view_count)->toBe(100);
});

test('news checks if is published', function () {
    $publishedNews = News::factory()->create([
        'status' => NewsStatus::Published,
        'published_at' => now(),
    ]);
    $draftNews = News::factory()->create(['status' => NewsStatus::Draft]);

    expect($publishedNews->isPublished())->toBeTrue()
        ->and($draftNews->isPublished())->toBeFalse();
});

test('news increments view count', function () {
    $news = News::factory()->create(['view_count' => 0]);

    $news->incrementViewCount();

    expect($news->fresh()->view_count)->toBe(1);
});

test('news has excerpt method', function () {
    $news = News::factory()->create([
        'content' => 'This is a long content that should be truncated when we call the excerpt method.',
    ]);

    $excerpt = $news->getExcerpt(50);

    expect(strlen($excerpt))->toBeLessThanOrEqual(53); // 50 + '...'
});

test('news has featured image url accessor', function () {
    $news = News::factory()->create(['featured_image' => 'news/test.jpg']);

    expect($news->featured_image_url)->toContain('news/test.jpg');
});

test('news soft deletes', function () {
    $news = News::factory()->create();

    $news->delete();

    expect(News::find($news->id))->toBeNull()
        ->and(News::withTrashed()->find($news->id))->not->toBeNull();
});

test('news has meta data casting', function () {
    $news = News::factory()->create([
        'meta_data' => [
            'seo_title' => 'SEO Title',
            'seo_description' => 'SEO Description',
        ],
    ]);

    expect($news->meta_data)->toBeArray()
        ->and($news->meta_data['seo_title'])->toBe('SEO Title');
});
