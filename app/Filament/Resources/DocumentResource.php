<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages;
use App\Models\Document;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document';

    protected static ?int $navigationSort = 3;

    protected static string | \UnitEnum | null $navigationGroup = 'Content';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Document Information')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, $set, $get) {
                                if (! $get('slug')) {
                                    $set('slug', \Illuminate\Support\Str::slug($state));
                                }
                            }),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Unique URL identifier for the document'),
                        TextInput::make('description')
                            ->maxLength(500)
                            ->nullable()
                            ->columnSpanFull(),
                        Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->nullable(),
                        Select::make('uploaded_by')
                            ->label('Uploaded By')
                            ->relationship('uploader', 'name')
                            ->nullable(),
                    ])->columns(2),

                Section::make('File Upload')
                    ->schema([
                        FileUpload::make('file_path')
                            ->label('Document File')
                            ->required()
                            ->maxSize(10240) // 10MB
                            ->directory('documents')
                            ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                            ->helperText('Supported formats: PDF, DOC, DOCX, XLS, XLSX (Max 10MB)')
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                if ($state) {
                                    $file = collect($state)->first();
                                    if (is_string($file)) {
                                        $set('file_name', basename($file));
                                    }
                                }
                            }),
                        TextInput::make('file_name')
                            ->label('File Name')
                            ->maxLength(255)
                            ->nullable(),
                        TextInput::make('version')
                            ->maxLength(50)
                            ->default('1.0')
                            ->nullable(),
                    ])->columns(2),

                Section::make('File Details')
                    ->schema([
                        TextInput::make('file_type')
                            ->label('File Type')
                            ->maxLength(100)
                            ->nullable()
                            ->default(fn () => request()->file('file_path')?->getClientMimeType()),
                        TextInput::make('checksum')
                            ->label('SHA256 Checksum')
                            ->maxLength(64)
                            ->nullable()
                            ->helperText('Auto-generated on upload for integrity verification'),
                    ])->columns(2),

                Section::make('Publication Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_public')
                            ->label('Public Access')
                            ->helperText('Allow public downloads of this document')
                            ->default(true),
                        DateTimePicker::make('published_at')
                            ->label('Publication Date')
                            ->nullable()
                            ->helperText('Leave empty for immediate publication'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->placeholder('No category'),
                TextColumn::make('file_name')
                    ->label('File Name')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->toggleable(),
                TextColumn::make('file_type')
                    ->label('Type')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('file_size')
                    ->label('Size')
                    ->formatStateUsing(fn ($state): string => self::formatBytes($state))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('version')
                    ->badge()
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('is_public')
                    ->boolean()
                    ->label('Public')
                    ->sortable()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('download_count')
                    ->label('Downloads')
                    ->sortable()
                    ->numeric()
                    ->toggleable(),
                TextColumn::make('uploader.name')
                    ->label('Uploaded By')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not published'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->relationship('category', 'name'),
                Filter::make('public')
                    ->query(fn ($query) => $query->where('is_public', true))
                    ->label('Public Documents'),
                Filter::make('private')
                    ->query(fn ($query) => $query->where('is_public', false))
                    ->label('Private Documents'),
                Filter::make('published')
                    ->query(fn ($query) => $query->whereNotNull('published_at'))
                    ->label('Published'),
                Filter::make('unpublished')
                    ->query(fn ($query) => $query->whereNull('published_at'))
                    ->label('Unpublished'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Document $record): string => $record->getFileUrl())
                    ->openUrlInNewTab()
                    ->visible(fn (Document $record): bool => $record->isDownloadable()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('publish')
                        ->label('Publish')
                        ->action(fn (Collection $records) => $records->each(fn (Document $document): bool => $document->update([
                            'is_public' => true,
                            'published_at' => now(),
                        ])))
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Documents published'),
                    BulkAction::make('make_private')
                        ->label('Make Private')
                        ->action(fn (Collection $records) => $records->each(fn (Document $document): bool => $document->update(['is_public' => false])))
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Documents made private')
                        ->color('warning'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
        ];
    }

    protected static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision).' '.$units[$pow];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_public', false)->count() > 0
            ? static::getModel()::where('is_public', false)->count().' private'
            : null;
    }
}
