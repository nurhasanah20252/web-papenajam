<?php

namespace App\Filament\Resources\JoomlaMigrationResource\Pages;

use App\Filament\Resources\JoomlaMigrationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateJoomlaMigration extends CreateRecord
{
    protected static string $resource = JoomlaMigrationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
