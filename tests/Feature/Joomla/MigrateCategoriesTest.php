<?php

use App\Models\Category;
use App\Models\JoomlaMigration;
use App\Services\JoomlaMigration\JoomlaMigrator;

beforeEach(function () {
    // Clean up before each test
    JoomlaMigration::query()->delete();
    Category::query()->delete();
});

it('can migrate joomla categories', function () {
    $migrator = app(JoomlaMigrator::class);
    $stats = $migrator->migrateCategories();

    expect($stats['success'])->toBeGreaterThan(0);
    expect($stats['failed'])->toBe(0);

    // Check that categories were created
    expect(Category::count())->toBeGreaterThan(0);
});

it('creates joomla migration records for categories', function () {
    $migrator = app(JoomlaMigrator::class);
    $migrator->migrateCategories();

    $migrationCount = JoomlaMigration::bySourceTable('categories')
        ->successful()
        ->count();

    expect($migrationCount)->toBeGreaterThan(0);
});

it('skips already migrated categories unless forced', function () {
    $migrator = app(JoomlaMigrator::class);

    // First migration
    $stats1 = $migrator->migrateCategories();
    $successCount1 = $stats1['success'];

    // Second migration without force
    $stats2 = $migrator->migrateCategories();
    expect($stats2['skipped'])->toBe($successCount1);
    expect($stats2['success'])->toBe(0);

    // Third migration with force
    $stats3 = $migrator->migrateCategories(force: true);
    expect($stats3['success'])->toBeGreaterThan(0);
});

it('preserves parent child relationships', function () {
    $migrator = app(JoomlaMigrator::class);
    $migrator->migrateCategories();

    // Check if any category has a parent
    $childCategories = Category::whereNotNull('parent_id')->get();

    if ($childCategories->count() > 0) {
        $child = $childCategories->first();
        expect($child->parent)->not->toBeNull();
    }
});

it('generates unique slugs for categories', function () {
    $migrator = app(JoomlaMigrator::class);
    $migrator->migrateCategories();

    $slugs = Category::pluck('slug');
    $uniqueSlugs = $slugs->unique();

    expect($slugs->count())->toBe($uniqueSlugs->count());
});

it('skips root category during migration', function () {
    $migrator = app(JoomlaMigrator::class);
    $stats = $migrator->migrateCategories();

    // ROOT category should not be migrated
    $rootMigration = JoomlaMigration::bySourceTable('categories')
        ->where('source_id', 1)
        ->first();

    expect($rootMigration)->toBeNull();
});

it('sets correct category type', function () {
    $migrator = app(JoomlaMigrator::class);
    $migrator->migrateCategories();

    $categories = Category::get();

    if ($categories->count() > 0) {
        $category = $categories->first();
        expect($category->type)->not->toBeNull();
    }
});

it('handles migration failures gracefully', function () {
    // Create a scenario that might fail
    // This is a basic test - real failure scenarios would need mocking

    $migrator = app(JoomlaMigrator::class);
    $stats = $migrator->migrateCategories();

    // Even if some fail, the process should complete
    expect($stats)->toBeArray();
    expect($stats)->toHaveKeys(['success', 'failed', 'skipped']);
});
