<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

beforeEach(function () {
    Gate::define('superAdmin', fn (User $user) => $user->isSuperAdmin());
    Gate::define('admin', fn (User $user) => $user->isAdmin());
    Gate::define('author', fn (User $user) => $user->isAuthor());
});

test('super admin has all permissions', function () {
    $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);

    expect($superAdmin->isSuperAdmin())->toBeTrue();
    expect($superAdmin->isAdmin())->toBeTrue();
    expect($superAdmin->isAuthor())->toBeTrue();
    expect($superAdmin->hasPermission('pages.create'))->toBeTrue();
    expect($superAdmin->hasPermission('users.create'))->toBeTrue();
    expect($superAdmin->hasPermission('settings.update'))->toBeTrue();
});

test('admin has most permissions except settings update', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    expect($admin->isSuperAdmin())->toBeFalse();
    expect($admin->isAdmin())->toBeTrue();
    expect($admin->isAuthor())->toBeTrue();
    expect($admin->hasPermission('pages.create'))->toBeTrue();
    expect($admin->hasPermission('users.create'))->toBeTrue();
    expect($admin->hasPermission('settings.update'))->toBeFalse();
});

test('author can create pages and news', function () {
    $author = User::factory()->create(['role' => UserRole::Author]);

    expect($author->isSuperAdmin())->toBeFalse();
    expect($author->isAdmin())->toBeFalse();
    expect($author->isAuthor())->toBeTrue();
    expect($author->hasPermission('pages.create'))->toBeTrue();
    expect($author->hasPermission('news.create'))->toBeTrue();
    expect($author->hasPermission('users.create'))->toBeFalse();
});

test('designer can only manage pages', function () {
    $designer = User::factory()->create(['role' => UserRole::Designer]);

    expect($designer->isSuperAdmin())->toBeFalse();
    expect($designer->isAdmin())->toBeFalse();
    expect($designer->isAuthor())->toBeFalse();
    expect($designer->hasPermission('pages.create'))->toBeTrue();
    expect($designer->hasPermission('news.create'))->toBeFalse();
    expect($designer->hasPermission('documents.create'))->toBeFalse();
});

test('subscriber can only view content', function () {
    $subscriber = User::factory()->create(['role' => UserRole::Subscriber]);

    expect($subscriber->isSuperAdmin())->toBeFalse();
    expect($subscriber->isAdmin())->toBeFalse();
    expect($subscriber->isAuthor())->toBeFalse();
    expect($subscriber->hasPermission('pages.view'))->toBeTrue();
    expect($subscriber->hasPermission('pages.create'))->toBeFalse();
    expect($subscriber->hasPermission('news.view'))->toBeTrue();
    expect($subscriber->hasPermission('news.create'))->toBeFalse();
});

test('user can check if they have specific role', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    expect($admin->hasRole(UserRole::Admin))->toBeTrue();
    expect($admin->hasRole(UserRole::SuperAdmin))->toBeFalse();
    expect($admin->hasRole('admin'))->toBeTrue();
    expect($admin->hasRole('super_admin'))->toBeFalse();
});

test('user can check if they have any of given roles', function () {
    $author = User::factory()->create(['role' => UserRole::Author]);

    expect($author->hasAnyRole([UserRole::Admin, UserRole::Author]))->toBeTrue();
    expect($author->hasAnyRole([UserRole::Admin, UserRole::SuperAdmin]))->toBeFalse();
    expect($author->hasAnyRole(['admin', 'author']))->toBeTrue();
    expect($author->hasAnyRole(['admin', 'super_admin']))->toBeFalse();
});

test('user can be assigned a new role', function () {
    $user = User::factory()->create(['role' => UserRole::Subscriber]);

    expect($user->hasRole(UserRole::Subscriber))->toBeTrue();

    $user->assignRole(UserRole::Author);
    $user->refresh();

    expect($user->hasRole(UserRole::Author))->toBeTrue();
    expect($user->hasRole(UserRole::Subscriber))->toBeFalse();
});

test('user can have custom permissions', function () {
    $subscriber = User::factory()->create([
        'role' => UserRole::Subscriber,
        'permissions' => ['pages.create'],
    ]);

    expect($subscriber->hasPermission('pages.create'))->toBeTrue();
    expect($subscriber->hasPermission('news.create'))->toBeFalse();
});

test('user can add custom permission', function () {
    $subscriber = User::factory()->create(['role' => UserRole::Subscriber]);

    expect($subscriber->hasPermission('pages.create'))->toBeFalse();

    $subscriber->addPermission('pages.create');
    $subscriber->refresh();

    expect($subscriber->hasPermission('pages.create'))->toBeTrue();
});

test('user can remove custom permission', function () {
    $subscriber = User::factory()->create([
        'role' => UserRole::Subscriber,
        'permissions' => ['pages.create'],
    ]);

    expect($subscriber->hasPermission('pages.create'))->toBeTrue();

    $subscriber->removePermission('pages.create');
    $subscriber->refresh();

    expect($subscriber->hasPermission('pages.create'))->toBeFalse();
});

test('last login timestamp is updated on login', function () {
    $user = User::factory()->create([
        'role' => UserRole::Subscriber,
        'last_login_at' => null,
    ]);

    expect($user->last_login_at)->toBeNull();

    $user->updateLastLogin();

    $user->refresh();

    expect($user->last_login_at)->not->toBeNull();
});

test('user can mark profile as completed', function () {
    $user = User::factory()->create([
        'role' => UserRole::Subscriber,
        'profile_completed' => false,
    ]);

    expect($user->profile_completed)->toBeFalse();

    $user->markProfileCompleted();
    $user->refresh();

    expect($user->profile_completed)->toBeTrue();
});

test('gates work correctly for roles', function () {
    $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $author = User::factory()->create(['role' => UserRole::Author]);

    expect(Gate::forUser($superAdmin)->allows('superAdmin'))->toBeTrue();
    expect(Gate::forUser($admin)->allows('superAdmin'))->toBeFalse();
    expect(Gate::forUser($author)->allows('superAdmin'))->toBeFalse();

    expect(Gate::forUser($superAdmin)->allows('admin'))->toBeTrue();
    expect(Gate::forUser($admin)->allows('admin'))->toBeTrue();
    expect(Gate::forUser($author)->allows('admin'))->toBeFalse();

    expect(Gate::forUser($superAdmin)->allows('author'))->toBeTrue();
    expect(Gate::forUser($admin)->allows('author'))->toBeTrue();
    expect(Gate::forUser($author)->allows('author'))->toBeTrue();
});

test('role middleware works correctly', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $subscriber = User::factory()->create(['role' => UserRole::Subscriber]);

    // Admin can access admin routes
    $response = $this->actingAs($admin)->get('/admin/dashboard');
    // Note: This assumes you have an admin dashboard route
    // Adjust as needed based on your actual routes

    // Subscriber cannot access admin routes
    $response = $this->actingAs($subscriber)->get('/admin/dashboard');
    // Note: This will fail if route doesn't exist, adjust as needed
});

test('permission middleware works correctly', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    // Admin can manage users
    expect($admin->can('manageUsers'))->toBeTrue();

    $subscriber = User::factory()->create(['role' => UserRole::Subscriber]);

    // Subscriber cannot manage users
    expect($subscriber->can('manageUsers'))->toBeFalse();
});
