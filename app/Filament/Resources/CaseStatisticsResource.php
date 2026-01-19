<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CaseStatisticsResource\Pages;
use App\Models\CaseStatistics;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class CaseStatisticsResource extends Resource
{
    protected static ?string $model = CaseStatistics::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?int $navigationSort = 6;

    protected static string|\UnitEnum|null $navigationGroup = 'Transparency';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Period Information')
                    ->description('Define the time period for these statistics')
                    ->schema([
                        TextInput::make('year')
                            ->required()
                            ->numeric()
                            ->minValue(2000)
                            ->maxValue(2100)
                            ->default(now()->year)
                            ->helperText('Tahun statistik'),
                        Select::make('month')
                            ->required()
                            ->options([
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                            ])
                            ->default(now()->month)
                            ->helperText('Bulan statistik'),
                        Select::make('court_type')
                            ->required()
                            ->options([
                                'perdata' => 'Perdata',
                                'pidana' => 'Pidana',
                                'agama' => 'Agama',
                            ])
                            ->default('perdata')
                            ->helperText('Jenis peradilan'),
                    ])->columns(3),

                Section::make('Case Numbers')
                    ->description('Enter case statistics for this period')
                    ->schema([
                        TextInput::make('total_filed')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->helperText('Total perkara yang diajukan'),
                        TextInput::make('total_resolved')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->helperText('Total perkara yang selesai'),
                        TextInput::make('pending_carryover')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->helperText('Sisa perkara yang belum selesai'),
                    ])->columns(3),

                Section::make('Performance Metrics')
                    ->description('Key performance indicators for this period')
                    ->schema([
                        TextInput::make('avg_resolution_days')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->suffix(' hari')
                            ->helperText('Rata-rata lama penyelesaian perkara'),
                        TextInput::make('settlement_rate')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->suffix('%')
                            ->helperText('Tingkat penyelesaian perkara'),
                    ])->columns(2),

                Section::make('External Data Tracking')
                    ->description('Track data imported from external systems')
                    ->schema([
                        TextInput::make('external_data_hash')
                            ->disabled()
                            ->helperText('Hash untuk verifikasi integritas data eksternal')
                            ->columnSpanFull(),
                        Forms\Components\Placeholder::make('last_sync_at')
                            ->label('Last Synced At')
                            ->content(fn ($record): string => $record?->last_sync_at?->format('d M Y H:i:s') ?? '-')
                            ->helperText('Terakhir kali disinkronisasi dengan sistem eksternal'),
                    ])->columns(2)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('year')
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('month')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (CaseStatistics $record): string => $record->getMonthName()),
                TextColumn::make('court_type')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'perdata' => 'success',
                        'pidana' => 'danger',
                        'agama' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'perdata' => 'Perdata',
                        'pidana' => 'Pidana',
                        'agama' => 'Agama',
                        default => $state,
                    }),
                TextColumn::make('total_filed')
                    ->label('Filed')
                    ->sortable()
                    ->numeric()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('total_resolved')
                    ->label('Resolved')
                    ->sortable()
                    ->numeric()
                    ->badge()
                    ->color('success')
                    ->suffix('/'.fn ($record): string => (string) $record->total_filed),
                TextColumn::make('pending_carryover')
                    ->label('Pending')
                    ->sortable()
                    ->numeric()
                    ->badge()
                    ->color('warning'),
                TextColumn::make('avg_resolution_days')
                    ->label('Avg Days')
                    ->sortable()
                    ->numeric()
                    ->toggleable()
                    ->suffix(' hari'),
                TextColumn::make('settlement_rate')
                    ->label('Settlement Rate')
                    ->sortable()
                    ->numeric()
                    ->toggleable()
                    ->suffix('%'),
                TextColumn::make('last_sync_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('Not synced'),
                TextColumn::make('created_at')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('court_type')
                    ->options([
                        'perdata' => 'Perdata',
                        'pidana' => 'Pidana',
                        'agama' => 'Agama',
                    ]),
                Filter::make('year')
                    ->form([
                        TextInput::make('year')
                            ->numeric()
                            ->minValue(2000)
                            ->maxValue(2100)
                            ->placeholder('Filter by year...'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['year'],
                                fn (Builder $query, $year): Builder => $query->where('year', $year),
                            );
                    }),
                Filter::make('month')
                    ->form([
                        Select::make('month')
                            ->placeholder('Filter by month...')
                            ->options([
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['month'],
                                fn (Builder $query, $month): Builder => $query->where('month', $month),
                            );
                    }),
                Filter::make('has_pending')
                    ->query(fn (Builder $query): Builder => $query->where('pending_carryover', '>', 0))
                    ->label('Has Pending Cases'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('calculate_metrics')
                    ->label('Calculate Metrics')
                    ->icon('heroicon-o-calculator')
                    ->color('info')
                    ->requiresConfirmation()
                    ->action(function (CaseStatistics $record) {
                        $pending = $record->pending_carryover + $record->total_filed - $record->total_resolved;
                        $resolutionRate = $record->total_filed > 0
                            ? round(($record->total_resolved / $record->total_filed) * 100, 2)
                            : 0;

                        $record->update([
                            'settlement_rate' => $resolutionRate,
                        ]);
                    })
                    ->successNotificationTitle('Metrics recalculated'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('calculate_metrics')
                        ->label('Calculate Metrics Selected')
                        ->icon('heroicon-o-calculator')
                        ->color('info')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each(function (CaseStatistics $record) {
                                $resolutionRate = $record->total_filed > 0
                                    ? round(($record->total_resolved / $record->total_filed) * 100, 2)
                                    : 0;

                                $record->update([
                                    'settlement_rate' => $resolutionRate,
                                ]);
                            });
                        })
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Metrics recalculated for selected records'),
                ]),
            ])
            ->defaultSort('year', 'desc')
            ->defaultSort('month', 'desc')
            ->defaultSort('court_type');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCaseStatistics::route('/'),
            'create' => Pages\CreateCaseStatistics::route('/create'),
            'edit' => Pages\EditCaseStatistics::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('pending_carryover', '>', 0)->count() > 0
            ? static::getModel()::where('pending_carryover', '>', 0)->count().' pending'
            : null;
    }
}
