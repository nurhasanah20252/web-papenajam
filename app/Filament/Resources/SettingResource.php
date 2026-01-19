<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?int $navigationSort = 100;

    protected static ?string $navigationLabel = 'Settings';

    protected static ?string $modelLabel = 'Setting';

    protected static ?string $pluralModelLabel = 'Settings';

    protected static string | \UnitEnum | null $navigationGroup = 'System';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Setting Information')
                    ->schema([
                        TextInput::make('key')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Unique identifier for this setting (e.g., site_name)')
                            ->reactive()
                            ->afterStateUpdated(fn ($state, $set) => $set('key', Str::slug($state, '_'))),
                        TextInput::make('value')
                            ->required()
                            ->maxLength(65535)
                            ->helperText('The setting value'),
                        TextInput::make('group')
                            ->required()
                            ->maxLength(100)
                            ->default('general')
                            ->helperText('Group this setting belongs to (e.g., site, seo, social)'),
                        TextInput::make('type')
                            ->required()
                            ->maxLength(50)
                            ->default('text')
                            ->helperText('Data type: text, boolean, integer, float, json'),
                        Toggle::make('is_public')
                            ->label('Public')
                            ->helperText('Can this setting be accessed on the frontend?')
                            ->default(false),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('group')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'site' => 'primary',
                        'seo' => 'success',
                        'social' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('key')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('value')
                    ->searchable()
                    ->limit(50)
                    ->wrap(),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'boolean' => 'success',
                        'integer' => 'primary',
                        'json' => 'warning',
                        default => 'gray',
                    }),
                IconColumn::make('is_public')
                    ->label('Public')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('group')
                    ->options([
                        'general' => 'General',
                        'site' => 'Site',
                        'seo' => 'SEO',
                        'social' => 'Social Media',
                    ]),
                SelectFilter::make('type')
                    ->options([
                        'text' => 'Text',
                        'boolean' => 'Boolean',
                        'integer' => 'Integer',
                        'float' => 'Float',
                        'json' => 'JSON',
                    ]),
                Tables\Filters\TernaryFilter::make('is_public')
                    ->label('Public Settings')
                    ->placeholder('All settings')
                    ->trueLabel('Public only')
                    ->falseLabel('Private only'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('group', 'asc')
            ->reorderable('group');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
