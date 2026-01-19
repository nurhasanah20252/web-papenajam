<?php

namespace App\Jobs;

use App\Models\JoomlaMigration;
use App\Services\JoomlaMigration\JoomlaMigrationManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ProcessJoomlaMigration implements ShouldQueue
{
    use Queueable;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 3600;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected int $migrationId,
        protected string $filePath,
        protected string $type
    ) {}

    /**
     * Execute the job.
     */
    public function handle(JoomlaMigrationManager $manager): void
    {
        $migration = JoomlaMigration::findOrFail($this->migrationId);
        $migration->markAsRunning();

        try {
            if (! File::exists($this->filePath)) {
                throw new \Exception("Migration file not found: {$this->filePath}");
            }

            $jsonContent = File::get($this->filePath);
            $data = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON in file: '.json_last_error_msg());
            }

            // Prepare data for the manager
            // The manager expects a full joomlaData array with specific keys
            $joomlaData = [];

            // Map types to expected keys in JoomlaMigrationManager
            $typeMap = [
                JoomlaMigration::TYPE_CATEGORIES => 'categories',
                JoomlaMigration::TYPE_PAGES => 'articles',
                JoomlaMigration::TYPE_NEWS => 'news',
                JoomlaMigration::TYPE_MENUS => 'menus',
                JoomlaMigration::TYPE_DOCUMENTS => 'documents',
            ];

            $key = $typeMap[$this->type] ?? $this->type;
            $joomlaData[$key] = $data;

            // If it's menus, we might also need menu_items if they are separate
            if ($this->type === JoomlaMigration::TYPE_MENUS && ! isset($data['menu_items'])) {
                // If the input JSON already contains both, we're good
                // Otherwise we might need to handle it. For now assume it's one file.
            }

            // Run the specific migration through manager logic
            // Note: JoomlaMigrationManager::runMigration runs EVERYTHING in sequence
            // We might want to call the specific migrate methods instead if we want granular control

            $manager->setMigration($migration);

            switch ($this->type) {
                case JoomlaMigration::TYPE_CATEGORIES:
                    $manager->migrateCategories($migration, $data);
                    break;
                case JoomlaMigration::TYPE_PAGES:
                    $manager->migratePages($migration, $data);
                    break;
                case JoomlaMigration::TYPE_NEWS:
                    $manager->migrateNews($migration, $data);
                    break;
                case JoomlaMigration::TYPE_MENUS:
                    // Menus migration usually requires both menus and menu_items
                    // If they are in the same file, we can extract them
                    $menus = $data['menus'] ?? $data;
                    $menuItems = $data['menu_items'] ?? [];
                    $manager->migrateMenus($migration, $menus, $menuItems);
                    break;
                case JoomlaMigration::TYPE_DOCUMENTS:
                    $manager->migrateDocuments($migration, $data);
                    break;
                default:
                    throw new \Exception("Unknown migration type: {$this->type}");
            }

            $migration->markAsCompleted();

            // Optionally delete the temporary file
            // File::delete($this->filePath);

        } catch (\Throwable $e) {
            Log::error('Joomla migration failed: '.$e->getMessage(), [
                'migration_id' => $this->migrationId,
                'type' => $this->type,
                'file' => $this->filePath,
            ]);

            $migration->markAsFailed([[
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]]);
        }
    }
}
