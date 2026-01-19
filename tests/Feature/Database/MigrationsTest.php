<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('all migrations run successfully', function () {
    // This test ensures all migrations can run without errors
    $this->expectNotToPerformAssertions();

    // The RefreshDatabase trait already runs migrations
    // If we get here without exceptions, migrations succeeded
});

test('users table has correct columns', function () {
    $user = \App\Models\User::factory()->create();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'role' => $user->role->value,
    ]);

    // Verify columns exist
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('users');
    expect($columns)->toContain('name');
    expect($columns)->toContain('email');
    expect($columns)->toContain('password');
    expect($columns)->toContain('role');
    expect($columns)->toContain('permissions');
    expect($columns)->toContain('last_login_at');
    expect($columns)->toContain('profile_completed');
});

test('pages table has correct columns', function () {
    $page = \App\Models\Page::factory()->create();

    $this->assertDatabaseHas('pages', [
        'id' => $page->id,
        'title' => $page->title,
        'slug' => $page->slug,
    ]);

    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('pages');
    expect($columns)->toContain('title');
    expect($columns)->toContain('slug');
    expect($columns)->toContain('content');
    expect($columns)->toContain('page_type');
    expect($columns)->toContain('status');
    expect($columns)->toContain('author_id');
    expect($columns)->toContain('meta');
});

test('menus table has correct columns', function () {
    $menu = \App\Models\Menu::factory()->create();

    $this->assertDatabaseHas('menus', [
        'id' => $menu->id,
        'name' => $menu->name,
        'location' => $menu->location->value,
    ]);

    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('menus');
    expect($columns)->toContain('name');
    expect($columns)->toContain('location');
    expect($columns)->toContain('max_depth');
});

test('menu_items table has correct columns', function () {
    $menuItem = \App\Models\MenuItem::factory()->create();

    $this->assertDatabaseHas('menu_items', [
        'id' => $menuItem->id,
        'label' => $menuItem->label,
        'menu_id' => $menuItem->menu_id,
    ]);

    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('menu_items');
    expect($columns)->toContain('label');
    expect($columns)->toContain('menu_id');
    expect($columns)->toContain('parent_id');
    expect($columns)->toContain('url_type');
    expect($columns)->toContain('url');
    expect($columns)->toContain('route_name');
    expect($columns)->toContain('order');
});

test('news table has correct columns', function () {
    $news = \App\Models\News::factory()->create();

    $this->assertDatabaseHas('news', [
        'id' => $news->id,
        'title' => $news->title,
        'slug' => $news->slug,
    ]);

    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('news');
    expect($columns)->toContain('title');
    expect($columns)->toContain('slug');
    expect($columns)->toContain('content');
    expect($columns)->toContain('excerpt');
    expect($columns)->toContain('featured_image');
    expect($columns)->toContain('author_id');
    expect($columns)->toContain('category_id');
    expect($columns)->toContain('tags');
    expect($columns)->toContain('is_featured');
    expect($columns)->toContain('views_count');
    expect($columns)->toContain('published_at');
});

test('documents table has correct columns', function () {
    $document = \App\Models\Document::factory()->create();

    $this->assertDatabaseHas('documents', [
        'id' => $document->id,
        'title' => $document->title,
        'slug' => $document->slug,
    ]);

    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('documents');
    expect($columns)->toContain('title');
    expect($columns)->toContain('slug');
    expect($columns)->toContain('file_path');
    expect($columns)->toContain('file_size');
    expect($columns)->toContain('download_count');
    expect($columns)->toContain('uploaded_by');
    expect($columns)->toContain('category_id');
});

test('categories table has correct columns', function () {
    $category = \App\Models\Category::factory()->create();

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => $category->name,
        'slug' => $category->slug,
    ]);

    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('categories');
    expect($columns)->toContain('name');
    expect($columns)->toContain('slug');
    expect($columns)->toContain('type');
    expect($columns)->toContain('parent_id');
});

