<?php

namespace App\Enums;

enum PPIDStatus: string
{
    case Submitted = 'submitted';
    case Reviewed = 'reviewed';
    case Processed = 'processed';
    case Completed = 'completed';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Submitted',
            self::Reviewed => 'Reviewed',
            self::Processed => 'Processed',
            self::Completed => 'Completed',
            self::Rejected => 'Rejected',
        };
    }

    public function isPending(): bool
    {
        return in_array($this, [self::Submitted, self::Reviewed]);
    }
}
