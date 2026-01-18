<?php

namespace App\Filament\Resources;

use App\Enums\UserRole;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationGroup = 'System';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Personal Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->maxLength(255)
                            ->dehydrated(fn($state): bool => filled($state))
                            ->dehydrateStateUsing(fn($state): string => Hash::make($state))
                            ->required(fn(Page $livewire): bool => $livewire instanceof CreateRecord),
                        Select::make('role')
                            ->options(UserRole::class)
                            ->enum(UserRole::class)
                            ->required()
                            ->default(UserRole::Admin),
                        TextInput::make('avatar')
                            ->maxLength(500)
                            ->nullable(),
                    ])->columns(2),

                Section::make('Status')
                    ->schema([
                        Toggle::make('is_active')
                            ->default(true),
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
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('role')
                    ->formatStateUsing(fn(UserRole $state): string => $state->label())
                    ->badge()
                    ->color(fn(UserRole $state): string => match ($state) {
                        UserRole::SuperAdmin => 'danger',
                        UserRole::Admin => 'warning',
                        UserRole::Author => 'info',
                        UserRole::Designer => 'success',
                        UserRole::Subscriber => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('last_login_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Never'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('active')
                    ->query(fn($query): $query->where('is_active', true))
                    ->label('Active Users'),
                Filter::make('inactive')
                    ->query(fn($query): $query->where('is_active', false))
                    ->label('Inactive Users'),
                Filter::make('role')
                    ->form([
                        Select::make('role')
                            ->options(UserRole::class)
                            ->enum(UserRole::class),
                    ])
                    ->query(fn($query, $data): $query => filled($data['role']) ? $query->where('role', $data['role']) : $query),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label('Activate')
                        ->action(fn(Collection $records): void => $records->each(fn(User $user): bool => $user->update(['is_active' => true])))
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Users activated'),
                    BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->action(fn(Collection $records): void => $records->each(fn(User $user): bool => $user->update(['is_active' => false])))
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Users deactivated')
                        ->color('warning'),
                ]),
            ])
            ->checkable()
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count()->toString();
    }
}
