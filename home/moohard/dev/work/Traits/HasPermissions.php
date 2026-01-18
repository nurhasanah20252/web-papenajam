<?php

namespace App\Traits;

use App\Enums\UserRole;
use Illuminate\Support\Collection;

trait HasPermissionsNew
{
    public function getAllPermissions(): Collection
    {
        $rolePermissions = $this->getRolePermissions();
        $customPermissions = $this->getCustomPermissions();
        return $rolePermissions->merge($customPermissions)->unique('key');
    }

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

    protected function getAllResourcePermissions(): Collection
    {
        return collect([
            ['resource' => 'pages', 'action' => 'viewAny'],
            ['resource' => 'pages', 'action' => 'view'],
            ['resource' => 'news', 'action' => 'viewAny'],
            ['resource' => 'news', 'action' => 'view'],
            ['resource' => 'documents', 'action' => 'viewAny'],
            ['resource' => 'documents', 'action' => 'view'],
            ['resource' => 'users', 'action' => 'viewAny'],
            ['resource' => 'users', 'action' => 'view'],
        ]);
    }

    protected function getAdminPermissions(): Collection
    {
        return collect([
            ['resource' => 'pages', 'action' => 'viewAny'],
            ['resource' => 'pages', 'action' => 'view'],
            ['resource' => 'news', 'action' => 'viewAny'],
            ['resource' => 'news', 'action' => 'view'],
            ['resource' => 'users', 'action' => 'viewAny'],
            ['resource' => 'users', 'action' => 'view'],
        ]);
    }

    protected function getAuthorPermissions(): Collection
    {
        return collect([
            ['resource' => 'pages', 'action' => 'viewAny'],
            ['resource' => 'pages', 'action' => 'view'],
            ['resource' => 'news', 'action' => 'viewAny'],
            ['resource' => 'news', 'action' => 'view'],
        ]);
    }

    protected function getDesignerPermissions(): Collection
    {
        return collect([
            ['resource' => 'pages', 'action' => 'viewAny'],
            ['resource' => 'pages', 'action' => 'view'],
        ]);
    }

    protected function getSubscriberPermissions(): Collection
    {
        return collect([
            ['resource' => 'news', 'action' => 'viewAny'],
            ['resource' => 'news', 'action' => 'view'],
        ]);
    }

    public function getCustomPermissions(): Collection
    {
        return $this->permissions ?? collect();
    }

    public function hasPermission(string $permission): bool
    {
        return $this->getAllPermissions()->contains('key', $permission);
    }

    public function owns(mixed $resource, string $foreignKey = 'user_id'): bool
    {
        if (!$resource) {
            return false;
        }
        return $this->getKey() === $resource->{$foreignKey};
    }

    public function canEditOwn(mixed $resource, string $foreignKey = 'user_id'): bool
    {
        return $this->owns($resource, $foreignKey) || $this->hasPermission('users.update');
    }

    public function getPermissionKey(string $resource, string $action): string
    {
        return "{$resource}.{$action}";
    }
}
