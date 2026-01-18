<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JoomlaMigrationResource\Pages;
use App\Models\JoomlaMigration;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class JoomlaMigrationResource extends Resource
{
    protected static ?string $model = JoomlaMigration::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 100;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'gray' => JoomlaMigration::STATUS_PENDING,
                        'info' => JoomlaMigration::STATUS_RUNNING,
                        'success' => JoomlaMigration::STATUS_COMPLETED,
                        'danger' => JoomlaMigration::STATUS_FAILED,
                        'warning' => JoomlaMigration::STATUS_ROLLED_BACK,
                    ]),
                TextColumn::make('total_records')
                    ->label('Total')
                    ->numeric(),
                TextColumn::make('processed_records')
                    ->label('Processed')
                    ->numeric(),
                TextColumn::make('failed_records')
                    ->label('Failed')
                    ->numeric()
                    ->color('danger'),
                TextColumn::make('progress')
                    ->label('Progress')
                    ->formatStateUsing(fn(JoomlaMigration $record): string => $record->progress.'%'),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJoomlaMigrations::route('/'),
            'view' => Pages\ViewJoomlaMigration::route('/{record}'),
        ];
    }
}
