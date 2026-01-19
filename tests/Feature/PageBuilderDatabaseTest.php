<?php

use App\Enums\BlockType;
use App\Models\Page;
use App\Models\PageBlock;
use App\Models\PageVersion;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('page can have multiple versions', function () {
    $page = Page::factory()->create(['version' => 1]);

    PageVersion::factory()->count(3)->create([
        'page_id' => $page->id,
    ]);

    expect($page->versions)->toHaveCount(3);
});

test('page can create a new version snapshot', function () {
    $user = User::factory()->create();
    $page = Page::factory()->create([
        'version' => 1,
        'content' => ['text' => 'Original Content'],
        'builder_content' => [['type' => 'text', 'content' => 'Original Builder']],
        'last_edited_by' => $user->id,
    ]);

    $version = $page->createVersion();

    expect($version)->toBeInstanceOf(PageVersion::class)
        ->and($version->version)->toBe(2)
        ->and($version->content)->toBe(['text' => 'Original Content'])
        ->and($version->builder_content)->toBe([['type' => 'text', 'content' => 'Original Builder']])
        ->and($version->created_by)->toBe($user->id);

    expect($page->fresh()->version)->toBe(2);
});

test('page can restore to a specific version', function () {
    $page = Page::factory()->create([
        'version' => 2,
        'content' => ['text' => 'Current Content'],
    ]);

    $oldVersion = PageVersion::factory()->create([
        'page_id' => $page->id,
        'version' => 1,
        'content' => ['text' => 'Old Content'],
        'builder_content' => [['type' => 'text', 'content' => 'Old Builder']],
    ]);

    $success = $page->restoreVersion($oldVersion->id);

    expect($success)->toBeTrue();

    $page->refresh();
    expect($page->content)->toBe(['text' => 'Old Content'])
        ->and($page->builder_content)->toBe([['type' => 'text', 'content' => 'Old Builder']])
        ->and($page->version)->toBe(3); // Restoration increments version again
});

test('page block supports meta, css_class and anchor_id', function () {
    $page = Page::factory()->create();

    $block = PageBlock::create([
        'page_id' => $page->id,
        'type' => BlockType::Text,
        'content' => ['text' => 'Hello'],
        'settings' => ['color' => 'red'],
        'meta' => ['seo_title' => 'Custom Block Title'],
        'css_class' => 'custom-class p-4',
        'anchor_id' => 'section-1',
        'order' => 1,
    ]);

    $block->refresh();

    expect($block->meta)->toBe(['seo_title' => 'Custom Block Title'])
        ->and($block->css_class)->toBe('custom-class p-4')
        ->and($block->anchor_id)->toBe('section-1');
});

test('page block meta is cast to array', function () {
    $block = PageBlock::factory()->create([
        'meta' => ['foo' => 'bar'],
    ]);

    expect($block->meta)->toBeArray()
        ->and($block->meta)->toBe(['foo' => 'bar']);
});
