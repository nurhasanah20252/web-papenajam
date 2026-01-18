<?php

namespace App\Policies;

use App\Models\CaseStatistic;
use App\Models\User;

class CaseStatisticPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('caseStatistics.viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CaseStatistic $caseStatistic): bool
    {
        return $user->hasPermission('caseStatistics.view');
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
    public function update(User $user, CaseStatistic $caseStatistic): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CaseStatistic $caseStatistic): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CaseStatistic $caseStatistic): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    /**
     * Determine whether the user can force delete the model.
     */
    public function forceDelete(User $user, CaseStatistic $caseStatistic): bool
    {
        return $user->isSuperAdmin();
    }
}
