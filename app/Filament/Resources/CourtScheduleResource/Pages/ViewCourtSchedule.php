<?php

namespace App\Filament\Resources\CourtScheduleResource\Pages;

use App\Filament\Resources\CourtScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCourtSchedule extends ViewRecord
{
    protected static string $resource = CourtScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
