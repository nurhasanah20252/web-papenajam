<?php

namespace App\Filament\Resources\CourtScheduleResource\Pages;

use App\Filament\Resources\CourtScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCourtSchedule extends EditRecord
{
    protected static string $resource = CourtScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
