<?php

namespace App\Enums;

enum CaseStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Postponed = 'postponed';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::InProgress => 'In Progress',
            self::Postponed => 'Postponed',
            self::Closed => 'Closed',
        };
    }
}
