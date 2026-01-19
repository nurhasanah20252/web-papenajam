<?php

use App\Enums\PageStatus;
use App\Models\Page;
use App\Models\User;

test('page has fillable attributes', function () {
    $page = Page::factory()->make([
        'title' => 'Test Page',
        'slug' => 'test-page',
        'content' => ['content' => 'Test content'],
        'status' => PageStatus::Published,
    ]);

    expect($page->title)->toBe('Test Page')
        ->and($page->slug)->toBe('test-page')
        ->and($page->status)->toBe(PageStatus::Published);
});

test('page belongs to author', function () {
    $user = User::factory()->create();
    $page = Page::factory()->create(['author_id' => $user->id]);

    expect($page->author)->toBeInstanceOf(User::class)
        ->and($page->author->id)->toBe($user->id);
});

test('page has published scope', function () {
    Page::factory()->create(['status' => PageStatus::Draft]);
    Page::factory()->create([
        'status' => PageStatus::Published,
        'published_at' => now()->subDay(),
    ]);
    Page::factory()->create(['status' => PageStatus::Archived]);

    $publishedPages = Page::published()->get();

    expect($publishedPages)->toHaveCount(1)
        ->and($publishedPages->first()->status)->toBe(PageStatus::Published);
});

test('page has draft scope', function () {
    Page::factory()->create(['status' => PageStatus::Draft]);
    Page::factory()->create([
        'status' => PageStatus::Published,
        'published_at' => now()->subDay(),
    ]);
    Page::factory()->create(['status' => PageStatus::Draft]);

    $draftPages = Page::draft()->get();

    expect($draftPages)->toHaveCount(2);
});

test('page casts status to enum', function () {
    $page = Page::factory()->create(['status' => PageStatus::Published]);

    expect($page->status)->toBeInstanceOf(PageStatus::class)
        ->and($page->status->value)->toBe('published');
});

test('page checks if is published', function () {
    $publishedPage = Page::factory()->create([
        'status' => PageStatus::Published,
        'published_at' => now()->subDay(),
    ]);
    $draftPage = Page::factory()->create(['status' => PageStatus::Draft]);

    expect($publishedPage->isPublished())->toBeTrue()
        ->and($draftPage->isPublished())->toBeFalse();
});

test('page checks if is draft', function () {
    $publishedPage = Page::factory()->create([
        'status' => PageStatus::Published,
        'published_at' => now()->subDay(),
    ]);
    $draftPage = Page::factory()->create(['status' => PageStatus::Draft]);

    expect($draftPage->isDraft())->toBeTrue()
        ->and($publishedPage->isDraft())->toBeFalse();
});

test('page increments views', function () {
    $page = Page::factory()->create(['view_count' => 0]);

    $page->incrementViews();

    expect($page->fresh()->view_count)->toBe(1);
});

test('page has by type scope', function () {
    Page::factory()->create(['page_type' => \App\Enums\PageType::Static]);
    Page::factory()->create(['page_type' => \App\Enums\PageType::Dynamic]);
    Page::factory()->create(['page_type' => \App\Enums\PageType::Static]);

    $staticPages = Page::byType(\App\Enums\PageType::Static)->get();

    expect($staticPages)->toHaveCount(2);
});

test('page soft deletes', function () {
    $page = Page::factory()->create();

    $page->delete();

    expect(Page::find($page->id))->toBeNull()
        ->and(Page::withTrashed()->find($page->id))->not->toBeNull();
});

test('page has meta data casting', function () {
    $page = Page::factory()->create([
        'meta' => [
            'description' => 'Test Description',
            'keywords' => ['test', 'page'],
        ],
    ]);

    expect($page->meta)->toBeArray()
        ->and($page->meta['description'])->toBe('Test Description')
        ->and($page->meta['keywords'])->toBe(['test', 'page']);
});

test('page has content array casting', function () {
    $page = Page::factory()->create([
        'content' => [
            'type' => 'text',
            'content' => 'Test content',
        ],
    ]);

    expect($page->content)->toBeArray()
        ->and($page->content['type'])->toBe('text');
});

test('page has builder content array casting', function () {
    $page = Page::factory()->create([
        'builder_content' => [
            ['type' => 'heading', 'content' => 'Title'],
            ['type' => 'text', 'content' => 'Content'],
        ],
    ]);

    expect($page->builder_content)->toBeArray()
        ->and($page->builder_content)->toHaveCount(2);
});

test('page increments version', function () {
    $page = Page::factory()->create(['version' => 1]);

    $page->incrementVersion();

    expect($page->fresh()->version)->toBe(2);
});

test('page checks if builder is enabled', function () {
    $pageWithBuilder = Page::factory()->create(['is_builder_enabled' => true]);
    $pageWithoutBuilder = Page::factory()->create(['is_builder_enabled' => false]);

    expect($pageWithBuilder->isBuilderEnabled())->toBeTrue()
        ->and($pageWithoutBuilder->isBuilderEnabled())->toBeFalse();
});

test('page gets url', function () {
    $page = Page::factory()->create(['slug' => 'test-page']);

    expect($page->getUrl())->toBe('/test-page');
});

test('page gets meta description', function () {
    $pageWithMeta = Page::factory()->create([
        'excerpt' => 'Default excerpt',
        'meta' => ['description' => 'Custom meta description'],
    ]);

    $pageWithoutMeta = Page::factory()->create([
        'excerpt' => 'Default excerpt',
        'meta' => [],
    ]);

    expect($pageWithMeta->getMetaDescription())->toBe('Custom meta description')
        ->and($pageWithoutMeta->getMetaDescription())->toBe('Default excerpt');
});

test('page gets meta keywords', function () {
    $page = Page::factory()->create([
        'meta' => ['keywords' => ['test', 'page', 'seo']],
    ]);

    expect($page->getMetaKeywords())->toBe(['test', 'page', 'seo']);
});
