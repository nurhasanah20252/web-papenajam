<?php

namespace App\Enums;

enum UrlType: string
{
    case Route = 'route';
    case Page = 'page';
    case Custom = 'custom';
    case External = 'external';

    public function label(): string
    {
        return match ($this) {
            self::Route => 'Route',
            self::Page => 'Page',
            self::Custom => 'Custom',
            self::External => 'External',
        };
    }
}
