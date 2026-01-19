<?php

namespace App\Console\Commands;

use App\Services\Sipp\SippApiClient;
use App\Services\Sipp\SippDataSync;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SippSyncCommand extends Command
{
    protected $signature = 'sipp:sync {type=incremental : Sync type (full or incremental)} {--force : Force sync even if already running}';

    protected $description = 'Sync data from SIPP API';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $type = $this->argument('type');
        $force = $this->option('force');

        if (! in_array($type, ['full', 'incremental'])) {
            $this->error('Invalid sync type. Use "full" or "incremental".');

            return Command::FAILURE;
        }

        $apiClient = new SippApiClient;

        if (! $apiClient->isConfigured()) {
            $this->error('SIPP API is not configured. Please check your configuration.');

            return Command::FAILURE;
        }

        if (! $apiClient->isAvailable()) {
            $this->error('SIPP API is not available. Please check your connection.');

            return Command::FAILURE;
        }

        $syncService = new SippDataSync($apiClient);

        if ($syncService->isRunning() && ! $force) {
            $this->error('A sync operation is already running. Use --force to override.');

            return Command::FAILURE;
        }

        $this->info("Starting {$type} sync...");
        $this->newLine();

        try {
            if ($type === 'full') {
                $result = $syncService->fullSync(true);
            } else {
                $result = $syncService->incrementalSync();
            }

            $this->info('Sync completed successfully!');
            $this->newLine();

            if (! empty($result['schedules'])) {
                $this->info('Schedules:');
                $this->line("  - Synced: {$result['schedules']['synced']}");
                $this->line("  - Updated: {$result['schedules']['updated']}");
                $this->line("  - Failed: {$result['schedules']['failed']}");
            }

            if (! empty($result['cases'])) {
                $this->info('Cases:');
                $this->line("  - Synced: {$result['cases']['synced']}");
                $this->line("  - Updated: {$result['cases']['updated']}");
                $this->line("  - Failed: {$result['cases']['failed']}");
            }

            if (! empty($result['judges'])) {
                $this->info('Judges:');
                $this->line("  - Synced: {$result['judges']['synced']}");
                $this->line("  - Updated: {$result['judges']['updated']}");
            }

            if (! empty($result['court_rooms'])) {
                $this->info('Court Rooms:');
                $this->line("  - Synced: {$result['court_rooms']['synced']}");
                $this->line("  - Updated: {$result['court_rooms']['updated']}");
            }

            if (! empty($result['case_types'])) {
                $this->info('Case Types:');
                $this->line("  - Synced: {$result['case_types']['synced']}");
                $this->line("  - Updated: {$result['case_types']['updated']}");
            }

            Log::info('SIPP sync completed via command', [
                'type' => $type,
                'result' => $result,
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Sync failed: {$e->getMessage()}");

            Log::error('SIPP sync command failed', [
                'type' => $type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }
}
