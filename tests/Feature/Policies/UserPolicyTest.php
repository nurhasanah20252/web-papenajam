<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('super admin can view any users', function () {
    $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);

    expect($superAdmin->can('viewAny', User::class))->toBeTrue();
});

test('admin can view any users', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    expect($admin->can('viewAny', User::class))->toBeTrue();
});

test('author cannot view any users', function () {
    $author = User::factory()->create(['role' => UserRole::Author]);

    expect($author->can('viewAny', User::class))->toBeFalse();
});

test('users can view their own profile', function () {
    $user = User::factory()->create(['role' => UserRole::Subscriber]);

    expect($user->can('view', $user))->toBeTrue();
});

test('admin can view other users', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $user = User::factory()->create(['role' => UserRole::Subscriber]);

    expect($admin->can('view', $user))->toBeTrue();
});

test('subscriber cannot view other users', function () {
    $subscriber = User::factory()->create(['role' => UserRole::Subscriber]);
    $otherUser = User::factory()->create(['role' => UserRole::Subscriber]);

    expect($subscriber->can('view', $otherUser))->toBeFalse();
});

test('super admin can create users', function () {
    $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);

    expect($superAdmin->can('create', User::class))->toBeTrue();
});

test('admin can create users', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    expect($admin->can('create', User::class))->toBeTrue();
});

test('author cannot create users', function () {
    $author = User::factory()->create(['role' => UserRole::Author]);

    expect($author->can('create', User::class))->toBeFalse();
});

test('users can update their own profile', function () {
    $user = User::factory()->create(['role' => UserRole::Subscriber]);

    expect($user->can('update', $user))->toBeTrue();
});

test('super admin can update any user', function () {
    $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);
    $user = User::factory()->create(['role' => UserRole::Subscriber]);

    expect($superAdmin->can('update', $user))->toBeTrue();
});

test('admin can update any user', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $user = User::factory()->create(['role' => UserRole::Subscriber]);

    expect($admin->can('update', $user))->toBeTrue();
});

test('author cannot update other users', function () {
    $author = User::factory()->create(['role' => UserRole::Author]);
    $user = User::factory()->create(['role' => UserRole::Subscriber]);

    expect($author->can('update', $user))->toBeFalse();
});

test('users cannot delete themselves', function () {
    $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);

    expect($superAdmin->can('delete', $superAdmin))->toBeFalse();
});

test('only super admin can delete other users', function () {
    $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $user = User::factory()->create(['role' => UserRole::Subscriber]);

    expect($superAdmin->can('delete', $user))->toBeTrue();
    expect($admin->can('delete', $user))->toBeFalse();
    expect($user->can('delete', $user))->toBeFalse();
});

test('only super admin can restore users', function () {
    $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $user = User::factory()->create(['role' => UserRole::Subscriber]);

    expect($superAdmin->can('restore', $user))->toBeTrue();
    expect($admin->can('restore', $user))->toBeFalse();
    expect($user->can('restore', $user))->toBeFalse();
});

test('only super admin can force delete users', function () {
    $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $user = User::factory()->create(['role' => UserRole::Subscriber]);

    expect($superAdmin->can('forceDelete', $user))->toBeTrue();
    expect($admin->can('forceDelete', $user))->toBeFalse();
    expect($user->can('forceDelete', $user))->toBeFalse();
});

test('only super admin can manage roles', function () {
    $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $user = User::factory()->create(['role' => UserRole::Subscriber]);

    expect($superAdmin->can('manageRoles'))->toBeTrue();
    expect($admin->can('manageRoles'))->toBeFalse();
    expect($user->can('manageRoles'))->toBeFalse();
});

test('users cannot change their own role', function () {
    $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);
    $user = User::factory()->create(['role' => UserRole::Subscriber]);

    expect($superAdmin->can('changeRole', $superAdmin))->toBeFalse();
    expect($user->can('changeRole', $user))->toBeFalse();
});

test('only super admin can change user roles', function () {
    $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $user = User::factory()->create(['role' => UserRole::Subscriber]);

    expect($superAdmin->can('changeRole', $user))->toBeTrue();
    expect($admin->can('changeRole', $user))->toBeFalse();
    expect($user->can('changeRole', $user))->toBeFalse();
});
