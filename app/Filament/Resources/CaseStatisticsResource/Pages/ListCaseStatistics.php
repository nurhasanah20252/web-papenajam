<?php

namespace App\Filament\Resources\CaseStatisticsResource\Pages;

use App\Filament\Resources\CaseStatisticsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCaseStatistics extends ListRecords
{
    protected static string $resource = CaseStatisticsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
