<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Auto-discovery is enabled by default in Laravel 12
        // but we can explicitly map policies here if needed
        User::class => \App\Policies\UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerGates();
    }

    /**
     * Register custom gates for role-based access control.
     */
    protected function registerGates(): void
    {
        // Super admin gate
        Gate::define('superAdmin', fn (User $user) => $user->isSuperAdmin());

        // Admin gate (super admin or admin)
        Gate::define('admin', fn (User $user) => $user->isAdmin());

        // Author gate (author, admin, or super admin)
        Gate::define('author', fn (User $user) => $user->isAuthor());

        // Designer gate
        Gate::define('designer', fn (User $user) => $user->hasRole(UserRole::Designer));

        // Subscriber gate
        Gate::define('subscriber', fn (User $user) => $user->hasRole(UserRole::Subscriber));

        // Manage users gate
        Gate::define('manageUsers', fn (User $user) => $user->canManageUsers());

        // Manage settings gate
        Gate::define('manageSettings', fn (User $user) => $user->canManageSettings());

        // Read settings gate
        Gate::define('readSettings', fn (User $user) => $user->canReadSettings());

        // Policy method gates
        Gate::define('manageRoles', fn (User $user) => $user->isSuperAdmin());
        Gate::define('changeRole', fn (User $user, ?User $target = null) => $target ? $user->isSuperAdmin() && $user->id !== $target->id : $user->isSuperAdmin()
        );
    }
}
