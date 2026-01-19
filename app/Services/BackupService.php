<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class BackupService
{
    protected string $disk = 'local';

    protected string $backupDir = 'backups';

    public function __construct()
    {
        if (! Storage::disk($this->disk)->exists($this->backupDir)) {
            Storage::disk($this->disk)->makeDirectory($this->backupDir);
        }
    }

    /**
     * Create a new backup.
     *
     * @return array{path: string, size: string, filename: string}
     *
     * @throws Exception
     */
    public function create(): array
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "backup_{$timestamp}.zip";

        $disk = Storage::disk($this->disk);
        $fullPath = $disk->path("{$this->backupDir}/{$filename}");
        $tempSqlFilename = "{$this->backupDir}/temp_db_{$timestamp}.sql";
        $tempSqlFile = $disk->path($tempSqlFilename);

        // 1. Database Backup
        $this->dumpDatabase($tempSqlFile);

        // 2. File Backup (Zip)
        $zip = new ZipArchive;
        if ($zip->open($fullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception("Could not create zip file at {$fullPath}");
        }

        // Add SQL dump to zip
        if (file_exists($tempSqlFile)) {
            $zip->addFile($tempSqlFile, 'database.sql');
        }

        // Add storage files
        $this->addStorageFilesToZip($zip);

        $zip->close();

        // Clean up temp SQL file
        if (file_exists($tempSqlFile)) {
            unlink($tempSqlFile);
        }

        if (! file_exists($fullPath)) {
            // In testing with Storage::fake(), the file might not exist on the real disk
            // but we need it for ZipArchive to work.
            // However, ZipArchive works on real paths.
            throw new Exception("Backup zip file was not created at {$fullPath}");
        }

        return [
            'path' => $fullPath,
            'size' => $this->formatBytes(filesize($fullPath)),
            'filename' => $filename,
        ];
    }

    /**
     * List all existing backups.
     *
     * @return array<int, array{filename: string, size: string, date: string}>
     */
    public function list(): array
    {
        $disk = Storage::disk($this->disk);
        $files = $disk->files($this->backupDir);
        $backups = [];

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'zip') {
                $backups[] = [
                    'filename' => basename($file),
                    'size' => $this->formatBytes($disk->size($file)),
                    'date' => date('Y-m-d H:i:s', $disk->lastModified($file)),
                ];
            }
        }

        // Sort by date descending
        usort($backups, fn ($a, $b) => strcmp($b['date'], $a['date']));

        return $backups;
    }

    /**
     * Delete a specific backup.
     */
    public function delete(string $filename): bool
    {
        $path = "{$this->backupDir}/{$filename}";

        if (Storage::disk($this->disk)->exists($path)) {
            return Storage::disk($this->disk)->delete($path);
        }

        return false;
    }

    /**
     * Get the absolute path for download.
     */
    public function download(string $filename): string
    {
        $disk = Storage::disk($this->disk);
        $path = "{$this->backupDir}/{$filename}";

        if (! $disk->exists($path)) {
            throw new Exception("Backup file not found: {$filename}");
        }

        return $disk->path($path);
    }

    /**
     * Dump the database using mysqldump.
     *
     * @throws Exception
     */
    protected function dumpDatabase(string $outputPath): void
    {
        $config = Config::get('database.connections.mysql');

        // Ensure directory exists
        $dir = dirname($outputPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $command = sprintf(
            'mysqldump --user=%s %s --host=%s --port=%s %s > %s',
            escapeshellarg($config['username']),
            $config['password'] !== null && $config['password'] !== '' ? '--password='.escapeshellarg($config['password']) : '',
            escapeshellarg($config['host']),
            escapeshellarg($config['port']),
            escapeshellarg($config['database']),
            escapeshellarg($outputPath)
        );

        $result = Process::run($command);

        if ($result->failed()) {
            throw new Exception('Database backup failed: '.$result->errorOutput());
        }
    }

    /**
     * Add storage files to the zip archive.
     */
    protected function addStorageFilesToZip(ZipArchive $zip): void
    {
        // For ZipArchive to work with Storage::fake(), we need to use the actual disk path
        $disk = Storage::disk($this->disk);
        $rootPath = $disk->path('');

        if (! is_dir($rootPath)) {
            return;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            // Skip directories
            if (! $file->isFile()) {
                continue;
            }

            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($rootPath));
            $relativePath = ltrim($relativePath, DIRECTORY_SEPARATOR);

            // Skip the backups directory itself to avoid recursion
            if (str_starts_with($relativePath, $this->backupDir)) {
                continue;
            }

            $zip->addFile($filePath, 'storage/'.$relativePath);
        }
    }

    /**
     * Format bytes to human readable format.
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision).' '.$units[$pow];
    }
}
