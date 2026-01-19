<?php

namespace App\Filament\Resources\DocumentResource\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class VersionsRelationManager extends RelationManager
{
    protected static string $relationship = 'versions';

    protected static ?string $title = 'Document Versions';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('version')
                    ->required()
                    ->maxLength(50)
                    ->default('1.0')
                    ->helperText('Version number (e.g., 1.0, 1.1, 2.0)'),
                Forms\Components\FileUpload::make('file_path')
                    ->label('Document File')
                    ->required()
                    ->maxSize(10240)
                    ->directory('documents/versions')
                    ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                    ->helperText('Supported formats: PDF, DOC, DOCX, XLS, XLSX (Max 10MB)'),
                Forms\Components\TextInput::make('file_name')
                    ->label('Display File Name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('changelog')
                    ->label('Changelog')
                    ->rows(3)
                    ->helperText('Describe the changes in this version'),
                Forms\Components\Toggle::make('is_current')
                    ->label('Mark as Current Version')
                    ->default(false)
                    ->helperText('Marking this as current will unmark other versions'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('version')
            ->columns([
                Tables\Columns\TextColumn::make('version')
                    ->badge()
                    ->sortable()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('file_name')
                    ->label('File Name')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('file_size')
                    ->label('Size')
                    ->formatStateUsing(fn ($state): string => $this->formatBytes($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('changelog')
                    ->label('Changes')
                    ->limit(50)
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_current')
                    ->boolean()
                    ->label('Current')
                    ->sortable()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Created'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Set creator to current user
                        $data['created_by'] = auth()->id();

                        // Get file info
                        if (isset($data['file_path']) && is_string($data['file_path'])) {
                            $filePath = storage_path('app/public/'.$data['file_path']);
                            if (file_exists($filePath)) {
                                $data['file_size'] = filesize($filePath);
                                $data['checksum'] = hash_file('sha256', $filePath);
                            }
                        }

                        return $data;
                    })
                    ->after(function ($record) {
                        // If marked as current, unmark others
                        if ($record->is_current) {
                            $record->markAsCurrent();
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('mark_current')
                    ->label('Mark as Current')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->markAsCurrent();
                    })
                    ->visible(fn ($record): bool => ! $record->is_current),
                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn ($record): string => $record->getFileUrl())
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision).' '.$units[$pow];
    }
}
