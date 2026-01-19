<?php

namespace App\Filament\Pages;

use App\Jobs\ProcessJoomlaMigration;
use App\Models\JoomlaMigration;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class JoomlaMigrationPage extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static string | \UnitEnum | null $navigationGroup = 'System';

    protected static ?int $navigationSort = 100;

    protected string $view = 'filament.pages.joomla-migration';

    protected static ?string $title = 'Joomla Migration';

    public bool $forceMigration = false;

    public ?array $migrationStats = null;

    public bool $isRunning = false;

    public $categoryFile;

    public $contentFile;

    public $newsFile;

    public $menuFile;

    public $documentFile;

    public function mount(): void
    {
        $this->loadStats();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                JoomlaMigration::query()
                    ->latest()
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Migration Name')
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('status')
                    ->colors([
                        'gray' => JoomlaMigration::STATUS_PENDING,
                        'info' => JoomlaMigration::STATUS_RUNNING,
                        'success' => JoomlaMigration::STATUS_COMPLETED,
                        'danger' => JoomlaMigration::STATUS_FAILED,
                        'warning' => JoomlaMigration::STATUS_ROLLED_BACK,
                    ]),
                TextColumn::make('progress')
                    ->formatStateUsing(fn ($state) => $state.'%')
                    ->label('Progress'),
                TextColumn::make('processed_records')
                    ->label('Processed'),
                TextColumn::make('failed_records')
                    ->label('Failed')
                    ->color('danger'),
                TextColumn::make('created_at')
                    ->label('Started')
                    ->dateTime()
                    ->sortable(),
            ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Upload Joomla Export Files')
                ->description('Upload JSON files exported from Joomla')
                ->schema([
                    FileUpload::make('categoryFile')
                        ->label('Categories JSON')
                        ->directory('joomla-migrations')
                        ->acceptedFileTypes(['application/json']),
                    FileUpload::make('contentFile')
                        ->label('Content (Pages) JSON')
                        ->directory('joomla-migrations')
                        ->acceptedFileTypes(['application/json']),
                    FileUpload::make('newsFile')
                        ->label('News JSON')
                        ->directory('joomla-migrations')
                        ->acceptedFileTypes(['application/json']),
                    FileUpload::make('menuFile')
                        ->label('Menus JSON')
                        ->directory('joomla-migrations')
                        ->acceptedFileTypes(['application/json']),
                    FileUpload::make('documentFile')
                        ->label('Documents JSON')
                        ->directory('joomla-migrations')
                        ->acceptedFileTypes(['application/json']),
                    Toggle::make('forceMigration')
                        ->label('Force Re-migration')
                        ->helperText('Re-migrate records that have already been migrated')
                        ->inline(false),
                ])->columns(2),
        ];
    }

    public function startMigration(string $type): void
    {
        $fileProperty = $type.'File';
        $filePath = $this->$fileProperty;

        if (! $filePath) {
            Notification::make()
                ->title('File Required')
                ->body('Please upload a JSON file for '.ucfirst($type))
                ->warning()
                ->send();

            return;
        }

        $fullPath = Storage::disk('public')->path($filePath);

        $migration = JoomlaMigration::create([
            'name' => 'Manual '.ucfirst($type).' Migration '.now()->format('Y-m-d H:i'),
            'status' => JoomlaMigration::STATUS_PENDING,
            'metadata' => ['type' => $type, 'file' => $filePath],
        ]);

        ProcessJoomlaMigration::dispatch($migration->id, $fullPath, $this->getMigrationType($type));

        Notification::make()
            ->title('Migration Started')
            ->body(ucfirst($type).' migration has been queued.')
            ->success()
            ->send();

        $this->isRunning = true;
    }

    protected function getMigrationType(string $type): string
    {
        return match ($type) {
            'category' => JoomlaMigration::TYPE_CATEGORIES,
            'content' => JoomlaMigration::TYPE_PAGES,
            'news' => JoomlaMigration::TYPE_NEWS,
            'menu' => JoomlaMigration::TYPE_MENUS,
            'document' => JoomlaMigration::TYPE_DOCUMENTS,
            default => $type,
        };
    }

    public function validateMigration(): void
    {
        $this->isRunning = true;

        try {
            // Find latest completed migration to validate
            $migration = JoomlaMigration::where('status', JoomlaMigration::STATUS_COMPLETED)->latest()->first();

            if (! $migration) {
                Notification::make()
                    ->title('No Migration Found')
                    ->body('Please run and complete a migration first.')
                    ->warning()
                    ->send();

                return;
            }

            Artisan::call('joomla:validate', [
                '--id' => $migration->id,
            ]);

            $output = Artisan::output();

            $this->migrationStats['validation'] = [
                'output' => $output,
                'migration_id' => $migration->id,
                'timestamp' => now()->toDateTimeString(),
            ];

            Notification::make()
                ->title('Validation Complete')
                ->body('Check the output below for details.')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Validation Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        } finally {
            $this->isRunning = false;
        }
    }

    protected function loadStats(): void
    {
        // For the summary section
        $summary = JoomlaMigration::query()
            ->whereIn('status', [JoomlaMigration::STATUS_COMPLETED, JoomlaMigration::STATUS_RUNNING])
            ->latest()
            ->get()
            ->take(5);

        $this->migrationStats = [
            'recent' => $summary->toArray(),
        ];
    }

    public function getRecentMigrations(): array
    {
        return $this->migrationStats['recent'] ?? [];
    }
}
