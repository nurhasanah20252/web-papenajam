<?php

namespace App\Filament\Resources\CaseStatisticsResource\Pages;

use App\Filament\Resources\CaseStatisticsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCaseStatistics extends EditRecord
{
    protected static string $resource = CaseStatisticsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
