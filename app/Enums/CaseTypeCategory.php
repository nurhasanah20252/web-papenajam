<?php

namespace App\Enums;

enum CaseTypeCategory: string
{
    case Perdata = 'perdata';
    case Pidana = 'pidana';
    case Agama = 'agama';

    public function label(): string
    {
        return match ($this) {
            self::Perdata => 'Perdata',
            self::Pidana => 'Pidana',
            self::Agama => 'Agama',
        };
    }
}
