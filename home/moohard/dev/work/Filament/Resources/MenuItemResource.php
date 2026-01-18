<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuItemResource\Pages;
use App\Models\MenuItem;
use Filament\Forms;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
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

class MenuItemResource extends Resource
{
    protected static ?string $model = MenuItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationGroup = 'Structure';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Menu Item Information')
                    ->schema([
                        Select::make('menu_id')
                            ->relationship('menu', 'name')
                            ->required(),
                        Select::make('parent_id')
                            ->label('Parent Item')
                            ->relationship('parent', 'title')
                            ->nullable()
                            ->searchable()
                            ->preload(),
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Select::make('type')
                            ->options([
                                'custom' => 'Custom URL',
                                'route' => 'Route',
                                'page' => 'Page',
                                'external' => 'External Link',
                            ])
                            ->required()
                            ->default('custom')
                            ->reactive(),
                    ])->columns(2),

                Section::make('Link Details')
                    ->schema([
                        TextInput::make('url')
                            ->label('URL')
                            ->maxLength(500)
                            ->nullable()
                            ->hidden(fn($get): bool => $get('type') !== 'custom' && $get('type') !== 'external'),
                        Select::make('page_id')
                            ->label('Page')
                            ->relationship('page', 'title')
                            ->nullable()
                            ->searchable()
                            ->preload()
                            ->hidden(fn($get): bool => $get('type') !== 'page'),
                        TextInput::make('icon')
                            ->label('Icon Class')
                            ->maxLength(100)
                            ->placeholder('heroicon-o-name')
                            ->helperText('Use Heroicons class names'),
                        TextInput::make('css_class')
                            ->label('CSS Class')
                            ->maxLength(255)
                            ->nullable(),
                        Toggle::make('target_blank')
                            ->label('Open in new tab')
                            ->default(false),
                    ])->columns(2),

                Section::name('Ordering & Status')
                    ->schema([
                        TextInput::make('order')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        Toggle::make('is_active')
                            ->default(true),
                    ])->columns(2),

                Section::name('Conditional Display')
                    ->schema([
                        KeyValue::make('conditional_rules')
                            ->label('Display Conditions')
                            ->keyLabel('Rule')
                            ->valueLabel('Value')
                            ->nullable(),
                    ]),
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
                TextColumn::make('type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'custom' => 'gray',
                        'route' => 'info',
                        'page' => 'success',
                        'external' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('menu.name')
                    ->label('Menu')
                    ->sortable(),
                TextColumn::make('parent.title')
                    ->label('Parent')
                    ->sortable()
                    ->placeholder('None'),
                TextColumn::make('icon')
                    ->label('Icon')
                    ->formatStateUsing(fn($state): string => $state ?? '-')
                    ->limit(30),
                IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('order')
            ->filters([
                SelectFilter::make('menu')
                    ->relationship('menu', 'name'),
                SelectFilter::make('type')
                    ->options([
                        'custom' => 'Custom URL',
                        'route' => 'Route',
                        'page' => 'Page',
                        'external' => 'External Link',
                    ]),
                Filter::make('active')
                    ->query(fn($query): $query->where('is_active', true))
                    ->label('Active Items'),
                Filter::make('inactive')
                    ->query(fn($query): $query->where('is_active', false))
                    ->label('Inactive Items'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('moveUp')
                    ->label('Move Up')
                    ->icon('heroicon-o-arrow-up')
                    ->action(fn(MenuItem $record): int => $record->moveOrderUp())
                    ->hidden(fn(MenuItem $record): bool => $record->order === 0),
                Tables\Actions\Action::make('moveDown')
                    ->label('Move Down')
                    ->icon('heroicon-o-arrow-down')
                    ->action(fn(MenuItem $record): int => $record->moveOrderDown()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label('Activate')
                        ->action(fn(Collection $records): void => $records->each(fn(MenuItem $item): bool => $item->update(['is_active' => true])))
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Items activated'),
                    BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->action(fn(Collection $records): void => $records->each(fn(MenuItem $item): bool => $item->update(['is_active' => false])))
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Items deactivated')
                        ->color('warning'),
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
