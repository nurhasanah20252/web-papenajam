<?php

namespace App\Traits;

use App\Enums\UserRole;
use Illuminate\Support\Collection;

trait HasPermissions
{
    /**
     * Get all permissions for this user (merged from role and custom permissions).
     */
    public function getAllPermissions(): Collection
    {
        $rolePermissions = $this->getRolePermissions();
        $customPermissions = $this->getCustomPermissions();

        return $rolePermissions->merge($customPermissions)->unique('key');
    }

    /**
     * Get permissions based on role.
     */
    public function getRolePermissions(): Collection
    {
        return match ($this->getRole()) {
            UserRole::SuperAdmin => $this->getAllResourcePermissions(),
            UserRole::Admin => $this->getAdminPermissions(),
            UserRole::Author => $this->getAuthorPermissions(),
            UserRole::Designer => $this->getDesignerPermissions(),
            UserRole::Subscriber => $this->getSubscriberPermissions(),
        };
    }

    /**
     * Get all permissions for all resources (super admin).
     */
    protected function getAllResourcePermissions(): Collection
    {
        return collect([
            // Pages
            ['resource' => 'pages', 'action' => 'viewAny'],
            ['resource' => 'pages', 'action' => 'view'],
            ['resource' => 'pages', 'action' => 'create'],
            ['resource' => 'pages', 'action' => 'update'],
            ['resource' => 'pages', 'action' => 'delete'],
            ['resource' => 'pages', 'action' => 'restore'],
            ['resource' => 'pages', 'action' => 'forceDelete'],
            // News
            ['resource' => 'news', 'action' => 'viewAny'],
            ['resource' => 'news', 'action' => 'view'],
            ['resource' => 'news', 'action' => 'create'],
            ['resource' => 'news', 'action' => 'update'],
            ['resource' => 'news', 'action' => 'delete'],
            ['resource' => 'news', 'action' => 'restore'],
            ['resource' => 'news', 'action' => 'forceDelete'],
            // Documents
            ['resource' => 'documents', 'action' => 'viewAny'],
            ['resource' => 'documents', 'action' => 'view'],
            ['resource' => 'documents', 'action' => 'create'],
            ['resource' => 'documents', 'action' => 'update'],
            ['resource' => 'documents', 'action' => 'delete'],
            ['resource' => 'documents', 'action' => 'restore'],
            ['resource' => 'documents', 'action' => 'forceDelete'],
            // Categories
            ['resource' => 'categories', 'action' => 'viewAny'],
            ['resource' => 'categories', 'action' => 'view'],
            ['resource' => 'categories', 'action' => 'create'],
            ['resource' => 'categories', 'action' => 'update'],
            ['resource' => 'categories', 'action' => 'delete'],
            ['resource' => 'categories', 'action' => 'restore'],
            ['resource' => 'categories', 'action' => 'forceDelete'],
            // Menus
            ['resource' => 'menus', 'action' => 'viewAny'],
            ['resource' => 'menus', 'action' => 'view'],
            ['resource' => 'menus', 'action' => 'create'],
            ['resource' => 'menus', 'action' => 'update'],
            ['resource' => 'menus', 'action' => 'delete'],
            ['resource' => 'menus', 'action' => 'restore'],
            ['resource' => 'menus', 'action' => 'forceDelete'],
            // Menu Items
            ['resource' => 'menuItems', 'action' => 'viewAny'],
            ['resource' => 'menuItems', 'action' => 'view'],
            ['resource' => 'menuItems', 'action' => 'create'],
            ['resource' => 'menuItems', 'action' => 'update'],
            ['resource' => 'menuItems', 'action' => 'delete'],
            ['resource' => 'menuItems', 'action' => 'restore'],
            ['resource' => 'menuItems', 'action' => 'forceDelete'],
            // Court Schedules
            ['resource' => 'courtSchedules', 'action' => 'viewAny'],
            ['resource' => 'courtSchedules', 'action' => 'view'],
            ['resource' => 'courtSchedules', 'action' => 'create'],
            ['resource' => 'courtSchedules', 'action' => 'update'],
            ['resource' => 'courtSchedules', 'action' => 'delete'],
            ['resource' => 'courtSchedules', 'action' => 'restore'],
            ['resource' => 'courtSchedules', 'action' => 'forceDelete'],
            // Budget Transparency
            ['resource' => 'budgetTransparency', 'action' => 'viewAny'],
            ['resource' => 'budgetTransparency', 'action' => 'view'],
            ['resource' => 'budgetTransparency', 'action' => 'create'],
            ['resource' => 'budgetTransparency', 'action' => 'update'],
            ['resource' => 'budgetTransparency', 'action' => 'delete'],
            ['resource' => 'budgetTransparency', 'action' => 'restore'],
            ['resource' => 'budgetTransparency', 'action' => 'forceDelete'],
            // Case Statistics
            ['resource' => 'caseStatistics', 'action' => 'viewAny'],
            ['resource' => 'caseStatistics', 'action' => 'view'],
            ['resource' => 'caseStatistics', 'action' => 'create'],
            ['resource' => 'caseStatistics', 'action' => 'update'],
            ['resource' => 'caseStatistics', 'action' => 'delete'],
            ['resource' => 'caseStatistics', 'action' => 'restore'],
            ['resource' => 'caseStatistics', 'action' => 'forceDelete'],
            // PPID Requests
            ['resource' => 'ppidRequests', 'action' => 'viewAny'],
            ['resource' => 'ppidRequests', 'action' => 'view'],
            ['resource' => 'ppidRequests', 'action' => 'create'],
            ['resource' => 'ppidRequests', 'action' => 'update'],
            ['resource' => 'ppidRequests', 'action' => 'delete'],
            ['resource' => 'ppidRequests', 'action' => 'restore'],
            ['resource' => 'ppidRequests', 'action' => 'forceDelete'],
            // Settings
            ['resource' => 'settings', 'action' => 'viewAny'],
            ['resource' => 'settings', 'action' => 'view'],
            ['resource' => 'settings', 'action' => 'update'],
            // Users
            ['resource' => 'users', 'action' => 'viewAny'],
            ['resource' => 'users', 'action' => 'view'],
            ['resource' => 'users', 'action' => 'create'],
            ['resource' => 'users', 'action' => 'update'],
            ['resource' => 'users', 'action' => 'delete'],
            ['resource' => 'users', 'action' => 'restore'],
            ['resource' => 'users', 'action' => 'forceDelete'],
        ])->map(fn($p) => ['key' => "{$p['resource']}.{$p['action']}", ...$p]);
    }

