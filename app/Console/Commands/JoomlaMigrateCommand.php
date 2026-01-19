<?php

namespace App\Console\Commands;

use App\Services\JoomlaMigration\JoomlaMigrationManager;
use Illuminate\Console\Command;

class JoomlaMigrateCommand extends Command
{
    protected $signature = 'joomla:migrate {file? : Path to the Joomla export JSON file}
                            {--name= : Optional name for the migration}
                            {--rollback : Rollback a previous migration}
                            {--validate : Validate a migration}
                            {--id= : Migration ID for rollback/validate}';

    protected $description = 'Run Joomla data migration or rollback';

    public function handle(): int
    {
        if ($this->option('rollback')) {
            return $this->handleRollback();
        }

        if ($this->option('validate')) {
            return $this->handleValidate();
        }

        return $this->handleMigration();
    }

    protected function handleMigration(): int
    {
        $name = $this->option('name') ?: 'Joomla Migration '.now()->format('Y-m-d H:i:s');

        $this->info("Starting migration: {$name}");

        try {
            $manager = app(JoomlaMigrationManager::class);
            $migration = $manager->runMigration($name);

            $this->newLine();
            $this->info('Migration completed!');
            $this->info("Status: {$migration->status}");
            $this->info("Total Records: {$migration->total_records}");
            $this->info("Processed: {$migration->processed_records}");
            $this->info("Failed: {$migration->failed_records}");

            if (! empty($migration->errors)) {
                $this->warn('Errors occurred during migration:');
                foreach (array_slice($migration->errors, 0, 5) as $error) {
                    $this->error('  - '.(is_array($error) ? ($error['message'] ?? json_encode($error)) : $error));
                }

                if (count($migration->errors) > 5) {
                    $this->warn('  ... and '.(count($migration->errors) - 5).' more errors');
                }
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Migration failed: '.$e->getMessage());

            return Command::FAILURE;
        }
    }

    protected function handleRollback(): int
    {
        $id = $this->option('id');

        if (! $id) {
            $this->error('Please provide a migration ID with --id=');

            return Command::FAILURE;
        }

        $migration = \App\Models\JoomlaMigration::find($id);

        if (! $migration) {
            $this->error("Migration with ID {$id} not found.");

            return Command::FAILURE;
        }

        if (! $migration->isComplete()) {
            $this->error('Cannot rollback a migration that is not completed.');

            return Command::FAILURE;
        }

        $this->info("Rolling back migration: {$migration->name}");

        $manager = app(JoomlaMigrationManager::class);
        $result = $manager->rollback($migration);

        if ($result) {
            $this->info('Rollback completed successfully.');

            return Command::SUCCESS;
        }

        $this->error('Rollback failed.');

        return Command::FAILURE;
    }

    protected function handleValidate(): int
    {
        $id = $this->option('id');

        if (! $id) {
            $this->error('Please provide a migration ID with --id=');

            return Command::FAILURE;
        }

        $migration = \App\Models\JoomlaMigration::find($id);

        if (! $migration) {
            $this->error("Migration with ID {$id} not found.");

            return Command::FAILURE;
        }

        $this->info("Validating migration: {$migration->name}");

        $validator = app(\App\Services\JoomlaMigration\MigrationValidationService::class);
        $report = $validator->generateReport($migration);

        $this->newLine();
        $this->info('=== Migration Report ===');
        $this->info("Status: {$report['migration']['status']}");
        $this->info("Duration: {$report['migration']['duration']} seconds");

        $this->newLine();
        $this->info('=== Record Counts ===');
        foreach ($report['record_counts']['expected'] as $type => $expected) {
            $actual = $report['record_counts']['actual'][$type] ?? 0;
            $status = $expected === $actual ? 'OK' : 'MISMATCH';
            $this->info("{$type}: Expected {$expected}, Actual {$actual} [{$status}]");
        }

        $this->newLine();
        $this->info('Integrity Score: '.$report['integrity_score'].'%');

        if (! empty($report['warnings'])) {
            $this->newLine();
            $this->warn('=== Warnings ===');
            foreach ($report['warnings'] as $warning) {
                $this->warn("  [{$warning['type']}] {$warning['message']}");
            }
        }

        if (! empty($report['errors'])) {
            $this->newLine();
            $this->error('=== Errors ===');
            foreach ($report['errors'] as $error) {
                $this->error("  [{$error['type']}] {$error['message']}");
            }
        }

        if (empty($report['warnings']) && empty($report['errors'])) {
            $this->newLine();
            $this->info('No issues found. Migration is valid!');
        }

        return Command::SUCCESS;
    }
}
