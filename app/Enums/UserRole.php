<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case Author = 'author';
    case Designer = 'designer';
    case Subscriber = 'subscriber';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Admin => 'Admin',
            self::Author => 'Author',
            self::Designer => 'Designer',
            self::Subscriber => 'Subscriber',
        };
    }

    public function isSuperAdmin(): bool
    {
        return $this === self::SuperAdmin;
    }

    public function isAdmin(): bool
    {
        return $this === self::Admin;
    }

    public function canManageUsers(): bool
    {
        return in_array($this, [self::SuperAdmin, self::Admin]);
    }

    public function canManageSettings(): bool
    {
        return in_array($this, [self::SuperAdmin, self::Admin]);
    }

    public function canReadSettings(): bool
    {
        return in_array($this, [self::SuperAdmin, self::Admin]);
    }
}
