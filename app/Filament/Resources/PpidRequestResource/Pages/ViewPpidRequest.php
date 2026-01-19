<?php

namespace App\Filament\Resources\PpidRequestResource\Pages;

use App\Filament\Resources\PpidRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPpidRequest extends ViewRecord
{
    protected static string $resource = PpidRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
