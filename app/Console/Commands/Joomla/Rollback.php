<?php

namespace App\Console\Commands\Joomla;

use App\Services\JoomlaMigration\JoomlaMigrator;
use Illuminate\Console\Command;

class Rollback extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'joomla:rollback
                            {type : The type of migration to rollback (categories, pages, news, menu_items, menus, all)}
                            {--keep-records : Keep the actual records, only remove migration tracking}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rollback Joomla migrations';

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
        $type = $this->argument('type');
        $keepRecords = $this->option('keep-records');

        $validTypes = ['categories', 'pages', 'news', 'menu_items', 'menus', 'all'];

        if (! in_array($type, $validTypes)) {
            $this->error("Invalid type: {$type}");
            $this->newLine();
            $this->info('Valid types are: '.implode(', ', $validTypes));

            return self::FAILURE;
        }

        if ($keepRecords) {
            $this->info('Keep records mode - will only remove migration tracking');
        }

        $this->warn('You are about to rollback migrations. This action cannot be undone!');
        $confirmed = $this->confirm("Do you wish to rollback {$type} migrations?");

        if (! $confirmed) {
            $this->info('Rollback cancelled.');

            return self::SUCCESS;
        }

        try {
            $typesToRollback = $type === 'all' ? ['categories', 'pages', 'news', 'menu_items', 'menus'] : [$type];

            $totalStats = [
                'deleted' => 0,
                'kept' => 0,
            ];

            foreach ($typesToRollback as $rollbackType) {
                $this->info("Rolling back {$rollbackType}...");

                $stats = $this->migrator->rollback($rollbackType, ! $keepRecords);

                $totalStats['deleted'] += $stats['deleted'];
                $totalStats['kept'] += $stats['kept'];

                $this->line("  Deleted: {$stats['deleted']}, Kept: {$stats['kept']}");
            }

            $this->newLine();
            $this->info('Rollback completed!');
            $this->newLine();

            $this->table(
                ['Status', 'Count'],
                [
                    ['Deleted', $totalStats['deleted']],
                    ['Kept', $totalStats['kept']],
                ]
            );

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->newLine();
            $this->error("Rollback failed: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
