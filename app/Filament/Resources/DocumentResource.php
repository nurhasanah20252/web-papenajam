<?php

namespace App\Filament\Resources;

use App\Enums\UserRole;
use App\Filament\Resources\DocumentResource\Pages;
use App\Models\Document;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document';

    protected static ?int $navigationSort = 3;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make(3)
                    ->schema([
                        Group::make()
                            ->schema([
                                Section::make('Document Content')
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
                                        RichEditor::make('description')
                                            ->maxLength(2000)
                                            ->nullable()
                                            ->columnSpanFull(),

                                        FileUpload::make('file_path')
                                            ->label('Document File')
                                            ->required()
                                            ->maxSize(10240) // 10MB
                                            ->directory('documents')
                                            ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                                            ->helperText('Supported formats: PDF, DOC, DOCX, XLS, XLSX (Max 10MB)')
                                            ->live()
                                            ->afterStateUpdated(function ($state, $set, $get) {
                                                if ($state) {
                                                    $file = collect($state)->first();
                                                    if (is_string($file)) {
                                                        $set('file_name', basename($file));
                                                    }
                                                }
                                            }),
                                    ])->columns(2),

                                Section::make('File Metadata')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('file_name')
                                                    ->label('File Name')
                                                    ->readOnly(),
                                                TextInput::make('file_size')
                                                    ->label('File Size (Bytes)')
                                                    ->numeric()
                                                    ->readOnly(),
                                                TextInput::make('mime_type')
                                                    ->label('MIME Type')
                                                    ->readOnly(),
                                                TextInput::make('checksum')
                                                    ->label('SHA256 Checksum')
                                                    ->readOnly(),
                                            ]),
                                    ])
                                    ->collapsed(),
                            ])
                            ->columnSpan(2),

                        Group::make()
                            ->schema([
                                Section::make('Classification')
                                    ->schema([
                                        Select::make('category_id')
                                            ->label('Category')
                                            ->relationship('category', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->nullable(),

                                        DateTimePicker::make('published_at')
                                            ->label('Publication Date')
                                            ->nullable()
                                            ->helperText('Leave empty for immediate publication'),

                                        Forms\Components\Toggle::make('is_public')
                                            ->label('Public Access')
                                            ->helperText('Allow public downloads of this document')
                                            ->default(true),
                                    ]),

                                Section::make('Access & Tags')
                                    ->schema([
                                        TagsInput::make('tags')
                                            ->placeholder('New tag')
                                            ->nullable(),

                                        Select::make('allowed_roles')
                                            ->label('Allowed Roles')
                                            ->multiple()
                                            ->options(collect(UserRole::cases())->mapWithKeys(fn ($r) => [$r->value => $r->label()]))
                                            ->placeholder('Select roles')
                                            ->helperText('Only applies if not public'),
                                    ]),
                            ])
                            ->columnSpan(1),
                    ]),
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
                TextColumn::make('tags')
                    ->badge()
                    ->separator(',')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('allowed_roles')
                    ->label('Allowed Roles')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => UserRole::tryFrom($state)?->label() ?? $state)
                    ->toggleable(isToggledHiddenByDefault: true),
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
                TernaryFilter::make('is_public')
                    ->label('Public Access'),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from'),
                        DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
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
                    BulkAction::make('makePublic')
                        ->label('Make Public')
                        ->icon('heroicon-o-globe-alt')
                        ->action(fn (Collection $records) => $records->each(fn (Document $document): bool => $document->update([
                            'is_public' => true,
                        ])))
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Documents made public'),
                    BulkAction::make('makePrivate')
                        ->label('Make Private')
                        ->icon('heroicon-o-lock-closed')
                        ->action(fn (Collection $records) => $records->each(fn (Document $document): bool => $document->update([
                            'is_public' => false,
                        ])))
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
