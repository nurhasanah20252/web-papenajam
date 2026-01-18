<?php

namespace App\Enums;

enum PPIDPriority: string
{
    case Normal = 'normal';
    case High = 'high';

    public function label(): string
    {
        return match ($this) {
            self::Normal => 'Normal',
            self::High => 'High',
        };
    }

    public function isHigh(): bool
    {
        return $this === self::High;
    }
}