test('court_schedules table has correct columns', function () {
    $schedule = \App\Models\CourtSchedule::factory()->create();

    $this->assertDatabaseHas('court_schedules', [
        'id' => $schedule->id,
        'case_number' => $schedule->case_number,
    ]);

    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('court_schedules');
    expect($columns)->toContain('case_number');
    expect($columns)->toContain('case_title');
    expect($columns)->toContain('parties');
    expect($columns)->toContain('scheduled_date');
    expect($columns)->toContain('scheduled_time');
    expect($columns)->toContain('court_room');
    expect($columns)->toContain('judge_id');
    expect($columns)->toContain('external_id');
    expect($columns)->toContain('sync_status');
    expect($columns)->toContain('last_sync_at');
});

test('ppid_requests table has correct columns', function () {
    $ppid = \App\Models\PpidRequest::factory()->create();

    $this->assertDatabaseHas('ppid_requests', [
        'id' => $ppid->id,
        'request_number' => $ppid->request_number,
    ]);

    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('ppid_requests');
    expect($columns)->toContain('request_number');
    expect($columns)->toContain('requester_name');
    expect($columns)->toContain('requester_email');
    expect($columns)->toContain('requester_phone');
    expect($columns)->toContain('request_type');
    expect($columns)->toContain('description');
    expect($columns)->toContain('status');
    expect($columns)->toContain('priority');
    expect($columns)->toContain('response');
    expect($columns)->toContain('responded_at');
    expect($columns)->toContain('attachments');
});

test('budget_transparency table has correct columns', function () {
    $budget = \App\Models\BudgetTransparency::factory()->create();

    $this->assertDatabaseHas('budget_transparency', [
        'id' => $budget->id,
        'title' => $budget->title,
    ]);

    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('budget_transparency');
    expect($columns)->toContain('title');
    expect($columns)->toContain('description');
    expect($columns)->toContain('amount');
    expect($columns)->toContain('fiscal_year');
    expect($columns)->toContain('document_path');
});

test('case_statistics table has correct columns', function () {
    $stats = \App\Models\CaseStatistics::factory()->create();

    $this->assertDatabaseHas('case_statistics', [
        'id' => $stats->id,
        'year' => $stats->year,
        'month' => $stats->month,
    ]);

    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('case_statistics');
    expect($columns)->toContain('year');
    expect($columns)->toContain('month');
    expect($columns)->toContain('total_filed');
    expect($columns)->toContain('total_resolved');
    expect($columns)->toContain('pending_carryover');
});

test('sipp_cases table has correct columns', function () {
    $case = \App\Models\SippCase::factory()->create();

    $this->assertDatabaseHas('sipp_cases', [
        'id' => $case->id,
        'case_number' => $case->case_number,
    ]);

    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('sipp_cases');
    expect($columns)->toContain('case_number');
    expect($columns)->toContain('case_title');
    expect($columns)->toContain('plaintiff');
    expect($columns)->toContain('defendant');
    expect($columns)->toContain('case_type_id');
    expect($columns)->toContain('court_room_id');
    expect($columns)->toContain('judge_id');
    expect($columns)->toContain('external_id');
});

test('sipp_judges table has correct columns', function () {
    $judge = \App\Models\SippJudge::factory()->create();

    $this->assertDatabaseHas('sipp_judges', [
        'id' => $judge->id,
        'full_name' => $judge->full_name,
    ]);

    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('sipp_judges');
    expect($columns)->toContain('full_name');
    expect($columns)->toContain('title');
    expect($columns)->toContain('nip');
    expect($columns)->toContain('external_id');
});

test('sipp_court_rooms table has correct columns', function () {
    $room = \App\Models\SippCourtRoom::factory()->create();

    $this->assertDatabaseHas('sipp_court_rooms', [
        'id' => $room->id,
        'name' => $room->name,
    ]);

    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('sipp_court_rooms');
    expect($columns)->toContain('name');
    expect($columns)->toContain('capacity');
    expect($columns)->toContain('external_id');
});

test('sipp_case_types table has correct columns', function () {
    $caseType = \App\Models\SippCaseType::factory()->create();

    $this->assertDatabaseHas('sipp_case_types', [
        'id' => $caseType->id,
        'name' => $caseType->name,
    ]);

    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('sipp_case_types');
    expect($columns)->toContain('name');
    expect($columns)->toContain('code');
    expect($columns)->toContain('external_id');
});

