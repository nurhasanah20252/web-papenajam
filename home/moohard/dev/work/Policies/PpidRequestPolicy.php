<?php

namespace App\Policies;

use App\Models\PpidRequest;
use App\Models\User;

class PpidRequestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PpidRequest $ppidRequest): bool
    {
        // Admin and super admin can view any request
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        // Users can view their own requests
        return $user->getKey() === $ppidRequest->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Any authenticated user can create a PPID request
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PpidRequest $ppidRequest): bool
    {
        // Admin and super admin can update any request
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PpidRequest $ppidRequest): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PpidRequest $ppidRequest): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    /**
     * Determine whether the user can force delete the model.
     */
    public function forceDelete(User $user, PpidRequest $ppidRequest): bool
    {
        return $user->isSuperAdmin();
    }
}
