<?php

namespace App\Jobs;

use App\Models\JoomlaMigration;
use App\Services\JoomlaMigration\JoomlaMigrationManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class JoomlaMigrationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     */
    public int $maxExceptions = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 3600;

    /**
     * Indicate if the job should be marked as failed on timeout.
     */
    public bool $failOnTimeout = true;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected JoomlaMigration $migration,
        protected array $joomlaData,
        protected int $chunkSize = 100
    ) {
        $this->onQueue('migrations');
    }

    /**
     * Execute the job.
     */
    public function handle(JoomlaMigrationManager $manager): void
    {
        try {
            Log::info('Starting Joomla migration job', [
                'migration_id' => $this->migration->id,
                'name' => $this->migration->name,
            ]);

            // Run the migration
            $manager->runMigration($this->joomlaData, $this->migration->name);

            Log::info('Joomla migration job completed successfully', [
                'migration_id' => $this->migration->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('Joomla migration job failed', [
                'migration_id' => $this->migration->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Mark migration as failed
            $this->migration->markAsFailed([[
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]]);

            throw $e;
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<int, string>
     */
    public function tags(): array
    {
        return ['joomla-migration', 'migration:'.$this->migration->id];
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [60, 300, 900]; // 1min, 5min, 15min
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Joomla migration job failed permanently', [
            'migration_id' => $this->migration->id,
            'error' => $exception->getMessage(),
        ]);

        // Send notification to admin if needed
        // $this->migration->user->notify(new MigrationFailedNotification($this->migration, $exception));
    }
}
