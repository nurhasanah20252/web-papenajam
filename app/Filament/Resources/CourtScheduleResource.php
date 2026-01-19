<?php

namespace App\Filament\Resources;

use App\Enums\ScheduleStatus;
use App\Enums\SyncStatus;
use App\Filament\Resources\CourtScheduleResource\Pages;
use App\Models\CourtSchedule;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class CourtScheduleResource extends Resource
{
    protected static ?string $model = CourtSchedule::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-calendar';

    protected static ?int $navigationSort = 3;

    protected static string | \UnitEnum | null $navigationGroup = 'SIPP Integration';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Case Information')
                    ->schema([
                        TextInput::make('case_number')
                            ->label('Case Number')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('case_title')
                            ->label('Case Title')
                            ->maxLength(255),
                        Select::make('case_type')
                            ->label('Case Type')
                            ->options([
                                'Cerai Gugat' => 'Cerai Gugat',
                                'Cerai Talak' => 'Cerai Talak',
                                'Isbat Nikah' => 'Isbat Nikah',
                                'Lainnya' => 'Lainnya',
                            ])
                            ->nullable(),
                    ])->columns(3),

                Section::make('Schedule Information')
                    ->schema([
                        DatePicker::make('schedule_date')
                            ->label('Schedule Date')
                            ->required()
                            ->native(false),
                        TimePicker::make('schedule_time')
                            ->label('Schedule Time')
                            ->seconds(false)
                            ->nullable(),
                        Select::make('schedule_status')
                            ->label('Status')
                            ->options(ScheduleStatus::class)
                            ->enum(ScheduleStatus::class)
                            ->required()
                            ->default(ScheduleStatus::Scheduled),
                    ])->columns(3),

                Section::make('Location & Personnel')
                    ->schema([
                        TextInput::make('court_room')
                            ->label('Court Room')
                            ->maxLength(100)
                            ->nullable(),
                        TextInput::make('judge_name')
                            ->label('Judge')
                            ->maxLength(255)
                            ->nullable(),
                    ])->columns(2),

                Section::make('Details')
                    ->schema([
                        Forms\Components\Textarea::make('agenda')
                            ->label('Agenda')
                            ->rows(3)
                            ->nullable(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->nullable(),
                        Forms\Components\KeyValue::make('parties')
                            ->label('Parties')
                            ->keyLabel('Role')
                            ->valueLabel('Name')
                            ->nullable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('schedule_date')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable()
                    ->description(fn (CourtSchedule $record): string => $record->schedule_time ?? '')
                    ->badge()
                    ->color(fn (CourtSchedule $record): string => match ($record->schedule_status) {
                        ScheduleStatus::Scheduled => 'info',
                        ScheduleStatus::Postponed => 'warning',
                        ScheduleStatus::Cancelled => 'danger',
                        ScheduleStatus::Completed => 'success',
                    }),
                TextColumn::make('case_number')
                    ->label('Case Number')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                TextColumn::make('case_title')
                    ->label('Title')
                    ->searchable()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('case_type')
                    ->label('Type')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                TextColumn::make('court_room')
                    ->label('Room')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('judge_name')
                    ->label('Judge')
                    ->searchable()
                    ->sortable()
                    ->limit(20)
                    ->toggleable(),
                TextColumn::make('agenda')
                    ->label('Agenda')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('schedule_status')
                    ->label('Status')
                    ->formatStateUsing(fn (ScheduleStatus $state): string => $state->label())
                    ->badge()
                    ->color(fn (ScheduleStatus $state): string => match ($state) {
                        ScheduleStatus::Scheduled => 'info',
                        ScheduleStatus::Postponed => 'warning',
                        ScheduleStatus::Cancelled => 'danger',
                        ScheduleStatus::Completed => 'success',
                    })
                    ->sortable(),
                TextColumn::make('sync_status')
                    ->label('Sync Status')
                    ->formatStateUsing(fn (SyncStatus $state): string => $state->label())
                    ->badge()
                    ->color(fn (SyncStatus $state): string => match ($state) {
                        SyncStatus::Success => 'success',
                        SyncStatus::Error => 'danger',
                        default => 'warning',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('last_sync_at')
                    ->label('Last Sync')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('schedule_status')
                    ->label('Status')
                    ->options(ScheduleStatus::class),
                SelectFilter::make('case_type')
                    ->label('Case Type')
                    ->options([
                        'Cerai Gugat' => 'Cerai Gugat',
                        'Cerai Talak' => 'Cerai Talak',
                        'Isbat Nikah' => 'Isbat Nikah',
                        'Lainnya' => 'Lainnya',
                    ]),
                Filter::make('today')
                    ->query(fn (Builder $query): Builder => $query->whereDate('schedule_date', today()))
                    ->label('Today'),
                Filter::make('upcoming')
                    ->query(fn (Builder $query): Builder => $query->whereDate('schedule_date', '>=', today()))
                    ->label('Upcoming'),
                Filter::make('this_week')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('schedule_date', [now()->startOfWeek(), now()->endOfWeek()]))
                    ->label('This Week'),
                Filter::make('this_month')
                    ->query(fn (Builder $query): Builder => $query->whereYear('schedule_date', now()->year)->whereMonth('schedule_date', now()->month))
                    ->label('This Month'),
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('date_from')
                            ->label('From')
                            ->native(false),
                        DatePicker::make('date_until')
                            ->label('Until')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('schedule_date', '>=', $date)
                            )
                            ->when(
                                $data['date_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('schedule_date', '<=', $date)
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['date_from'] ?? null) {
                            $indicators['date_from'] = 'Date from: '.Carbon::parse($data['date_from'])->format('d M Y');
                        }

                        if ($data['date_until'] ?? null) {
                            $indicators['date_until'] = 'Date until: '.Carbon::parse($data['date_until'])->format('d M Y');
                        }

                        return $indicators;
                    }),
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
            ->headerActions([
                Tables\Actions\Action::make('sync_from_sipp')
                    ->label('Sync from SIPP')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->action(function () {
                        try {
                            \Illuminate\Support\Facades\Artisan::call('sipp:sync', [
                                'type' => 'court_schedules',
                            ]);

                            \Filament\Notifications\Notification::make()
                                ->title('Sync successful')
                                ->body('Court schedules have been synced from SIPP.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Sync failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Sync Court Schedules from SIPP')
                    ->modalDescription('This will sync court schedules from the SIPP API. Continue?'),
            ])
            ->defaultSort('schedule_date', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourtSchedules::route('/'),
            'create' => Pages\CreateCourtSchedule::route('/create'),
            'view' => Pages\ViewCourtSchedule::route('/{record}'),
            'edit' => Pages\EditCourtSchedule::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereDate('schedule_date', '>=', today())
            ->where('schedule_status', ScheduleStatus::Scheduled)
            ->count() > 0
            ? static::getModel()::whereDate('schedule_date', '>=', today())
                ->where('schedule_status', ScheduleStatus::Scheduled)
                ->count().' upcoming'
            : null;
    }
}
