<?php

namespace App\Filament\Pages;

use App\Models\JoomlaMigration;
use App\Services\JoomlaMigration\JoomlaMigrationManager;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class JoomlaMigrationPage extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 100;

    protected static string $view = 'filament.pages.joomla-migration';

    public ?string $migrationName = null;

    public $uploadedFile = null;

    public bool $isMigrating = false;

    public int $currentProgress = 0;

    public int $totalRecords = 0;

    protected JoomlaMigration $currentMigration;

    public function mount(): void
    {
        $this->migrationName = 'Joomla Migration '.now()->format('Y-m-d H:i:s');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(JoomlaMigration::query()->latest())
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable(),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'gray' => JoomlaMigration::STATUS_PENDING,
                        'info' => JoomlaMigration::STATUS_RUNNING,
                        'success' => JoomlaMigration::STATUS_COMPLETED,
                        'danger' => JoomlaMigration::STATUS_FAILED,
                        'warning' => JoomlaMigration::STATUS_ROLLED_BACK,
                    ]),
                TextColumn::make('total_records')
                    ->label('Total')
                    ->numeric(),
                TextColumn::make('processed_records')
                    ->label('Processed')
                    ->numeric(),
                TextColumn::make('failed_records')
                    ->label('Failed')
                    ->numeric()
                    ->color('danger'),
                TextColumn::make('progress')
                    ->label('Progress')
                    ->formatStateUsing(fn(JoomlaMigration $record): string => $record->progress.'%'),
                TextColumn::make('started_at')
                    ->label('Started')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('completed_at')
                    ->label('Completed')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Action::make('view')
                    ->label('View Details')
                    ->icon('heroicon-o-eye')
                    ->modalContent(function (JoomlaMigration $record) {
                        return view('filament.pages.joomla-migration-details', ['migration' => $record]);
                    }),
                Action::make('rollback')
                    ->label('Rollback')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn(JoomlaMigration $record): bool => $record->isComplete())
                    ->action(fn(JoomlaMigration $record) => $this->rollbackMigration($record)),
                DeleteAction::make(),
                RestoreAction::make(),
            ])
            ->bulkActions([
                BulkAction::make('rollback_selected')
                    ->label('Rollback Selected')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Collection $records): void {
                        foreach ($records as $record) {
                            if ($record->isComplete()) {
                                $this->rollbackMigration($record);
                            }
                        }
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Upload Joomla Export')
                ->description('Upload a JSON file exported from Joomla 3.x')
                ->schema([
                    TextInput::make('migrationName')
                        ->label('Migration Name')
                        ->default($this->migrationName)
                        ->required(),
                    FileUpload::make('uploadedFile')
                        ->label('Joomla Export File')
                        ->acceptedFileTypes(['application/json'])
                        ->maxSize(51200) // 50MB
                        ->required(),
                ]),
        ];
    }

    public function startMigration(): void
    {
        $this->validate([
            'migrationName' => 'required|string|max:255',
            'uploadedFile' => 'required|file|mimes:json',
        ]);

        $this->isMigrating = true;
        $this->currentProgress = 0;

        try {
            $jsonContent = file_get_contents($this->uploadedFile->getRealPath());
            $joomlaData = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Notification::make()
                    ->title('Invalid JSON')
                    ->body('The uploaded file is not valid JSON: '.json_last_error_msg())
                    ->danger()
                    ->send();

                $this->isMigrating = false;

                return;
            }

            // Validate basic structure
            $this->validateJoomlaData($joomlaData);

            // Run migration
            $manager = app(JoomlaMigrationManager::class);
            $migration = $manager->runMigration($joomlaData, $this->migrationName);

            $this->currentMigration = $migration;

            Notification::make()
                ->title('Migration Complete')
                ->body("Migration '{$migration->name}' has been completed successfully.")
                ->success()
                ->send();

            $this->isMigrating = false;
            $this->uploadedFile = null;

            $this->redirectRoute('filament.admin.pages.joomla-migration');
        } catch (\Exception $e) {
            Notification::make()
                ->title('Migration Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->isMigrating = false;

            throw $e;
        }
    }

    protected function validateJoomlaData(array $data): void
    {
        // Check for expected keys
        $expectedKeys = ['categories', 'articles', 'news', 'menus', 'menu_items', 'documents'];
        $hasData = false;

        foreach ($expectedKeys as $key) {
            if (isset($data[$key]) && is_array($data[$key]) && !empty($data[$key])) {
                $hasData = true;
            }
        }

        if (!$hasData && empty($data)) {
            throw new \Exception('The uploaded file does not contain any valid Joomla data.');
        }
    }

    public function rollbackMigration(JoomlaMigration $migration): bool
    {
        try {
            $manager = app(JoomlaMigrationManager::class);
            $result = $manager->rollback($migration);

            if ($result) {
                Notification::make()
                    ->title('Rollback Complete')
                    ->body("Migration '{$migration->name}' has been rolled back.")
                    ->success()
                    ->send();
            }

            return $result;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Rollback Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return false;
        }
    }

    protected function getViewData(): array
    {
        return [
            'migrations' => JoomlaMigration::query()->latest()->take(10)->get(),
        ];
    }
}
