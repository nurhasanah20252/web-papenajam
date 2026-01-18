<?php

namespace App\Policies;

use App\Models\News;
use App\Models\User;

class NewsPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('news.viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, News $news): bool
    {
        return $user->hasPermission('news.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('news.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, News $news): bool
    {
        // Super admin and admin can update any news
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        // Authors can only update their own news
        return $user->canEditOwn($news, 'author_id') && $user->hasPermission('news.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, News $news): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, News $news): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    /**
     * Determine whether the user can force delete the model.
     */
    public function forceDelete(User $user, News $news): bool
    {
        return $user->isSuperAdmin();
    }
}
