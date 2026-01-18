<?php

namespace App\Filament\Resources\JoomlaMigrationResource\Pages;

use App\Filament\Resources\JoomlaMigrationResource;
use App\Models\JoomlaMigration;
use App\Models\JoomlaMigrationItem;
use App\Services\JoomlaMigration\JoomlaMigrationManager;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ViewJoomlaMigration extends ViewRecord
{
    protected static string $resource = JoomlaMigrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('rollback')
                ->label('Rollback Migration')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn(JoomlaMigration $record): bool => $record->isComplete())
                ->action(fn(JoomlaMigration $record) => $this->rollbackMigration($record)),
            DeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function rollbackMigration(JoomlaMigration $migration): void
    {
        $manager = app(JoomlaMigrationManager::class);
        $manager->rollback($migration);

        $this->refresh();
    }

    protected function table(Table $table): Table
    {
        return $table
            ->relationship('items')
            ->columns([
                TextColumn::make('joomla_id')
                    ->label('Joomla ID')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn(string $state): string => ucfirst($state)),
                TextColumn::make('local_model')
                    ->label('Local Model')
                    ->formatStateUsing(fn(?string $state): string => $state ? class_basename($state) : '-'),
                TextColumn::make('local_id')
                    ->label('Local ID')
                    ->numeric(),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'gray' => JoomlaMigrationItem::STATUS_PENDING,
                        'info' => JoomlaMigrationItem::STATUS_PROCESSING,
                        'success' => JoomlaMigrationItem::STATUS_COMPLETED,
                        'danger' => JoomlaMigrationItem::STATUS_FAILED,
                        'warning' => JoomlaMigrationItem::STATUS_SKIPPED,
                    ]),
                TextColumn::make('error_message')
                    ->label('Error')
                    ->limit(50)
                    ->tooltip(fn(?string $state): ?string => $state),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'categories' => 'Categories',
                        'pages' => 'Pages',
                        'news' => 'News',
                        'menus' => 'Menus',
                        'documents' => 'Documents',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'skipped' => 'Skipped',
                    ]),
            ]);
    }
}
