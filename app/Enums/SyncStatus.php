<?php

namespace App\Enums;

enum SyncStatus: string
{
    case PENDING = 'pending';
    case RUNNING = 'running';
    case SYNCED = 'synced';
    case FAILED = 'failed';
    case SKIPPED = 'skipped';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::RUNNING => 'Running',
            self::SYNCED => 'Synced',
            self::FAILED => 'Failed',
            self::SKIPPED => 'Skipped',
        };
    }
}
