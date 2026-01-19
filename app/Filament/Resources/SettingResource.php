<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?int $navigationSort = 100;

    protected static ?string $navigationLabel = 'Settings';

    protected static ?string $modelLabel = 'Setting';

    protected static ?string $pluralModelLabel = 'Settings';

    protected static string|\UnitEnum|null $navigationGroup = 'System';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make(3)
                    ->schema([
                        Group::make()
                            ->schema([
                                Section::make('Configuration')
                                    ->schema([
                                        TextInput::make('key')
                                            ->required()
                                            ->maxLength(255)
                                            ->unique(ignoreRecord: true)
                                            ->helperText('Unique identifier for this setting (e.g., site_name)')
                                            ->reactive()
                                            ->afterStateUpdated(fn ($state, $set) => $set('key', Str::slug($state, '_'))),

                                        Textarea::make('value')
                                            ->label('Value')
                                            ->visible(fn (Get $get) => $get('type') === 'text' || ! $get('type'))
                                            ->required()
                                            ->rows(5)
                                            ->helperText('The setting value as text'),

                                        Toggle::make('value')
                                            ->label('Value')
                                            ->visible(fn (Get $get) => $get('type') === 'boolean')
                                            ->dehydrateStateUsing(fn ($state) => $state ? '1' : '0')
                                            ->formatStateUsing(fn ($state) => (bool) $state)
                                            ->helperText('The setting value as boolean'),

                                        TextInput::make('value')
                                            ->label('Value')
                                            ->visible(fn (Get $get) => in_array($get('type'), ['integer', 'float']))
                                            ->numeric()
                                            ->required()
                                            ->helperText('The setting value as number'),

                                        KeyValue::make('value')
                                            ->label('Value')
                                            ->visible(fn (Get $get) => $get('type') === 'json')
                                            ->dehydrateStateUsing(fn ($state) => json_encode($state))
                                            ->formatStateUsing(fn ($state) => is_string($state) ? json_decode($state, true) : $state)
                                            ->helperText('The setting value as JSON'),

                                        FileUpload::make('value')
                                            ->label('Value')
                                            ->visible(fn (Get $get) => $get('type') === 'image')
                                            ->image()
                                            ->directory('settings')
                                            ->helperText('The setting value as an image'),
                                    ]),
                            ])
                            ->columnSpan(2),

                        Group::make()
                            ->schema([
                                Section::make('Metadata')
                                    ->schema([
                                        Select::make('group')
                                            ->options([
                                                'site' => 'Site',
                                                'seo' => 'SEO',
                                                'social' => 'Social',
                                                'general' => 'General',
                                            ])
                                            ->required()
                                            ->default('general'),

                                        Select::make('type')
                                            ->options([
                                                'text' => 'Text',
                                                'boolean' => 'Boolean',
                                                'integer' => 'Integer',
                                                'float' => 'Float',
                                                'json' => 'JSON',
                                                'image' => 'Image',
                                            ])
                                            ->required()
                                            ->default('text')
                                            ->reactive()
                                            ->afterStateUpdated(fn (Set $set) => $set('value', null)),

                                        Toggle::make('is_public')
                                            ->label('Publicly Accessible')
                                            ->helperText('Can this setting be accessed on the frontend?')
                                            ->default(false),
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
                    Tables\Actions\BulkAction::make('makePublic')
                        ->label('Make Public Selected')
                        ->icon('heroicon-o-globe-alt')
                        ->action(fn (Collection $records) => $records->each(fn (Setting $setting): bool => $setting->update(['is_public' => true])))
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Settings made public')
                        ->color('success'),
                    Tables\Actions\BulkAction::make('makePrivate')
                        ->label('Make Private Selected')
                        ->icon('heroicon-o-lock-closed')
                        ->action(fn (Collection $records) => $records->each(fn (Setting $setting): bool => $setting->update(['is_public' => false])))
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Settings made private')
                        ->color('warning'),
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
