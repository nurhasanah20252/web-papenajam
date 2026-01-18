<?php

namespace App\Enums;

enum BudgetCategory: string
{
    case APBN = 'apbn';
    case APBD = 'apbd';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::APBN => 'APBN',
            self::APBD => 'APBD',
            self::Other => 'Other',
        };
    }
}
