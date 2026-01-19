<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 1;

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
                        Select::make('template_id')
                            ->label('Template')
                            ->relationship('template', 'name')
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
                        RichEditor::make('content')
                            ->nullable()
                            ->columnSpanFull()
                            ->hidden(fn (Forms\Get $get) => $get('is_builder_enabled')),
                        FileUpload::make('featured_image')
                            ->image()
                            ->maxSize(2048)
                            ->directory('pages')
                            ->nullable(),
                    ]),

                Section::make('SEO & Meta')
                    ->schema([
                        TextInput::make('meta.title')
                            ->label('Meta Title')
                            ->maxLength(255)
                            ->nullable(),
                        TextInput::make('meta.description')
                            ->label('Meta Description')
                            ->maxLength(500)
                            ->nullable(),
                    ])->columns(2),

                Section::make('Status')
                    ->schema([
                        Toggle::make('is_builder_enabled')
                            ->label('Enable Page Builder')
                            ->helperText('Use the drag-and-drop builder for this page.')
                            ->reactive()
                            ->columnSpanFull(),
                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                                'archived' => 'Archived',
                            ])
                            ->required()
                            ->default('draft'),
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
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'draft' => 'warning',
                        'archived' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('template.name')
                    ->label('Template')
                    ->sortable()
                    ->placeholder('No template'),
                TextColumn::make('author.name')
                    ->label('Author')
                    ->sortable()
                    ->placeholder('Unknown'),
                IconColumn::make('published_at')
                    ->boolean()
                    ->label('Published')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ]),
                SelectFilter::make('template')
                    ->relationship('template', 'name'),
                SelectFilter::make('author')
                    ->relationship('author', 'name'),
                Filter::make('published')
                    ->query(fn ($query) => $query->whereNotNull('published_at'))
                    ->label('Published Pages'),
                Filter::make('unpublished')
                    ->query(fn ($query) => $query->whereNull('published_at'))
                    ->label('Unpublished Pages'),
                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Created from'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Created until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn ($query) => $query->whereDate('created_at', '>=', $data['created_from'])
                            )
                            ->when(
                                $data['created_until'],
                                fn ($query) => $query->whereDate('created_at', '<=', $data['created_until'])
                            );
                    })
                    ->indicateUsing(function (array $data) {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Created from '.$data['created_from'];
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Created until '.$data['created_until'];
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Page $record): string => route('page.show', $record->slug))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
                Action::make('page-builder')
                    ->label('Page Builder')
                    ->icon('heroicon-o-cube')
                    ->url(fn (Page $record): string => route('builder.edit', $record))
                    ->openUrlInNewTab(false),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('publish')
                        ->label('Publish Selected')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn (Collection $records) => $records->each(fn (Page $page) => $page->update([
                            'status' => 'published',
                            'published_at' => now(),
                        ])))
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Pages published')
                        ->color('success'),
                    BulkAction::make('unpublish')
                        ->label('Unpublish Selected')
                        ->icon('heroicon-o-x-circle')
                        ->action(fn (Collection $records) => $records->each(fn (Page $page) => $page->update([
                            'status' => 'draft',
                            'published_at' => null,
                        ])))
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Pages unpublished')
                        ->color('warning'),
                    BulkAction::make('archive')
                        ->label('Archive Selected')
                        ->icon('heroicon-o-archive-box')
                        ->action(fn (Collection $records) => $records->each(fn (Page $page) => $page->update(['status' => 'archived'])))
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Pages archived')
                        ->color('gray')
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'draft')->count() > 0
            ? static::getModel()::where('status', 'draft')->count().' drafts'
            : null;
    }
}
