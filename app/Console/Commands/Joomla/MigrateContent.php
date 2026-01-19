<?php

namespace App\Console\Commands\Joomla;

use App\Services\JoomlaMigration\JoomlaMigrator;
use Illuminate\Console\Command;

class MigrateContent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'joomla:migrate:content
                            {--force : Force re-migration of already migrated records}
                            {--dry-run : Show what would be migrated without actually migrating}
                            {--batch=100 : Number of records to process in each batch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate Joomla content to Laravel Pages and News';

    public function __construct(
        protected JoomlaMigrator $migrator
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $force = $this->option('force');
        $dryRun = $this->option('dry-run');
        $batchSize = (int) $this->option('batch');

        if ($dryRun) {
            $this->info('Dry run mode - no data will be migrated');
        }

        $this->info('Starting Joomla content migration...');
        $this->info('Content will be migrated to Pages (static) and News (categorized)');

        if ($force) {
            $this->warn('Force mode enabled - will re-migrate existing records');
        }

        try {
            if ($dryRun) {
                $this->newLine();
                $this->info('This would migrate content from docs/joomla_content.json');
                $this->info("Batch size: {$batchSize}");
                $this->newLine();

                return self::SUCCESS;
            }

            $stats = $this->migrator->migrateContent($force);

            $this->newLine();
            $this->info('Migration completed!');
            $this->newLine();

            $this->table(
                ['Status', 'Count'],
                [
                    ['Success', $stats['success']],
                    ['Failed', $stats['failed']],
                    ['Skipped', $stats['skipped']],
                ]
            );

            if ($stats['failed'] > 0) {
                $this->newLine();
                $this->error('Some migrations failed. Check logs for details.');

                return self::FAILURE;
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->newLine();
            $this->error("Migration failed: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
