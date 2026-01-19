<?php

use App\Enums\UserRole;
use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);
    $this->admin = User::factory()->create(['role' => UserRole::Admin]);
    $this->author = User::factory()->create(['role' => UserRole::Author]);
    $this->designer = User::factory()->create(['role' => UserRole::Designer]);
    $this->subscriber = User::factory()->create(['role' => UserRole::Subscriber]);

    $this->news = News::factory()->create(['author_id' => $this->author->id]);
    $this->ownNews = News::factory()->create(['author_id' => $this->author->id]);
    $this->otherNews = News::factory()->create(['author_id' => $this->admin->id]);
});

test('super admin can view any news', function () {
    expect($this->superAdmin->can('viewAny', News::class))->toBeTrue();
});

test('admin can view any news', function () {
    expect($this->admin->can('viewAny', News::class))->toBeTrue();
});

test('author can view any news', function () {
    expect($this->author->can('viewAny', News::class))->toBeTrue();
});

test('designer cannot view news', function () {
    expect($this->designer->can('viewAny', News::class))->toBeFalse();
});

test('subscriber can view any news', function () {
    expect($this->subscriber->can('viewAny', News::class))->toBeTrue();
});

test('super admin can view individual news', function () {
    expect($this->superAdmin->can('view', $this->news))->toBeTrue();
});

test('admin can view individual news', function () {
    expect($this->admin->can('view', $this->news))->toBeTrue();
});

test('author can view individual news', function () {
    expect($this->author->can('view', $this->news))->toBeTrue();
});

test('designer cannot view individual news', function () {
    expect($this->designer->can('view', $this->news))->toBeFalse();
});

test('subscriber can view individual news', function () {
    expect($this->subscriber->can('view', $this->news))->toBeTrue();
});

test('super admin can create news', function () {
    expect($this->superAdmin->can('create', News::class))->toBeTrue();
});

test('admin can create news', function () {
    expect($this->admin->can('create', News::class))->toBeTrue();
});

test('author can create news', function () {
    expect($this->author->can('create', News::class))->toBeTrue();
});

test('designer cannot create news', function () {
    expect($this->designer->can('create', News::class))->toBeFalse();
});

test('subscriber cannot create news', function () {
    expect($this->subscriber->can('create', News::class))->toBeFalse();
});

test('super admin can update any news', function () {
    expect($this->superAdmin->can('update', $this->news))->toBeTrue();
});

test('admin can update any news', function () {
    expect($this->admin->can('update', $this->news))->toBeTrue();
});

test('author can update their own news', function () {
    expect($this->author->can('update', $this->ownNews))->toBeTrue();
});

test('author cannot update other users news', function () {
    expect($this->author->can('update', $this->otherNews))->toBeFalse();
});

test('designer cannot update news', function () {
    expect($this->designer->can('update', $this->news))->toBeFalse();
});

test('subscriber cannot update news', function () {
    expect($this->subscriber->can('update', $this->news))->toBeFalse();
});

test('super admin can delete news', function () {
    expect($this->superAdmin->can('delete', $this->news))->toBeTrue();
});

test('admin can delete news', function () {
    expect($this->admin->can('delete', $this->news))->toBeTrue();
});

test('author cannot delete news', function () {
    expect($this->author->can('delete', $this->ownNews))->toBeFalse();
});

test('designer cannot delete news', function () {
    expect($this->designer->can('delete', $this->news))->toBeFalse();
});

test('subscriber cannot delete news', function () {
    expect($this->subscriber->can('delete', $this->news))->toBeFalse();
});

test('super admin can restore news', function () {
    expect($this->superAdmin->can('restore', $this->news))->toBeTrue();
});

test('admin can restore news', function () {
    expect($this->admin->can('restore', $this->news))->toBeTrue();
});

test('author cannot restore news', function () {
    expect($this->author->can('restore', $this->ownNews))->toBeFalse();
});

test('super admin can force delete news', function () {
    expect($this->superAdmin->can('forceDelete', $this->news))->toBeTrue();
});

test('admin cannot force delete news', function () {
    expect($this->admin->can('forceDelete', $this->news))->toBeFalse();
});

test('author cannot force delete news', function () {
    expect($this->author->can('forceDelete', $this->ownNews))->toBeFalse();
});

test('designer with custom permission can create news', function () {
    $this->designer->addPermission('news.create');

    expect($this->designer->can('create', News::class))->toBeTrue();
});
