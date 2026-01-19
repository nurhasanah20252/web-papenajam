<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BudgetTransparencyResource\Pages;
use App\Models\BudgetTransparency;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BudgetTransparencyResource extends Resource
{
    protected static ?string $model = BudgetTransparency::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?int $navigationSort = 5;

    protected static string | \UnitEnum | null $navigationGroup = 'Transparency';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Budget Information')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Example: APBN 2025 atau APBD Kabupaten Penajam'),
                        TextInput::make('year')
                            ->required()
                            ->numeric()
                            ->minValue(2000)
                            ->maxValue(2100)
                            ->default(now()->year)
                            ->helperText('Tahun anggaran'),
                        Select::make('category')
                            ->required()
                            ->options([
                                'apbn' => 'APBN (Anggaran Pendapatan dan Belanja Negara)',
                                'apbd' => 'APBD (Anggaran Pendapatan dan Belanja Daerah)',
                                'other' => 'Lainnya',
                            ])
                            ->default('apbn')
                            ->helperText('Kategori sumber dana'),
                    ])->columns(3),

                Section::make('Amount & Description')
                    ->schema([
                        TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->helperText('Masukkan nilai anggaran dalam Rupiah'),
                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->rows(3)
                            ->helperText('Deskripsi rinci penggunaan anggaran')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Document Attachment')
                    ->schema([
                        FileUpload::make('document_path')
                            ->label('Budget Document')
                            ->acceptedFileTypes(['application/pdf'])
                            ->directory('budget-transparency')
                            ->maxSize(10240)
                            ->helperText('Upload dokumen PDF (Max 10MB)')
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state) {
                                    $file = collect($state)->first();
                                    if (is_string($file)) {
                                        $set('document_name', basename($file));
                                    }
                                }
                            }),
                        TextInput::make('document_name')
                            ->label('Document Name')
                            ->maxLength(255)
                            ->helperText('Nama dokumen yang akan ditampilkan')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Publication Settings')
                    ->schema([
                        DateTimePicker::make('published_at')
                            ->label('Publication Date')
                            ->seconds(false)
                            ->default(now())
                            ->helperText('Tanggal publikasi (kosongkan untuk draft)'),
                        Select::make('author_id')
                            ->label('Author')
                            ->relationship('author', 'name')
                            ->default(auth()->id())
                            ->searchable()
                            ->preload(),
                    ])->columns(2),
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
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->wrap(),
                TextColumn::make('category')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'apbn' => 'success',
                        'apbd' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'apbn' => 'APBN',
                        'apbd' => 'APBD',
                        'other' => 'Lainnya',
                        default => $state,
                    }),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->sortable()
                    ->formatStateUsing(fn (BudgetTransparency $record): string => $record->getFormattedAmount())
                    ->weight('bold')
                    ->color('success'),
                TextColumn::make('published_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->placeholder('Draft')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
                TextColumn::make('author.name')
                    ->label('Author')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'apbn' => 'APBN',
                        'apbd' => 'APBD',
                        'other' => 'Lainnya',
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
                Filter::make('published')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('published_at'))
                    ->label('Published'),
                Filter::make('draft')
                    ->query(fn (Builder $query): Builder => $query->whereNull('published_at'))
                    ->label('Draft'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('view_document')
                    ->label('View Document')
                    ->icon('heroicon-o-document-text')
                    ->url(fn (BudgetTransparency $record): ?string => $record->getDocumentUrl())
                    ->openUrlInNewTab()
                    ->visible(fn (BudgetTransparency $record): bool => $record->document_path !== null),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('publish')
                        ->label('Publish')
                        ->action(fn ($records) => $records->each->update(['published_at' => now()]))
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Budget entries published')
                        ->color('success'),
                    Tables\Actions\BulkAction::make('unpublish')
                        ->label('Unpublish')
                        ->action(fn ($records) => $records->each->update(['published_at' => null]))
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Budget entries unpublished')
                        ->color('warning'),
                ]),
            ])
            ->defaultSort('year', 'desc')
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBudgetTransparencies::route('/'),
            'create' => Pages\CreateBudgetTransparency::route('/create'),
            'edit' => Pages\EditBudgetTransparency::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereNull('published_at')->count() > 0
            ? static::getModel()::whereNull('published_at')->count().' draft'
            : null;
    }
}
