<?php

namespace App\Services\JoomlaMigration;

use App\Models\Category;
use App\Models\Document;
use App\Models\JoomlaMigration;
use App\Models\JoomlaMigrationItem;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\News;
use App\Models\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MigrationValidator
{
    /**
     * Validate a migration after completion.
     */
    public function validateMigration(JoomlaMigration $migration): MigrationValidationResult
    {
        $result = new MigrationValidationResult();

        // Check record counts
        $this->validateRecordCounts($migration, $result);

        // Check data integrity
        $this->validateDataIntegrity($migration, $result);

        // Check for orphaned records
        $this->validateOrphanedRecords($migration, $result);

        // Check link integrity
        $this->validateLinkIntegrity($migration, $result);

        return $result;
    }

    /**
     * Validate record counts between migration and database.
     */
    protected function validateRecordCounts(JoomlaMigration $migration, MigrationValidationResult $result): void
    {
        $expectedCounts = [
            JoomlaMigration::TYPE_CATEGORIES => JoomlaMigrationItem::where('migration_id', $migration->id)
                ->where('type', JoomlaMigration::TYPE_CATEGORIES)
                ->where('status', JoomlaMigrationItem::STATUS_COMPLETED)
                ->count(),
            JoomlaMigration::TYPE_PAGES => JoomlaMigrationItem::where('migration_id', $migration->id)
                ->where('type', JoomlaMigration::TYPE_PAGES)
                ->where('status', JoomlaMigrationItem::STATUS_COMPLETED)
                ->count(),
            JoomlaMigration::TYPE_NEWS => JoomlaMigrationItem::where('migration_id', $migration->id)
                ->where('type', JoomlaMigration::TYPE_NEWS)
                ->where('status', JoomlaMigrationItem::STATUS_COMPLETED)
                ->count(),
            JoomlaMigration::TYPE_MENUS => JoomlaMigrationItem::where('migration_id', $migration->id)
                ->where('type', JoomlaMigration::TYPE_MENUS)
                ->where('status', JoomlaMigrationItem::STATUS_COMPLETED)
                ->count(),
            JoomlaMigration::TYPE_DOCUMENTS => JoomlaMigrationItem::where('migration_id', $migration->id)
                ->where('type', JoomlaMigration::TYPE_DOCUMENTS)
                ->where('status', JoomlaMigrationItem::STATUS_COMPLETED)
                ->count(),
        ];

        $actualCounts = [
            JoomlaMigration::TYPE_CATEGORIES => Category::count(),
            JoomlaMigration::TYPE_PAGES => Page::count(),
            JoomlaMigration::TYPE_NEWS => News::count(),
            JoomlaMigration::TYPE_MENUS => Menu::count(),
            JoomlaMigration::TYPE_DOCUMENTS => Document::count(),
        ];

        foreach ($expectedCounts as $type => $expected) {
            $actual = $actualCounts[$type] ?? 0;

            if ($expected > $actual) {
                $result->addWarning(
                    $type,
                    "Expected {$expected} {$type} records, found {$actual}"
                );
            }
        }

        $result->setRecordCounts([
            'expected' => $expectedCounts,
            'actual' => $actualCounts,
        ]);
    }

    /**
     * Validate data integrity of migrated records.
     */
    protected function validateDataIntegrity(JoomlaMigration $migration, MigrationValidationResult $result): void
    {
        // Check for duplicate slugs
        $duplicateSlugs = DB::table('pages')
            ->select('slug')
            ->groupBy('slug')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        if ($duplicateSlugs > 0) {
            $result->addError('pages', "Found {$duplicateSlugs} duplicate slugs in pages table");
        }

        $duplicateNewsSlugs = DB::table('news')
            ->select('slug')
            ->groupBy('slug')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        if ($duplicateNewsSlugs > 0) {
            $result->addError('news', "Found {$duplicateNewsSlugs} duplicate slugs in news table");
        }

        // Check for empty required fields
        $emptyTitles = Page::whereNull('title')->orWhere('title', '')->count();
        if ($emptyTitles > 0) {
            $result->addWarning('pages', "Found {$emptyTitles} pages with empty titles");
        }

        $emptyTitleNews = News::whereNull('title')->orWhere('title', '')->count();
        if ($emptyTitleNews > 0) {
            $result->addWarning('news', "Found {$emptyTitleNews} news with empty titles");
        }
    }

    /**
     * Check for orphaned records.
     */
    protected function validateOrphanedRecords(JoomlaMigration $migration, MigrationValidationResult $result): void
    {
        // Check for menu items with invalid menu_id
        $orphanMenuItems = MenuItem::whereNotIn('menu_id', Menu::pluck('id'))
            ->count();

        if ($orphanMenuItems > 0) {
            $result->addWarning('menu_items', "Found {$orphanMenuItems} menu items with invalid menu_id");
        }

        // Check for nested menu items with invalid parent_id
        $orphanNestedMenuItems = MenuItem::whereNotNull('parent_id')
            ->whereNotIn('parent_id', MenuItem::pluck('id'))
            ->count();

        if ($orphanNestedMenuItems > 0) {
            $result->addWarning('menu_items', "Found {$orphanNestedMenuItems} menu items with invalid parent_id");
        }

        // Check for news with invalid category_id
        $orphanNews = News::whereNotNull('category_id')
            ->whereNotIn('category_id', Category::pluck('id'))
            ->count();

        if ($orphanNews > 0) {
            $result->addWarning('news', "Found {$orphanNews} news with invalid category_id");
        }

        // Check for documents with invalid category_id
        $orphanDocuments = Document::whereNotNull('category_id')
            ->whereNotIn('category_id', Category::pluck('id'))
            ->count();

        if ($orphanDocuments > 0) {
            $result->addWarning('documents', "Found {$orphanDocuments} documents with invalid category_id");
        }
    }

    /**
     * Validate link integrity between migrated records.
     */
    protected function validateLinkIntegrity(JoomlaMigration $migration, MigrationValidationResult $result): void
    {
        // Check menu items pointing to non-existent pages
        $brokenPageLinks = MenuItem::where('type', 'page')
            ->whereNotNull('page_id')
            ->whereNotIn('page_id', Page::pluck('id'))
            ->count();

        if ($brokenPageLinks > 0) {
            $result->addWarning('menu_items', "Found {$brokenPageLinks} menu items pointing to non-existent pages");
        }
    }

    /**
     * Generate a detailed migration report.
     */
    public function generateReport(JoomlaMigration $migration): array
    {
        $validationResult = $this->validateMigration($migration);

        $summary = [
            'migration' => [
                'id' => $migration->id,
                'name' => $migration->name,
                'status' => $migration->status,
                'started_at' => $migration->started_at,
                'completed_at' => $migration->completed_at,
                'duration' => $migration->completed_at?->diffInSeconds($migration->started_at),
            ],
            'record_counts' => $validationResult->getRecordCounts(),
            'integrity_score' => $this->calculateIntegrityScore($validationResult),
            'warnings' => $validationResult->getWarnings(),
            'errors' => $validationResult->getErrors(),
        ];

        return $summary;
    }

    /**
     * Calculate an integrity score based on validation results.
     */
    protected function calculateIntegrityScore(MigrationValidationResult $result): float
    {
        $totalItems = $result->getRecordCounts()['expected'] ?? [];
        $total = array_sum($totalItems);

        if ($total === 0) {
            return 100.0;
        }

        $errorCount = count($result->getErrors());
        $warningCount = count($result->getWarnings());

        // Simple scoring: start at 100, deduct for errors and warnings
        $score = 100.0 - ($errorCount * 5) - ($warningCount * 1);

        return max(0, min(100, $score));
    }
}

class MigrationValidationResult
{
    protected array $warnings = [];

    protected array $errors = [];

    protected array $recordCounts = [];

    public function addWarning(string $type, string $message): void
    {
        $this->warnings[] = [
            'type' => $type,
            'message' => $message,
        ];
    }

    public function addError(string $type, string $message): void
    {
        $this->errors[] = [
            'type' => $type,
            'message' => $message,
        ];
    }

    public function getWarnings(): array
    {
        return $this->warnings;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function setRecordCounts(array $counts): void
    {
        $this->recordCounts = $counts;
    }

    public function getRecordCounts(): array
    {
        return $this->recordCounts;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function hasWarnings(): bool
    {
        return !empty($this->warnings);
    }
}
