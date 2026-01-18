<?php

namespace App\Policies;

use App\Models\BudgetTransparency;
use App\Models\User;

class BudgetTransparencyPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('budgetTransparency.viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, BudgetTransparency $budgetTransparency): bool
    {
        return $user->hasPermission('budgetTransparency.view');
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
    public function update(User $user, BudgetTransparency $budgetTransparency): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BudgetTransparency $budgetTransparency): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, BudgetTransparency $budgetTransparency): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    /**
     * Determine whether the user can force delete the model.
     */
    public function forceDelete(User $user, BudgetTransparency $budgetTransparency): bool
    {
        return $user->isSuperAdmin();
    }
}
