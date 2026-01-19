<?php

namespace App\Filament\Resources\BudgetTransparencyResource\Pages;

use App\Filament\Resources\BudgetTransparencyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBudgetTransparency extends EditRecord
{
    protected static string $resource = BudgetTransparencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
