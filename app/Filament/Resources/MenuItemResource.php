<?php

namespace App\Filament\Resources;

use App\Enums\UrlType;
use App\Filament\Resources\MenuItemResource\Pages;
use App\Models\MenuItem;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MenuItemResource extends Resource
{
    protected static ?string $model = MenuItem::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?int $navigationSort = 3;

    protected static string | \UnitEnum | null $navigationGroup = 'Structure';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Menu Item Information')
                    ->schema([
                        Select::make('menu_id')
                            ->relationship('menu', 'name')
                            ->required()
                            ->live(),
                        Select::make('parent_id')
                            ->label('Parent Item')
                            ->options(fn (Forms\Get $get): array => \App\Models\MenuItem::query()
                                ->where('menu_id', $get('menu_id'))
                                ->whereNull('parent_id')
                                ->pluck('title', 'id')
                                ->toArray())
                            ->nullable()
                            ->searchable()
                            ->preload()
                            ->live(),
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Select::make('url_type')
                            ->options(fn (): array => collect(UrlType::cases())->pluck('label', 'value')->toArray())
                            ->required()
                            ->default(UrlType::Custom)
                            ->enum(UrlType::class)
                            ->live(),
                    ])->columns(2),

                Section::make('Link Details')
                    ->schema([
                        TextInput::make('route_name')
                            ->label('Route Name')
                            ->maxLength(255)
                            ->nullable()
                            ->visible(fn (Forms\Get $get): bool => $get('url_type') === UrlType::Route->value),
                        Select::make('page_id')
                            ->label('Page')
                            ->relationship('page', 'title')
                            ->nullable()
                            ->searchable()
                            ->preload()
                            ->visible(fn (Forms\Get $get): bool => $get('url_type') === UrlType::Page->value),
                        TextInput::make('custom_url')
                            ->label('URL')
                            ->maxLength(500)
                            ->nullable()
                            ->visible(fn (Forms\Get $get): bool => in_array($get('url_type'), [UrlType::Custom->value, UrlType::External->value])),
                        TextInput::make('icon')
                            ->label('Icon Class')
                            ->maxLength(100)
                            ->placeholder('heroicon-o-name')
                            ->helperText('Use Heroicons class names'),
                        Toggle::make('target_blank')
                            ->label('Open in new tab')
                            ->default(false),
                    ])->columns(2),

                Section::make('Ordering & Status')
                    ->schema([
                        TextInput::make('order')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        Toggle::make('is_active')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order')
                    ->label('#')
                    ->width(60)
                    ->sortable(),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('url_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        UrlType::Custom->value => 'gray',
                        UrlType::Route->value => 'info',
                        UrlType::Page->value => 'success',
                        UrlType::External->value => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('menu.name')
                    ->label('Menu')
                    ->sortable(),
                TextColumn::make('parent.title')
                    ->label('Parent')
                    ->sortable()
                    ->placeholder('None')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('icon')
                    ->label('Icon')
                    ->formatStateUsing(fn ($state): string => $state ?? '-')
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('order')
            ->reorderable('order')
            ->filters([
                SelectFilter::make('menu')
                    ->relationship('menu', 'name')
                    ->indicator(fn (SelectFilter $filter): array => match (true) {
                        $filter->isActive() => [$filter->getIndicator()],
                        default => [],
                    }),
                SelectFilter::make('url_type')
                    ->options(fn (): array => collect(UrlType::cases())->pluck('label', 'value')->toArray()),
                SelectFilter::make('is_active')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ])
                    ->label('Status'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenuItems::route('/'),
            'create' => Pages\CreateMenuItem::route('/create'),
            'edit' => Pages\EditMenuItem::route('/{record}/edit'),
        ];
    }
}
