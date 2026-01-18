<?php

use App\Enums\{PageStatus, PageType, MenuLocation, UrlType, NewsStatus, CategoryType, ScheduleStatus, SyncStatus, PPIDStatus, PPIDPriority, UserRole};
use App\Models\{Page, PageTemplate, PageBlock, Menu, MenuItem, Category, News, Document, CourtSchedule, SippCase, SippJudge, BudgetTransparency, CaseStatistics, PpidRequest, UserActivityLog, JoomlaMigration, SippSyncLog, User};
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create user with all roles', function () {
    $roles = [UserRole::SuperAdmin, UserRole::Admin, UserRole::Author, UserRole::Designer, UserRole::Subscriber];

    foreach ($roles as $role) {
        $user = User::factory()->create(['role' => $role]);
        expect($user->role)->toBe($role);
    }
});

it('can create page with all types', function () {
    $types = [PageType::Static, PageType::Dynamic, PageType::Template];

    foreach ($types as $type) {
        $page = Page::factory()->create(['page_type' => $type]);
        expect($page->page_type)->toBe($type);
    }
});

it('can create page with all statuses', function () {
    $statuses = [PageStatus::Draft, PageStatus::Published, PageStatus::Archived];

    foreach ($statuses as $status) {
        $page = Page::factory()->create(['status' => $status]);
        expect($page->status)->toBe($status);
    }
});

it('can create page with blocks', function () {
    $page = Page::factory()->create();
    PageBlock::factory()->count(3)->create(['page_id' => $page->id]);

    expect($page->blocks()->count())->toBe(3);
});

it('can create menu with hierarchical items', function () {
    $menu = Menu::factory()->create(['location' => MenuLocation::Header]);
    $parent = MenuItem::factory()->create(['menu_id' => $menu->id, 'parent_id' => null]);
    MenuItem::factory()->count(3)->create(['menu_id' => $menu->id, 'parent_id' => $parent->id]);

    expect($menu->items()->count())->toBe(4);
    expect($parent->children()->count())->toBe(3);
});

it('can create category with children', function () {
    $parent = Category::factory()->news()->create();
    Category::factory()->count(3)->create(['parent_id' => $parent->id, 'type' => CategoryType::News]);

    expect($parent->children()->count())->toBe(3);
});

it('can create news with tags', function () {
    $news = News::factory()->create([
        'tags' => ['tag1', 'tag2', 'tag3'],
    ]);

    expect($news->tags)->toBeArray();
    expect(count($news->tags))->toBe(3);
});

it('can create court schedule from SIPP', function () {
    $schedule = CourtSchedule::factory()->fromSipp()->create();

    expect($schedule->external_id)->not->toBeNull();
    expect($schedule->last_sync_at)->not->toBeNull();
    expect($schedule->sync_status)->toBe(SyncStatus::Success);
});

it('can create court schedule with parties', function () {
    $schedule = CourtSchedule::factory()->create([
        'parties' => [
            'penggugat' => 'John Doe',
            'tergugat' => 'Jane Smith',
            'kuasa_hukum' => 'Lawyer Firm',
        ],
    ]);

    expect($schedule->parties)->toBeArray();
    expect($schedule->parties['penggugat'])->toBe('John Doe');
});

it('can create PPID request with all statuses', function () {
    $statuses = [PPIDStatus::Submitted, PPIDStatus::Reviewed, PPIDStatus::Processed, PPIDStatus::Completed, PPIDStatus::Rejected];

    foreach ($statuses as $status) {
        $ppid = PpidRequest::factory()->create(['status' => $status]);
        expect($ppid->status)->toBe($status);
    }
});

it('can create PPID request with priority', function () {
    $ppid = PpidRequest::factory()->highPriority()->create();

    expect($ppid->priority)->toBe(PPIDPriority::High);
    expect($ppid->isHighPriority())->toBeTrue();
});

it('can create PPID request with attachments', function () {
    $ppid = PpidRequest::factory()->withAttachments()->create();

    expect($ppid->attachments)->toBeArray();
    expect(count($ppid->attachments))->toBe(2);
});

it('can generate PPID request number', function () {
    $ppid = PpidRequest::factory()->create();

    expect($ppid->request_number)->toMatch('/^PPID\/\d{4}\/\d{2}\/\d{4}$/');
});

