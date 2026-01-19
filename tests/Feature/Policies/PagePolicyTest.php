<?php

use App\Enums\UserRole;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);
    $this->admin = User::factory()->create(['role' => UserRole::Admin]);
    $this->author = User::factory()->create(['role' => UserRole::Author]);
    $this->designer = User::factory()->create(['role' => UserRole::Designer]);
    $this->subscriber = User::factory()->create(['role' => UserRole::Subscriber]);

    $this->page = Page::factory()->create(['author_id' => $this->author->id]);
    $this->ownPage = Page::factory()->create(['author_id' => $this->author->id]);
    $this->otherPage = Page::factory()->create(['author_id' => $this->admin->id]);
});

test('super admin can view any pages', function () {
    expect($this->superAdmin->can('viewAny', Page::class))->toBeTrue();
});

test('admin can view any pages', function () {
    expect($this->admin->can('viewAny', Page::class))->toBeTrue();
});

test('author can view any pages', function () {
    expect($this->author->can('viewAny', Page::class))->toBeTrue();
});

test('designer can view any pages', function () {
    expect($this->designer->can('viewAny', Page::class))->toBeTrue();
});

test('subscriber can view any pages', function () {
    expect($this->subscriber->can('viewAny', Page::class))->toBeTrue();
});

test('super admin can view individual pages', function () {
    expect($this->superAdmin->can('view', $this->page))->toBeTrue();
});

test('admin can view individual pages', function () {
    expect($this->admin->can('view', $this->page))->toBeTrue();
});

test('author can view individual pages', function () {
    expect($this->author->can('view', $this->page))->toBeTrue();
});

test('designer can view individual pages', function () {
    expect($this->designer->can('view', $this->page))->toBeTrue();
});

test('subscriber can view individual pages', function () {
    expect($this->subscriber->can('view', $this->page))->toBeTrue();
});

test('super admin can create pages', function () {
    expect($this->superAdmin->can('create', Page::class))->toBeTrue();
});

test('admin can create pages', function () {
    expect($this->admin->can('create', Page::class))->toBeTrue();
});

test('author can create pages', function () {
    expect($this->author->can('create', Page::class))->toBeTrue();
});

test('designer can create pages', function () {
    expect($this->designer->can('create', Page::class))->toBeTrue();
});

test('subscriber cannot create pages', function () {
    expect($this->subscriber->can('create', Page::class))->toBeFalse();
});

test('super admin can update any pages', function () {
    expect($this->superAdmin->can('update', $this->page))->toBeTrue();
});

test('admin can update any pages', function () {
    expect($this->admin->can('update', $this->page))->toBeTrue();
});

test('author can update their own pages', function () {
    expect($this->author->can('update', $this->ownPage))->toBeTrue();
});

test('author cannot update other users pages', function () {
    expect($this->author->can('update', $this->otherPage))->toBeFalse();
});

test('designer can update their own pages', function () {
    $designerPage = Page::factory()->create(['author_id' => $this->designer->id]);
    expect($this->designer->can('update', $designerPage))->toBeTrue();
});

test('designer cannot update other users pages', function () {
    expect($this->designer->can('update', $this->page))->toBeFalse();
});

test('subscriber cannot update pages', function () {
    expect($this->subscriber->can('update', $this->page))->toBeFalse();
});

test('super admin can delete pages', function () {
    expect($this->superAdmin->can('delete', $this->page))->toBeTrue();
});

test('admin can delete pages', function () {
    expect($this->admin->can('delete', $this->page))->toBeTrue();
});

test('author cannot delete pages', function () {
    expect($this->author->can('delete', $this->ownPage))->toBeFalse();
});

test('designer cannot delete pages', function () {
    $designerPage = Page::factory()->create(['author_id' => $this->designer->id]);
    expect($this->designer->can('delete', $designerPage))->toBeFalse();
});

test('subscriber cannot delete pages', function () {
    expect($this->subscriber->can('delete', $this->page))->toBeFalse();
});

test('super admin can restore pages', function () {
    expect($this->superAdmin->can('restore', $this->page))->toBeTrue();
});

test('admin can restore pages', function () {
    expect($this->admin->can('restore', $this->page))->toBeTrue();
});

test('author cannot restore pages', function () {
    expect($this->author->can('restore', $this->ownPage))->toBeFalse();
});

test('super admin can force delete pages', function () {
    expect($this->superAdmin->can('forceDelete', $this->page))->toBeTrue();
});

test('admin cannot force delete pages', function () {
    expect($this->admin->can('forceDelete', $this->page))->toBeFalse();
});

test('author cannot force delete pages', function () {
    expect($this->author->can('forceDelete', $this->ownPage))->toBeFalse();
});

test('subscriber with custom permission can create pages', function () {
    $subscriberWithPermission = User::factory()->create([
        'role' => UserRole::Subscriber,
        'permissions' => ['pages.create'],
    ]);

    expect($subscriberWithPermission->can('create', Page::class))->toBeTrue();
});
