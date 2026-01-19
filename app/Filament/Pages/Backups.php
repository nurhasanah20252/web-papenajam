<?php

namespace App\Filament\Pages;

use App\Services\BackupService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Response;

class Backups extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cloud-arrow-up';

    protected static string|\UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.backups';

    /**
     * @var array<int, array{filename: string, size: string, date: string}>
     */
    public array $backups = [];

    public function mount(): void
    {
        $this->loadBackups();
    }

    /**
     * Load existing backups from the service.
     */
    public function loadBackups(): void
    {
        $this->backups = app(BackupService::class)->list();
    }

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('createBackup')
                ->label('Create Backup')
                ->icon('heroicon-o-plus')
                ->action(function (BackupService $backupService): void {
                    try {
                        $backupService->create();

                        Notification::make()
                            ->title('Backup created successfully')
                            ->success()
                            ->send();

                        $this->loadBackups();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Backup failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    /**
     * Download a specific backup file.
     */
    public function downloadBackup(string $filename): mixed
    {
        try {
            $path = app(BackupService::class)->download($filename);

            return Response::download($path);
        } catch (\Exception $e) {
            Notification::make()
                ->title('Download failed')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return null;
        }
    }

    /**
     * Delete a specific backup file.
     */
    public function deleteBackup(string $filename): void
    {
        if (app(BackupService::class)->delete($filename)) {
            Notification::make()
                ->title('Backup deleted successfully')
                ->success()
                ->send();

            $this->loadBackups();
        } else {
            Notification::make()
                ->title('Delete failed')
                ->danger()
                ->send();
        }
    }
}
