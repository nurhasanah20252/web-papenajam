<?php

namespace App\Filament\Resources;

use App\Enums\PPIDStatus;
use App\Filament\Resources\PpidRequestResource\Pages;
use App\Models\PpidRequest;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class PpidRequestResource extends Resource
{
    protected static ?string $model = PpidRequest::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'PPID Management';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Request Information')
                    ->schema([
                        Forms\Components\TextInput::make('request_number')
                            ->required()
                            ->maxLength(50)
                            ->disabled()
                            ->default(fn () => PpidRequest::generateRequestNumber()),
                        Forms\Components\TextInput::make('applicant_name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('nik')
                            ->label('NIK')
                            ->maxLength(50)
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(20)
                            ->columnSpan(2),
                        Forms\Components\Textarea::make('address')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(4),

                Forms\Components\Section::make('Request Details')
                    ->schema([
                        Forms\Components\Select::make('request_type')
                            ->options([
                                'informasi_publik' => 'Informasi Publik',
                                'keberatan' => 'Keberatan',
                                'sengketa' => 'Sengketa Informasi',
                                'lainnya' => 'Lainnya',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('subject')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('priority')
                            ->options([
                                'normal' => 'Normal',
                                'high' => 'High',
                            ])
                            ->default('normal')
                            ->required()
                            ->native(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Attachments')
                    ->schema([
                        Forms\Components\FileUpload::make('attachments')
                            ->multiple()
                            ->downloadable()
                            ->directory('ppid-attachments')
                            ->maxFiles(5),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Status & Processing')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'submitted' => 'Submitted',
                                'reviewed' => 'Reviewed',
                                'processed' => 'Processed',
                                'completed' => 'Completed',
                                'rejected' => 'Rejected',
                            ])
                            ->default('submitted')
                            ->required()
                            ->native(false)
                            ->live(),
                        Forms\Components\Select::make('processed_by')
                            ->relationship('processor', 'name')
                            ->searchable()
                            ->preload()
                            ->native(false),
                        Forms\Components\Textarea::make('response')
                            ->rows(4)
                            ->visible(fn (Forms\Get $get): bool => in_array($get('status'), ['completed', 'rejected']))
                            ->required(fn (Forms\Get $get): bool => in_array($get('status'), ['completed', 'rejected'])),
                        Forms\Components\DateTimePicker::make('responded_at')
                            ->native(false)
                            ->visible(fn (Forms\Get $get): bool => in_array($get('status'), ['completed', 'rejected'])),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Internal Notes')
                    ->schema([
                        Forms\Components\KeyValue::make('notes')
                            ->keyLabel('Date')
                            ->valueLabel('Note')
                            ->addActionLabel('Add note'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('request_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('applicant_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('request_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'informasi_publik' => 'primary',
                        'keberatan' => 'warning',
                        'sengketa' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('subject')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'secondary' => 'submitted',
                        'info' => 'reviewed',
                        'warning' => 'processed',
                        'success' => 'completed',
                        'danger' => 'rejected',
                    ]),
                Tables\Columns\BadgeColumn::make('priority')
                    ->colors([
                        'gray' => 'normal',
                        'danger' => 'high',
                    ]),
                Tables\Columns\TextColumn::make('processor.name')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('responded_at')
                    ->dateTime('d M Y H:i')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('days_pending')
                    ->label('Days Pending')
                    ->getStateUsing(fn (PpidRequest $record): int => $record->getDaysPending() ?? 0)
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'submitted' => 'Submitted',
                        'reviewed' => 'Reviewed',
                        'processed' => 'Processed',
                        'completed' => 'Completed',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'normal' => 'Normal',
                        'high' => 'High',
                    ]),
                Tables\Filters\Filter::make('pending')
                    ->query(fn (Builder $query): Builder => $query->pending()),
                Tables\Filters\Filter::make('high_priority')
                    ->query(fn (Builder $query): Builder => $query->highPriority()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('mark_completed')
                    ->label('Mark Completed')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\Textarea::make('response')
                            ->required()
                            ->label('Response')
                            ->rows(6),
                    ])
                    ->action(function (PpidRequest $record, array $data) {
                        $user = auth()->user();
                        $record->markAsResponded($user, $data['response']);
                    })
                    ->visible(fn (PpidRequest $record): bool => $record->isPending()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('mark_reviewed')
                        ->label('Mark Reviewed Selected')
                        ->icon('heroicon-o-eye')
                        ->action(fn (Collection $records) => $records->each->update(['status' => PPIDStatus::Reviewed]))
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Requests marked as reviewed')
                        ->color('info'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No PPID requests found')
            ->emptyStateDescription('Create your first PPID request to get started.');
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
            'index' => Pages\ListPpidRequests::route('/'),
            'create' => Pages\CreatePpidRequest::route('/create'),
            'view' => Pages\ViewPpidRequest::route('/{record}'),
            'edit' => Pages\EditPpidRequest::route('/{record}/edit'),
        ];
    }
}
