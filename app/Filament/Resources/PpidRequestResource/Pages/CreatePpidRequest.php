<?php

namespace App\Filament\Resources\PpidRequestResource\Pages;

use App\Filament\Resources\PpidRequestResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePpidRequest extends CreateRecord
{
    protected static string $resource = PpidRequestResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
