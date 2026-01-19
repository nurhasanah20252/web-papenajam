<?php

namespace App\Console\Commands;

use App\Models\JoomlaMigration;
use App\Services\JoomlaMigration\MigrationValidationService;
use Illuminate\Console\Command;

class JoomlaValidateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'joomla:validate {--id= : The ID of the migration to validate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate Joomla migration results';

    /**
     * Execute the console command.
     */
    public function handle(MigrationValidationService $validator): int
    {
        $migrationId = $this->option('id');

        if ($migrationId) {
            $migration = JoomlaMigration::find($migrationId);
            if (! $migration) {
                $this->error("Migration with ID {$migrationId} not found.");

                return 1;
            }
        } else {
            $migration = JoomlaMigration::where('status', JoomlaMigration::STATUS_COMPLETED)
                ->latest()
                ->first();

            if (! $migration) {
                $this->error('No completed migration found to validate.');

                return 1;
            }
        }

        $this->info("Validating migration: {$migration->name} (ID: {$migration->id})");

        $report = $validator->generateReport($migration);

        $this->displaySummary($report);
        $this->displayRecordCounts($report['record_counts']);
        $this->displayIssues('Errors', $report['errors']);
        $this->displayIssues('Warnings', $report['warnings']);

        $this->info("\nIntegrity Score: ".number_format($report['integrity_score'], 2).'%');

        return empty($report['errors']) ? 0 : 1;
    }

    protected function displaySummary(array $report): void
    {
        $this->newLine();
        $this->info('Migration Summary:');
        $this->table(
            ['Property', 'Value'],
            [
                ['ID', $report['migration']['id']],
                ['Name', $report['migration']['name']],
                ['Status', $report['migration']['status']],
                ['Started', $report['migration']['started_at']],
                ['Completed', $report['migration']['completed_at']],
                ['Duration', $report['migration']['duration'].' seconds'],
            ]
        );
    }

    protected function displayRecordCounts(array $counts): void
    {
        $this->newLine();
        $this->info('Record Counts:');

        $rows = [];
        foreach ($counts['expected'] as $type => $expected) {
            $actual = $counts['actual'][$type] ?? 0;
            $diff = $actual - $expected;
            $status = $diff === 0 ? '✓' : ($diff > 0 ? '+'.$diff : $diff);

            $rows[] = [$type, $expected, $actual, $status];
        }

        $this->table(['Type', 'Expected', 'Actual', 'Diff'], $rows);
    }

    protected function displayIssues(string $title, array $issues): void
    {
        if (empty($issues)) {
            return;
        }

        $this->newLine();
        $this->warn("{$title}:");

        $rows = [];
        foreach ($issues as $issue) {
            $rows[] = [$issue['type'], $issue['message']];
        }

        $this->table(['Category', 'Message'], $rows);
    }
}
