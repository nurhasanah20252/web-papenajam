<?php

namespace App\Filament\Resources\JoomlaMigrationResource\Pages;

use App\Filament\Resources\JoomlaMigrationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJoomlaMigrations extends ListRecords
{
    protected static string $resource = JoomlaMigrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Migration')
                ->url(route('filament.admin.pages.joomla-migration')),
        ];
    }
}
