<?php

namespace App\Enums;

enum CategoryType: string
{
    case News = 'news';
    case Document = 'document';
    case Page = 'page';
    case Budget = 'budget';

    public function label(): string
    {
        return match ($this) {
            self::News => 'News',
            self::Document => 'Document',
            self::Page => 'Page',
            self::Budget => 'Budget',
        };
    }
}
