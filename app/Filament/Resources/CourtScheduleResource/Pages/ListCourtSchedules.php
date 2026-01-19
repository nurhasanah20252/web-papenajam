<?php

namespace App\Filament\Resources\CourtScheduleResource\Pages;

use App\Filament\Resources\CourtScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCourtSchedules extends ListRecords
{
    protected static string $resource = CourtScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
