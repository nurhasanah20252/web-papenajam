<?php

namespace App\Services\JoomlaMigration;

use App\Models\JoomlaMigration;
use App\Models\JoomlaMigrationItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class JoomlaMigrationManager
{
    protected array $services = [];

    public function __construct(
        protected CategoryMigrationService $categoryService,
        protected ContentMigrationService $contentService,
        protected NewsMigrationService $newsService,
        protected MenuMigrationService $menuService,
        protected DocumentMigrationService $documentService
    ) {
        $this->services = [
            JoomlaMigration::TYPE_CATEGORIES => $categoryService,
            JoomlaMigration::TYPE_PAGES => $contentService,
            JoomlaMigration::TYPE_NEWS => $newsService,
            JoomlaMigration::TYPE_MENUS => $menuService,
            JoomlaMigration::TYPE_DOCUMENTS => $documentService,
        ];
    }

    /**
     * Run complete migration.
     */
    public function runMigration(string $migrationName = 'Joomla Migration'): JoomlaMigration
    {
        $migration = JoomlaMigration::create([
            'name' => $migrationName,
            'status' => JoomlaMigration::STATUS_RUNNING,
            'started_at' => now(),
            'total_records' => 0,
            'processed_records' => 0,
            'failed_records' => 0,
        ]);

        try {
            // Migrate in order: categories -> pages -> news -> menus -> documents
            $this->categoryService->setMigration($migration)->migrate('joomla_categories.json');
            $this->contentService->setMigration($migration)->migrate('joomla_content.json');
            $this->newsService->setMigration($migration)->migrate('joomla_content.json'); // News also from content
            $this->menuService->setMigration($migration)->migrate('joomla_menu.json');
            $this->documentService->setMigration($migration)->migrate('joomla_images.json'); // Or documents if exists

            $migration->markAsCompleted();
        } catch (\Throwable $e) {
            $migration->markAsFailed([['message' => $e->getMessage()]]);
            throw $e;
        }

        return $migration;
    }

    /**
     * Set the migration context.
     */
    public function setMigration(JoomlaMigration $migration): self
    {
        foreach ($this->services as $service) {
            $service->setMigration($migration);
        }

        return $this;
    }

    /**
     * Migrate categories.
     */
    public function migrateCategories(JoomlaMigration $migration, array $categories): void
    {
        if (empty($categories)) {
            return;
        }

        $this->services[JoomlaMigration::TYPE_CATEGORIES]
            ->setMigration($migration)
            ->run(new Collection($categories));
    }

    /**
     * Migrate pages (articles not in news category).
     */
    public function migratePages(JoomlaMigration $migration, array $articles, array $categories = []): void
    {
        if (empty($articles)) {
            return;
        }

        $this->services[JoomlaMigration::TYPE_PAGES]
            ->setMigration($migration)
            ->run(new Collection($articles));
    }

    /**
     * Migrate news.
     */
    public function migrateNews(JoomlaMigration $migration, array $news, array $categories = []): void
    {
        if (empty($news)) {
            return;
        }

        $this->services[JoomlaMigration::TYPE_NEWS]
            ->setMigration($migration)
            ->run(new Collection($news));
    }

    /**
     * Migrate menus.
     */
    public function migrateMenus(JoomlaMigration $migration, array $menus, array $menuItems = []): void
    {
        if (empty($menus)) {
            return;
        }

        $this->services[JoomlaMigration::TYPE_MENUS]
            ->setMigration($migration)
            ->run(new Collection($menus));
    }

    /**
     * Migrate documents.
     */
    public function migrateDocuments(JoomlaMigration $migration, array $documents, array $categories = []): void
    {
        if (empty($documents)) {
            return;
        }

        $this->services[JoomlaMigration::TYPE_DOCUMENTS]
            ->setMigration($migration)
            ->run(new Collection($documents));
    }

    /**
     * Rollback a migration.
     */
    public function rollback(JoomlaMigration $migration): bool
    {
        if (! $migration->isComplete()) {
            return false;
        }

        DB::transaction(function () use ($migration) {
            // Delete in reverse order to handle foreign key constraints
            $types = [
                JoomlaMigration::TYPE_DOCUMENTS,
                JoomlaMigration::TYPE_MENUS,
                JoomlaMigration::TYPE_NEWS,
                JoomlaMigration::TYPE_PAGES,
                JoomlaMigration::TYPE_CATEGORIES,
            ];

            foreach ($types as $type) {
                $this->rollbackType($migration, $type);
            }
        });

        $migration->markAsRolledBack();

        return true;
    }

    /**
     * Rollback a specific type.
     */
    protected function rollbackType(JoomlaMigration $migration, string $type): void
    {
        $completedItems = $migration->items()
            ->where('type', $type)
            ->where('status', JoomlaMigrationItem::STATUS_COMPLETED)
            ->get();

        foreach ($completedItems as $item) {
            $modelClass = $item->local_model;

            if ($modelClass && $item->local_id) {
                $modelClass::where('id', $item->local_id)->delete();
            }
        }
    }

    /**
     * Get migration status.
     */
    public function getStatus(JoomlaMigration $migration): array
    {
        return [
            'id' => $migration->id,
            'name' => $migration->name,
            'status' => $migration->status,
            'progress' => $migration->progress,
            'total_records' => $migration->total_records,
            'processed_records' => $migration->processed_records,
            'failed_records' => $migration->failed_records,
            'started_at' => $migration->started_at,
            'completed_at' => $migration->completed_at,
            'errors' => $migration->errors,
        ];
    }

    /**
     * Get detailed migration report.
     */
    public function getReport(JoomlaMigration $migration): array
    {
        $itemsByType = $migration->items()
            ->select('type', 'status')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('type', 'status')
            ->get()
            ->groupBy('type');

        $typeSummary = [];

        foreach ($itemsByType as $type => $statusItems) {
            $typeSummary[$type] = [
                'total' => $statusItems->sum('count'),
                'completed' => $statusItems->where('status', JoomlaMigrationItem::STATUS_COMPLETED)->sum('count'),
                'failed' => $statusItems->where('status', JoomlaMigrationItem::STATUS_FAILED)->sum('count'),
                'skipped' => $statusItems->where('status', JoomlaMigrationItem::STATUS_SKIPPED)->sum('count'),
            ];
        }

        return [
            'migration' => $this->getStatus($migration),
            'by_type' => $typeSummary,
            'recent_errors' => collect($migration->errors ?? [])->take(10),
        ];
    }
}
