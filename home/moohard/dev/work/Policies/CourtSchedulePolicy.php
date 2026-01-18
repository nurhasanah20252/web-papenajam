<?php

namespace App\Policies;

use App\Models\CourtSchedule;
use App\Models\User;

class CourtSchedulePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('courtSchedules.viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CourtSchedule $courtSchedule): bool
    {
        return $user->hasPermission('courtSchedules.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CourtSchedule $courtSchedule): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CourtSchedule $courtSchedule): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CourtSchedule $courtSchedule): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    /**
     * Determine whether the user can force delete the model.
     */
    public function forceDelete(User $user, CourtSchedule $courtSchedule): bool
    {
        return $user->isSuperAdmin();
    }
}
