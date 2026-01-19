<?php

namespace App\Traits;

use App\Enums\UserRole;

trait HasRoles
{
    /**
     * Get the role enum value for this user.
     */
    public function getRole(): UserRole
    {
        return $this->role instanceof UserRole ? $this->role : UserRole::from($this->role);
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string|UserRole $role): bool
    {
        $currentRole = $this->role instanceof UserRole ? $this->role : UserRole::from($this->role);
        $checkRole = $role instanceof UserRole ? $role : UserRole::from($role);

        return $currentRole === $checkRole;
    }

    /**
     * Check if user has any of the given roles.
     */
    public function hasAnyRole(array|string $roles): bool
    {
        $currentRole = $this->role instanceof UserRole ? $this->role : UserRole::from($this->role);
        $roleEnums = array_map(
            fn ($r) => $r instanceof UserRole ? $r : UserRole::from($r),
            is_array($roles) ? $roles : [$roles]
        );

        return in_array($currentRole, $roleEnums, true);
    }

    /**
     * Check if user is super admin.
     */
    public function isSuperAdmin(): bool
    {
        $currentRole = $this->role instanceof UserRole ? $this->role : UserRole::from($this->role);

        return $currentRole === UserRole::SuperAdmin;
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasAnyRole([UserRole::SuperAdmin, UserRole::Admin]);
    }

    /**
     * Check if user is author or higher.
     */
    public function isAuthor(): bool
    {
        return $this->hasAnyRole([UserRole::SuperAdmin, UserRole::Admin, UserRole::Author]);
    }

    /**
     * Assign a role to the user.
     */
    public function assignRole(string|UserRole $role): self
    {
        $roleValue = $role instanceof UserRole ? $role->value : $role;

        $this->role = $roleValue;
        $this->save();

        return $this;
    }

    /**
     * Get available roles.
     *
     * @return array<string, string>
     */
    public static function getAvailableRoles(): array
    {
        return [
            UserRole::SuperAdmin->value => UserRole::SuperAdmin->label(),
            UserRole::Admin->value => UserRole::Admin->label(),
            UserRole::Author->value => UserRole::Author->label(),
            UserRole::Designer->value => UserRole::Designer->label(),
            UserRole::Subscriber->value => UserRole::Subscriber->label(),
        ];
    }
}