it('can create budget transparency with amount', function () {
    $budget = BudgetTransparency::factory()->create(['amount' => 1000000000]);

    expect((float) $budget->amount)->toBe(1000000000.0);
    expect($budget->getFormattedAmount())->toBe('Rp 1.000.000.000');
});

it('can create case statistics', function () {
    $stats = CaseStatistics::factory()->create([
        'total_filed' => 100,
        'total_resolved' => 80,
        'pending_carryover' => 20,
    ]);

    expect($stats->getPendingCases())->toBe(40);
    expect($stats->getResolutionRate())->toBe(80.0);
});

it('can create user activity log', function () {
    $user = User::factory()->create();
    $log = UserActivityLog::factory()->create([
        'user_id' => $user->id,
        'action' => 'login',
        'metadata' => ['ip' => '127.0.0.1'],
    ]);

    expect($log->user->id)->toBe($user->id);
    expect($log->action)->toBe('login');
});

it('can create joomla migration log', function () {
    $migration = JoomlaMigration::factory()->create([
        'source_table' => 'content',
        'source_id' => 123,
        'target_id' => 456,
        'migration_status' => 'success',
    ]);

    expect($migration->isSuccessful())->toBeTrue();
});

it('can create sipp sync log', function () {
    $log = SippSyncLog::factory()->create([
        'sync_type' => 'full',
        'records_fetched' => 100,
        'records_updated' => 50,
        'records_created' => 30,
    ]);

    expect($log->getTotalProcessed())->toBe(180);
    expect($log->wasSuccessful())->toBeTrue();
});

it('can create sipp case', function () {
    $case = SippCase::factory()->create([
        'plaintiff' => [['name' => 'John Doe']],
        'defendant' => [['name' => 'Jane Smith']],
    ]);

    expect($case->getPlaintiffNames())->toBe('John Doe');
    expect($case->getDefendantNames())->toBe('Jane Smith');
});

it('can create sipp judge', function () {
    $judge = SippJudge::factory()->create([
        'full_name' => 'Dr. H. Ahmad, S.H., M.H.',
        'title' => 'Yang Mulia',
    ]);

    expect($judge->getFormattedName())->toBe('Yang Mulia Dr. H. Ahmad, S.H., M.H.');
});

it('can create document with file info', function () {
    $document = Document::factory()->create([
        'file_size' => 1048576, // 1 MB
        'file_path' => 'documents/test.pdf',
    ]);

    expect($document->getHumanFileSize())->toBe('1 MB');
});

it('can create menu item with route', function () {
    $item = MenuItem::factory()->withRoute()->create([
        'route_name' => 'home',
    ]);

    expect($item->url_type)->toBe(UrlType::Route);
    expect($item->route_name)->toBe('home');
});

it('can create news with featured image', function () {
    $news = News::factory()->create([
        'featured_image' => 'https://example.com/image.jpg',
    ]);

    expect($news->featured_image)->toBe('https://example.com/image.jpg');
});

it('can create page with meta data', function () {
    $page = Page::factory()->create([
        'meta' => [
            'description' => 'Test description',
            'keywords' => ['test', 'page'],
        ],
    ]);

    expect($page->getMetaDescription())->toBe('Test description');
    expect($page->getMetaKeywords())->toBe(['test', 'page']);
});

it('can create court schedule with formatted dates', function () {
    $schedule = CourtSchedule::factory()->today()->create();

    expect($schedule->isToday())->toBeTrue();
    expect($schedule->getFormattedDate())->not->toBeNull();
    expect($schedule->getFormattedTime())->not->toBeNull();
});

it('can create ppid request with days pending', function () {
    $ppid = PpidRequest::factory()->submitted()->create([
        'created_at' => now()->subDays(5),
    ]);

    expect($ppid->isPending())->toBeTrue();
    expect($ppid->getDaysPending())->toBe(5);
});

it('can create user with profile completed', function () {
    $user = User::factory()->profileCompleted()->create();

    expect($user->profile_completed)->toBeTrue();
});

it('can create user with last login', function () {
    $user = User::factory()->loggedIn()->create();

    expect($user->last_login_at)->not->toBeNull();
});
