<?php

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
});

it('can create backup via command', function () {
    Process::fake([
        'mysqldump *' => Process::result('', '', 0),
    ]);

    // Ensure there is at least one file to zip
    Storage::disk('local')->put('some-file.txt', 'content');

    $this->artisan('app:backup')
        ->expectsOutput('Starting backup process...')
        ->expectsOutputToContain('Backup created successfully')
        ->assertExitCode(0);
});

it('can list backups via command', function () {
    $now = now();
    Storage::disk('local')->put('backups/backup_1.zip', 'content');
    touch(Storage::disk('local')->path('backups/backup_1.zip'), $now->timestamp);

    $this->artisan('app:backup --list')
        ->assertExitCode(0);
});

it('can delete backup via command', function () {
    Storage::disk('local')->put('backups/test_backup.zip', 'content');

    $this->artisan('app:backup --delete=test_backup.zip')
        ->expectsOutput('Backup deleted: test_backup.zip')
        ->assertExitCode(0);

    Storage::disk('local')->assertMissing('backups/test_backup.zip');
});

it('shows error when deleting non-existent backup', function () {
    $this->artisan('app:backup --delete=non_existent.zip')
        ->expectsOutput('Could not delete backup: non_existent.zip (File might not exist)')
        ->assertExitCode(0);
});
