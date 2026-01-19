<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JoomlaMigrationResource\Pages;
use App\Models\JoomlaMigration;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class JoomlaMigrationResource extends Resource
{
    protected static ?string $model = JoomlaMigration::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static string | \UnitEnum | null $navigationGroup = 'System';

    protected static ?int $navigationSort = 100;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Migration Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->default('Joomla Migration '.now()->format('Y-m-d H:i:s')),
                        Forms\Components\Textarea::make('metadata')
                            ->label('Description')
                            ->rows(3),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'gray' => JoomlaMigration::STATUS_PENDING,
                        'info' => JoomlaMigration::STATUS_RUNNING,
                        'success' => JoomlaMigration::STATUS_COMPLETED,
                        'danger' => JoomlaMigration::STATUS_FAILED,
                        'warning' => JoomlaMigration::STATUS_ROLLED_BACK,
                    ]),
                Tables\Columns\TextColumn::make('total_records')
                    ->label('Total')
                    ->numeric(),
                Tables\Columns\TextColumn::make('processed_records')
                    ->label('Processed')
                    ->numeric(),
                Tables\Columns\TextColumn::make('failed_records')
                    ->label('Failed')
                    ->numeric()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('progress')
                    ->label('Progress')
                    ->formatStateUsing(fn (JoomlaMigration $record): string => $record->progress.'%')
                    ->badge()
                    ->color(fn (JoomlaMigration $record): string => match (true) {
                        $record->progress >= 100 => 'success',
                        $record->progress > 0 => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        JoomlaMigration::STATUS_PENDING => 'Pending',
                        JoomlaMigration::STATUS_RUNNING => 'Running',
                        JoomlaMigration::STATUS_COMPLETED => 'Completed',
                        JoomlaMigration::STATUS_FAILED => 'Failed',
                        JoomlaMigration::STATUS_ROLLED_BACK => 'Rolled Back',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJoomlaMigrations::route('/'),
            'create' => Pages\CreateJoomlaMigration::route('/create'),
            'view' => Pages\ViewJoomlaMigration::route('/{record}'),
            'edit' => Pages\EditJoomlaMigration::route('/{record}/edit'),
        ];
    }
}
