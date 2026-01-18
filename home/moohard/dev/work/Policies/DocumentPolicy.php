<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('documents.viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Document $document): bool
    {
        return $user->hasPermission('documents.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('documents.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Document $document): bool
    {
        // Super admin and admin can update any document
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        // Authors can only update their own documents
        return $user->canEditOwn($document, 'uploaded_by') && $user->hasPermission('documents.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Document $document): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Document $document): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    /**
     * Determine whether the user can force delete the model.
     */
    public function forceDelete(User $user, Document $document): bool
    {
        return $user->isSuperAdmin();
    }
}
