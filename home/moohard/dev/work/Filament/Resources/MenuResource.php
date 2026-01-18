<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuResource\Pages;
use App\Models\Menu;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;

    protected static ?string $navigationIcon = 'heroicon-o-bars-3';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationGroup = 'Structure';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Menu Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('location')
                            ->required()
                            ->maxLength(100)
                            ->helperText('e.g., header, footer, sidebar'),
                        TextInput::make('max_depth')
                            ->label('Maximum Depth')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(10)
                            ->nullable()
                            ->helperText('Maximum nesting level for menu items'),
                    ])->columns(2),

                Section::make('Status')
                    ->schema([
                        Toggle::make('is_active')
                            ->default(true),
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
                IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Items'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('active')
                    ->query(fn($query): $query->where('is_active', true))
                    ->label('Active Menus'),
                Filter::make('inactive')
                    ->query(fn($query): $query->where('is_active', false))
                    ->label('Inactive Menus'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('manage_items')
                    ->label('Manage Items')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn(Menu $record): string => MenuItemResource::getUrl('index', ['tableFilters[menu][value]' => $record->id])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label('Activate')
                        ->action(fn(Collection $records): void => $records->each(fn(Menu $menu): bool => $menu->update(['is_active' => true])))
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Menus activated'),
                    BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->action(fn(Collection $records): void => $records->each(fn(Menu $menu): bool => $menu->update(['is_active' => false])))
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Menus deactivated')
                        ->color('warning'),
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