    /**
     * Get admin permissions (full except settings write).
     */
    protected function getAdminPermissions(): Collection
    {
        return $this->getAllResourcePermissions()->map(function ($permission) {
            // Admin can read settings but not update them
            if ($permission['resource'] === 'settings' && $permission['action'] === 'update') {
                return null;
            }

            return $permission;
        })->filter();
    }

    /**
     * Get author permissions.
     */
    protected function getAuthorPermissions(): Collection
    {
        return collect([
            // Pages - create, edit, own
            ['resource' => 'pages', 'action' => 'viewAny', 'key' => 'pages.viewAny'],
            ['resource' => 'pages', 'action' => 'view', 'key' => 'pages.view'],
            ['resource' => 'pages', 'action' => 'create', 'key' => 'pages.create'],
            ['resource' => 'pages', 'action' => 'update', 'key' => 'pages.update'],
            // News - create, edit, own
            ['resource' => 'news', 'action' => 'viewAny', 'key' => 'news.viewAny'],
            ['resource' => 'news', 'action' => 'view', 'key' => 'news.view'],
            ['resource' => 'news', 'action' => 'create', 'key' => 'news.create'],
            ['resource' => 'news', 'action' => 'update', 'key' => 'news.update'],
            // Documents - create
            ['resource' => 'documents', 'action' => 'viewAny', 'key' => 'documents.viewAny'],
            ['resource' => 'documents', 'action' => 'view', 'key' => 'documents.view'],
            ['resource' => 'documents', 'action' => 'create', 'key' => 'documents.create'],
        ]);
    }

