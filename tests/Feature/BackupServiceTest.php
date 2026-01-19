<?php

use App\Services\BackupService;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    $this->backupService = new BackupService;
});

it('can list backups', function () {
    $now = now();
    Storage::disk('local')->put('backups/backup_2024-01-01_10-00-00.zip', 'content');
    touch(Storage::disk('local')->path('backups/backup_2024-01-01_10-00-00.zip'), $now->subDay()->timestamp);

    Storage::disk('local')->put('backups/backup_2024-01-02_10-00-00.zip', 'content');
    touch(Storage::disk('local')->path('backups/backup_2024-01-02_10-00-00.zip'), $now->timestamp);

    $backups = $this->backupService->list();

    expect($backups)->toHaveCount(2)
        ->and($backups[0]['filename'])->toBe('backup_2024-01-02_10-00-00.zip')
        ->and($backups[1]['filename'])->toBe('backup_2024-01-01_10-00-00.zip');
});

it('can delete a backup', function () {
    Storage::disk('local')->put('backups/test_backup.zip', 'content');

    $result = $this->backupService->delete('test_backup.zip');

    expect($result)->toBeTrue();
    Storage::disk('local')->assertMissing('backups/test_backup.zip');
});

it('can download a backup', function () {
    expect(fn () => $this->backupService->download('non_existent.zip'))
        ->toThrow(Exception::class, 'Backup file not found: non_existent.zip');
});

it('can create a backup', function () {
    Process::fake([
        'mysqldump *' => Process::result('', '', 0),
    ]);

    // Ensure there is at least one file to zip
    Storage::disk('local')->put('some-file.txt', 'content');

    $result = $this->backupService->create();

    expect($result)->toHaveKeys(['path', 'size', 'filename'])
        ->and($result['filename'])->toStartWith('backup_');

    expect(file_exists($result['path']))->toBeTrue();

    Process::assertRan(fn ($process) => str_contains($process->command, 'mysqldump'));

    // Clean up
    if (file_exists($result['path'])) {
        unlink($result['path']);
    }
});
