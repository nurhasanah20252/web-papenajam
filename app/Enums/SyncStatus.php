<?php

namespace App\Enums;

enum SyncStatus: string
{
    case Pending = 'pending';
    case Success = 'success';
    case Error = 'error';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Success => 'Success',
            self::Error => 'Error',
        };
    }

    public function isPending(): bool
    {
        return $this === self::Pending;
    }

    public function isSuccess(): bool
    {
        return $this === self::Success;
    }

    public function isError(): bool
    {
        return $this === self::Error;
    }
}