    /**
     * Get designer permissions.
     */
    protected function getDesignerPermissions(): Collection
    {
        return collect([
            // Pages - create, edit, own
            ['resource' => 'pages', 'action' => 'viewAny', 'key' => 'pages.viewAny'],
            ['resource' => 'pages', 'action' => 'view', 'key' => 'pages.view'],
            ['resource' => 'pages', 'action' => 'create', 'key' => 'pages.create'],
            ['resource' => 'pages', 'action' => 'update', 'key' => 'pages.update'],
        ]);
    }

    /**
     * Get subscriber permissions (read only).
     */
    protected function getSubscriberPermissions(): Collection
    {
        return collect([
            // Pages - read
            ['resource' => 'pages', 'action' => 'viewAny', 'key' => 'pages.viewAny'],
            ['resource' => 'pages', 'action' => 'view', 'key' => 'pages.view'],
            // News - read
            ['resource' => 'news', 'action' => 'viewAny', 'key' => 'news.viewAny'],
            ['resource' => 'news', 'action' => 'view', 'key' => 'news.view'],
            // Documents - read
            ['resource' => 'documents', 'action' => 'viewAny', 'key' => 'documents.viewAny'],
            ['resource' => 'documents', 'action' => 'view', 'key' => 'documents.view'],
            // Menus - read
            ['resource' => 'menus', 'action' => 'viewAny', 'key' => 'menus.viewAny'],
            ['resource' => 'menus', 'action' => 'view', 'key' => 'menus.view'],
            // Court Schedules - read
            ['resource' => 'courtSchedules', 'action' => 'viewAny', 'key' => 'courtSchedules.viewAny'],
            ['resource' => 'courtSchedules', 'action' => 'view', 'key' => 'courtSchedules.view'],
        ]);
    }

    /**
     * Get custom permissions from JSON column.
     */
    public function getCustomPermissions(): Collection
    {
        $permissions = $this->permissions;

        if (empty($permissions)) {
            return collect([]);
        }

        return collect($permissions)->map(fn($p) => ['key' => $p, ...json_decode($p, true) ?: []]);
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        // Super admin has all permissions
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->getAllPermissions()->contains('key', $permission);
    }

    /**
     * Check if user can perform an action on a resource.
     */
    public function canAction(string $action, string $resource): bool
    {
        $permission = "{$resource}.{$action}";

        return $this->hasPermission($permission);
    }

    /**
     * Check if user owns a resource.
     */
    public function owns(mixed $resource, string $foreignKey = 'user_id'): bool
    {
        if (!$resource) {
            return false;
        }

        return $this->getKey() === $resource->{$foreignKey};
    }

    /**
     * Check if user can edit a resource they own.
     */
    public function canEditOwn(mixed $resource, string $foreignKey = 'user_id'): bool
    {
        return $this->owns($resource, $foreignKey) && $this->canAction('update', class_basename($resource));
    }

    /**
     * Set custom permissions.
     */
    public function setPermissions(array $permissions): self
    {
        $this->update(['permissions' => $permissions]);

        return $this;
    }

    /**
     * Add a custom permission.
     */
    public function addPermission(string $permission): self
    {
        $permissions = $this->permissions ?? [];

        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->update(['permissions' => $permissions]);
        }

        return $this;
    }

    /**
     * Remove a custom permission.
     */
    public function removePermission(string $permission): self
    {
        $permissions = $this->permissions ?? [];

        $this->update(['permissions' => array_values(array_diff($permissions, [$permission]))]);

        return $this;
    }
}
