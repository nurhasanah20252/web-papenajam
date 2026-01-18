<?php

namespace App\Enums;

enum PageType: string
{
    case Static = 'static';
    case Dynamic = 'dynamic';
    case Template = 'template';

    public function label(): string
    {
        return match ($this) {
            self::Static => 'Static',
            self::Dynamic => 'Dynamic',
            self::Template => 'Template',
        };
    }
}
