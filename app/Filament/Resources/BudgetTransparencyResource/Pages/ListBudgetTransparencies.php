<?php

namespace App\Filament\Resources\BudgetTransparencyResource\Pages;

use App\Filament\Resources\BudgetTransparencyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBudgetTransparencies extends ListRecords
{
    protected static string $resource = BudgetTransparencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
