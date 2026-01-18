<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageTemplateResource\Pages;
use App\Models\PageTemplate;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class PageTemplateResource extends Resource
{
    protected static ?string $model = PageTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationGroup = 'Structure';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Template Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(100),
                        Textarea::make('description')
                            ->maxLength(500)
                            ->nullable(),
                        Toggle::make('is_default')
                            ->label('Set as default template')
                            ->default(false),
                    ])->columns(2),

                Section::make('Template Structure')
                    ->schema([
                        Forms\Components\KeyValue::make('structure')
                            ->label('Block Structure')
                            ->keyLabel('Block Name')
                            ->valueLabel('Block Configuration')
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
                TextColumn::make('description')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_default')
                    ->boolean()
                    ->label('Default')
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
                Filter::make('default')
                    ->query(fn($query): $query->where('is_default', true))
                    ->label('Default Template'),
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
