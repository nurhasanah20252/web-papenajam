<?php

namespace App\Filament\Resources;

use App\Enums\NewsStatus;
use App\Filament\Resources\NewsResource\Pages;
use App\Models\News;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class NewsResource extends Resource
{
    protected static ?string $model = News::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-newspaper';

    protected static ?int $navigationSort = 2;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Basic Information')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->reactive()
                            ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->nullable(),
                        Select::make('author_id')
                            ->label('Author')
                            ->relationship('author', 'name')
                            ->nullable(),
                    ])->columns(2),

                Section::make('Content')
                    ->schema([
                        TextInput::make('excerpt')
                            ->maxLength(500)
                            ->nullable(),
                        Forms\Components\RichEditor::make('content')
                            ->nullable()
                            ->columnSpanFull(),
                        FileUpload::make('featured_image')
                            ->image()
                            ->maxSize(2048)
                            ->directory('news')
                            ->nullable(),
                    ]),

                Section::make('Classification')
                    ->schema([
                        TagsInput::make('tags')
                            ->placeholder('Add tags...')
                            ->separator(','),
                        Forms\Components\Toggle::make('is_featured')
                            ->default(false),
                    ])->columns(2),

                Section::make('Status')
                    ->schema([
                        Select::make('status')
                            ->options(NewsStatus::class)
                            ->enum(NewsStatus::class)
                            ->required()
                            ->default(NewsStatus::Draft),
                        DateTimePicker::make('published_at')
                            ->nullable(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('featured_image')
                    ->square()
                    ->size(40)
                    ->visibility('private')
                    ->placeholder(),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('slug')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->placeholder('No category'),
                TextColumn::make('author.name')
                    ->label('Author')
                    ->sortable()
                    ->placeholder('Unknown'),
                TextColumn::make('status')
                    ->formatStateUsing(fn (NewsStatus $state): string => $state->label())
                    ->badge()
                    ->color(fn (NewsStatus $state): string => match ($state) {
                        NewsStatus::Published => 'success',
                        NewsStatus::Draft => 'warning',
                        NewsStatus::Archived => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
                IconColumn::make('is_featured')
                    ->boolean()
                    ->label('Featured')
                    ->sortable(),
                TextColumn::make('views_count')
                    ->label('Views')
                    ->sortable()
                    ->numeric()
                    ->toggleable(),
                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(NewsStatus::class),
                SelectFilter::make('category')
                    ->relationship('category', 'name'),
                TernaryFilter::make('is_featured')
                    ->label('Featured'),
                TernaryFilter::make('is_published')
                    ->label('Published Status')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('published_at'),
                        false: fn (Builder $query) => $query->whereNull('published_at'),
                        blank: fn (Builder $query) => $query,
                    ),
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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('publish')
                        ->label('Publish Selected')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn (Collection $records) => $records->each(fn (News $news): bool => $news->update([
                            'status' => NewsStatus::Published,
                            'published_at' => now(),
                        ])))
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('News published'),
                    BulkAction::make('unpublish')
                        ->label('Unpublish Selected')
                        ->icon('heroicon-o-x-circle')
                        ->action(fn (Collection $records) => $records->each(fn (News $news): bool => $news->update([
                            'status' => NewsStatus::Draft,
                            'published_at' => null,
                        ])))
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('News unpublished')
                        ->color('warning'),
                    BulkAction::make('archive')
                        ->label('Archive Selected')
                        ->icon('heroicon-o-archive-box')
                        ->action(fn (Collection $records) => $records->each(fn (News $news): bool => $news->update([
                            'status' => NewsStatus::Archived,
                        ])))
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('News archived')
                        ->color('gray'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNews::route('/'),
            'create' => Pages\CreateNews::route('/create'),
            'edit' => Pages\EditNews::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', NewsStatus::Draft)->count() > 0
            ? static::getModel()::where('status', NewsStatus::Draft)->count().' drafts'
            : null;
    }
}
