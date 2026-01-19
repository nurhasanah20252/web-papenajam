<?php

namespace App\Filament\Resources;

use App\Enums\BlockType;
use App\Filament\Resources\PageTemplateResource\Pages;
use App\Models\PageTemplate;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class PageTemplateResource extends Resource
{
    protected static ?string $model = PageTemplate::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?int $navigationSort = 1;

    protected static string|\UnitEnum|null $navigationGroup = 'Structure';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Template Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(100),
                        Toggle::make('is_system')
                            ->label('System Template')
                            ->helperText('System templates are protected and used for core functionality.')
                            ->default(false),
                        Forms\Components\FileUpload::make('thumbnail')
                            ->image()
                            ->directory('templates/thumbnails')
                            ->nullable(),
                        Textarea::make('description')
                            ->maxLength(500)
                            ->nullable()
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Template Structure')
                    ->schema([
                        Forms\Components\Repeater::make('content')
                            ->label('Default Blocks')
                            ->schema([
                                Forms\Components\Select::make('type')
                                    ->options(BlockType::class)
                                    ->required()
                                    ->reactive(),
                                Forms\Components\KeyValue::make('content')
                                    ->label('Default Content')
                                    ->keyLabel('Field Name')
                                    ->valueLabel('Value')
                                    ->nullable(),
                                Forms\Components\KeyValue::make('settings')
                                    ->label('Default Settings')
                                    ->keyLabel('Setting Name')
                                    ->valueLabel('Value')
                                    ->nullable(),
                            ])
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => isset($state['type']) ? (BlockType::tryFrom($state['type'])?->label() ?? $state['type']) : 'New Block'
                            )
                            ->columnSpanFull(),
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
                TextColumn::make('description')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_system')
                    ->boolean()
                    ->label('System')
                    ->sortable(),
                TextColumn::make('pages_count')
                    ->counts('pages')
                    ->label('Pages'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('system')
                    ->query(fn ($query) => $query->where('is_system', true))
                    ->label('System Templates'),
                Filter::make('custom')
                    ->query(fn ($query) => $query->where('is_system', false))
                    ->label('Custom Templates'),
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
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPageTemplates::route('/'),
            'create' => Pages\CreatePageTemplate::route('/create'),
            'edit' => Pages\EditPageTemplate::route('/{record}/edit'),
        ];
    }
}
