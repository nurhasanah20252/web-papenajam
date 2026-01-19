<?php

namespace App\Services\JoomlaMigration;

use App\Models\JoomlaMigration;
use App\Models\JoomlaMigrationItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

abstract class BaseMigrationService
{
    protected JoomlaMigration $migration;

    abstract public function getType(): string;

    abstract public function validateData(array $data): bool;

    abstract public function transformData(array $data): array;

    abstract public function saveData(array $data): mixed;

    abstract public function getModelClass(): string;

    /**
     * Create a new migration record.
     */
    public function createMigration(string $name, array $metadata = []): JoomlaMigration
    {
        return JoomlaMigration::create([
            'name' => $name,
            'status' => JoomlaMigration::STATUS_PENDING,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Set the migration context.
     */
    public function setMigration(JoomlaMigration $migration): self
    {
        $this->migration = $migration;

        return $this;
    }

    /**
     * Get the current migration.
     */
    public function getMigration(): JoomlaMigration
    {
        return $this->migration;
    }

    /**
     * Run the migration for a collection of data.
     */
    public function run(Collection $data): JoomlaMigration
    {
        $this->migration->markAsRunning();

        $total = $data->count();

        // Update total records
        $this->migration->increment('total_records', $total);

        foreach ($data as $item) {
            $errorOccurred = false;
            try {
                $this->processItem($item);
            } catch (\Throwable $e) {
                $errorOccurred = true;
            }

            $this->migration->updateProgress(
                $this->migration->processed_records + 1,
                $this->migration->failed_records + ($errorOccurred ? 1 : 0)
            );
        }

        return $this->migration;
    }

    /**
     * Load data from a JSON file.
     */
    public function loadData(string $filename): Collection
    {
        $path = base_path('docs/'.$filename);

        if (! file_exists($path)) {
            throw new \Exception("File not found: {$path}");
        }

        $content = file_get_contents($path);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid JSON in file: {$filename}");
        }

        return collect($data);
    }

    /**
     * Main migrate method to be called by the manager.
     */
    public function migrate(string $filename): void
    {
        $data = $this->loadData($filename);
        $this->run($data);
    }

    /**
     * Process a single item.
     */
    protected function processItem(array $item): mixed
    {
        $migrationItem = JoomlaMigrationItem::create([
            'migration_id' => $this->migration->id,
            'type' => $this->getType(),
            'joomla_id' => $item['id'] ?? 0,
            'joomla_data' => $item,
            'status' => JoomlaMigrationItem::STATUS_PROCESSING,
        ]);

        try {
            if (! $this->validateData($item)) {
                $migrationItem->markAsSkipped('Data validation failed');

                return null;
            }

            $transformedData = $this->transformData($item);
            $localId = $this->saveData($transformedData);

            $migrationItem->markAsCompleted($localId);

            return $localId;
        } catch (\Throwable $e) {
            $migrationItem->markAsFailed($e->getMessage());

            throw $e;
        }
    }

    /**
     * Generate a slug from title.
     */
    protected function generateSlug(string $title): string
    {
        $slug = Str::slug($title);
        $modelClass = $this->getModelClass();
        $i = 1;

        while ($modelClass::where('slug', $slug)->exists()) {
            $slug = Str::slug($title).'-'.$i;
            $i++;
        }

        return $slug;
    }

    /**
     * Clean HTML content.
     */
    protected function cleanContent(string $content): array
    {
        return app(JoomlaDataCleaner::class)->cleanContent($content);
    }

    /**
     * Convert Joomla links to Laravel routes.
     */
    protected function convertLinks(string $content): string
    {
        return app(JoomlaDataCleaner::class)->convertLinks($content);
    }

    /**
     * Process images from Joomla content.
     */
    protected function processImages(array $images): array
    {
        return app(JoomlaDataCleaner::class)->processImages($images);
    }
}
