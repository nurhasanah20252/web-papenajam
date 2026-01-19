<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Exception;
use Illuminate\Console\Command;

class BackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backup {--list : List all backups} {--delete= : Delete a specific backup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Handle application backups';

    /**
     * Execute the console command.
     */
    public function handle(BackupService $backupService): int
    {
        if ($this->option('list')) {
            $this->listBackups($backupService);

            return 0;
        }

        if ($filename = $this->option('delete')) {
            $this->deleteBackup($backupService, $filename);

            return 0;
        }

        $this->createBackup($backupService);

        return 0;
    }

    protected function createBackup(BackupService $backupService): void
    {
        $this->info('Starting backup process...');

        try {
            $result = $backupService->create();
            $this->info("Backup created successfully: {$result['filename']}");
            $this->info("Path: {$result['path']}");
            $this->info("Size: {$result['size']}");
        } catch (Exception $e) {
            $this->error("Backup failed: {$e->getMessage()}");
        }
    }

    protected function listBackups(BackupService $backupService): void
    {
        $backups = $backupService->list();

        if (empty($backups)) {
            $this->info('No backups found.');

            return;
        }

        $this->table(
            ['Filename', 'Size', 'Date'],
            $backups
        );
    }

    protected function deleteBackup(BackupService $backupService, string $filename): void
    {
        if ($backupService->delete($filename)) {
            $this->info("Backup deleted: {$filename}");
        } else {
            $this->error("Could not delete backup: {$filename} (File might not exist)");
        }
    }
}
