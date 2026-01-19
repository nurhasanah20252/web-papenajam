<?php

namespace App\Filament\Resources;

use App\Enums\MenuLocation;
use App\Filament\Resources\MenuResource\Pages;
use App\Models\Menu;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-bars-3';

    protected static ?int $navigationSort = 2;

    protected static string | \UnitEnum | null $navigationGroup = 'Structure';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Menu Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\Select::make('location')
                            ->options(fn (): array => collect(MenuLocation::cases())->pluck('label', 'value')->toArray())
                            ->required()
                            ->enum(MenuLocation::class),
                        TextInput::make('max_depth')
                            ->label('Maximum Depth')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(10)
                            ->default(3)
                            ->helperText('Maximum nesting level for menu items'),
                    ])->columns(2),

                Section::make('Description')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->nullable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('location')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('max_depth')
                    ->label('Max Depth')
                    ->sortable()
                    ->placeholder('Unlimited'),
                TextColumn::make('description')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Items')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('location')
                    ->options(fn (): array => collect(MenuLocation::cases())->pluck('label', 'value')->toArray())
                    ->query(fn (Builder $query, array $data) => $query->when(
                        $data['value'],
                        fn ($query, $value) => $query->byLocation($value)
                    )),
                Tables\Filters\TernaryFilter::make('has_items')
                    ->label('Has Items')
                    ->queries(
                        true: fn (Builder $query) => $query->has('items'),
                        false: fn (Builder $query) => $query->doesntHave('items'),
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('manage_items')
                    ->label('Visual Editor')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn (Menu $record): string => route('admin.menus.edit', $record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenus::route('/'),
            'create' => Pages\CreateMenu::route('/create'),
            'edit' => Pages\EditMenu::route('/{record}/edit'),
        ];
    }
}