test('sipp_sync_logs table has correct columns', function () {
    $log = \App\Models\SippSyncLog::factory()->create();

    $this->assertDatabaseHas('sipp_sync_logs', [
        'id' => $log->id,
        'sync_type' => $log->sync_type,
    ]);

    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('sipp_sync_logs');
    expect($columns)->toContain('sync_type');
    expect($columns)->toContain('status');
    expect($columns)->toContain('records_fetched');
    expect($columns)->toContain('records_updated');
    expect($columns)->toContain('records_created');
    expect($columns)->toContain('error_message');
    expect($columns)->toContain('started_at');
    expect($columns)->toContain('completed_at');
});

test('user_activity_logs table has correct columns', function () {
    $log = \App\Models\UserActivityLog::factory()->create();

    $this->assertDatabaseHas('user_activity_logs', [
        'id' => $log->id,
        'user_id' => $log->user_id,
        'action' => $log->action,
    ]);

    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('user_activity_logs');
    expect($columns)->toContain('user_id');
    expect($columns)->toContain('action');
    expect($columns)->toContain('metadata');
    expect($columns)->toContain('ip_address');
    expect($columns)->toContain('user_agent');
});

test('document_versions table has correct columns', function () {
    $version = \App\Models\DocumentVersion::factory()->create();

    $this->assertDatabaseHas('document_versions', [
        'id' => $version->id,
        'document_id' => $version->document_id,
    ]);

    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('document_versions');
    expect($columns)->toContain('document_id');
    expect($columns)->toContain('version_number');
    expect($columns)->toContain('file_path');
    expect($columns)->toContain('file_size');
    expect($columns)->toContain('uploaded_by');
    expect($columns)->toContain('change_description');
});

test('settings table has correct columns', function () {
    $setting = \App\Models\Setting::factory()->create();

    $this->assertDatabaseHas('settings', [
        'id' => $setting->id,
        'key' => $setting->key,
    ]);

    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('settings');
    expect($columns)->toContain('key');
    expect($columns)->toContain('value');
    expect($columns)->toContain('type');
    expect($columns)->toContain('group');
    expect($columns)->toContain('description');
});

test('foreign keys are properly set up', function () {
    // Test page-author relationship
    $page = \App\Models\Page::factory()->create();
    expect($page->author)->toBeInstanceOf(\App\Models\User::class);

    // Test menu-item-menu relationship
    $menuItem = \App\Models\MenuItem::factory()->create();
    expect($menuItem->menu)->toBeInstanceOf(\App\Models\Menu::class);

    // Test news-author relationship
    $news = \App\Models\News::factory()->create();
    expect($news->author)->toBeInstanceOf(\App\Models\User::class);

    // Test document-uploader relationship
    $document = \App\Models\Document::factory()->create();
    expect($document->uploader)->toBeInstanceOf(\App\Models\User::class);

    // Test schedule-judge relationship
    $schedule = \App\Models\CourtSchedule::factory()->create();
    if ($schedule->judge) {
        expect($schedule->judge)->toBeInstanceOf(\App\Models\SippJudge::class);
    }
});

test('json columns are properly cast', function () {
    // Test user permissions JSON
    $user = \App\Models\User::factory()->create([
        'permissions' => ['pages.create', 'news.update'],
    ]);
    expect($user->permissions)->toBeArray();
    expect($user->permissions)->toHaveCount(2);

    // Test page meta JSON
    $page = \App\Models\Page::factory()->create([
        'meta' => ['description' => 'Test', 'keywords' => ['test']],
    ]);
    expect($page->meta)->toBeArray();
    expect($page->meta['description'])->toBe('Test');

    // Test news tags JSON
    $news = \App\Models\News::factory()->create([
        'tags' => ['tag1', 'tag2', 'tag3'],
    ]);
    expect($news->tags)->toBeArray();
    expect($news->tags)->toHaveCount(3);

    // Test court schedule parties JSON
    $schedule = \App\Models\CourtSchedule::factory()->create([
        'parties' => ['penggugat' => 'John', 'tergugat' => 'Jane'],
    ]);
    expect($schedule->parties)->toBeArray();
    expect($schedule->parties['penggugat'])->toBe('John');

    // Test PPID attachments JSON
    $ppid = \App\Models\PpidRequest::factory()->withAttachments()->create();
    expect($ppid->attachments)->toBeArray();
    expect($ppid->attachments)->toHaveCount(2);
});
