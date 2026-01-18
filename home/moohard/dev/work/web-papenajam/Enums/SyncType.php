<?php

namespace App\Enums;

enum SyncType: string
{
    case FULL = 'full';
    case INCREMENTAL = 'incremental';
    case MANUAL = 'manual';
    case SCHEDULED = 'scheduled';

    public function label(): string
    {
        return match ($this) {
            self::FULL => 'Full Sync',
            self::INCREMENTAL => 'Incremental Sync',
            self::MANUAL => 'Manual Sync',
            self::SCHEDULED => 'Scheduled Sync',
        };
    }
}
