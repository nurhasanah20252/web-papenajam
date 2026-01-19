<?php

use App\Enums\UserRole;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);
    $this->admin = User::factory()->create(['role' => UserRole::Admin]);
    $this->author = User::factory()->create(['role' => UserRole::Author]);
    $this->designer = User::factory()->create(['role' => UserRole::Designer]);
    $this->subscriber = User::factory()->create(['role' => UserRole::Subscriber]);

    $this->document = Document::factory()->create(['uploaded_by' => $this->author->id]);
    $this->ownDocument = Document::factory()->create(['uploaded_by' => $this->author->id]);
    $this->otherDocument = Document::factory()->create(['uploaded_by' => $this->admin->id]);
});

test('super admin can view any documents', function () {
    expect($this->superAdmin->can('viewAny', Document::class))->toBeTrue();
});

test('admin can view any documents', function () {
    expect($this->admin->can('viewAny', Document::class))->toBeTrue();
});

test('author can view any documents', function () {
    expect($this->author->can('viewAny', Document::class))->toBeTrue();
});

test('designer cannot view documents', function () {
    expect($this->designer->can('viewAny', Document::class))->toBeFalse();
});

test('subscriber can view any documents', function () {
    expect($this->subscriber->can('viewAny', Document::class))->toBeTrue();
});

test('super admin can view individual documents', function () {
    expect($this->superAdmin->can('view', $this->document))->toBeTrue();
});

test('admin can view individual documents', function () {
    expect($this->admin->can('view', $this->document))->toBeTrue();
});

test('author can view individual documents', function () {
    expect($this->author->can('view', $this->document))->toBeTrue();
});

test('designer cannot view individual documents', function () {
    expect($this->designer->can('view', $this->document))->toBeFalse();
});

test('subscriber can view individual documents', function () {
    expect($this->subscriber->can('view', $this->document))->toBeTrue();
});

test('super admin can create documents', function () {
    expect($this->superAdmin->can('create', Document::class))->toBeTrue();
});

test('admin can create documents', function () {
    expect($this->admin->can('create', Document::class))->toBeTrue();
});

test('author can create documents', function () {
    expect($this->author->can('create', Document::class))->toBeTrue();
});

test('designer cannot create documents', function () {
    expect($this->designer->can('create', Document::class))->toBeFalse();
});

test('subscriber cannot create documents', function () {
    expect($this->subscriber->can('create', Document::class))->toBeFalse();
});

test('super admin can update any documents', function () {
    expect($this->superAdmin->can('update', $this->document))->toBeTrue();
});

test('admin can update any documents', function () {
    expect($this->admin->can('update', $this->document))->toBeTrue();
});

test('author cannot update documents', function () {
    // Authors don't have documents.update permission
    expect($this->author->can('update', $this->ownDocument))->toBeFalse();
});

test('author cannot update other users documents', function () {
    expect($this->author->can('update', $this->otherDocument))->toBeFalse();
});

test('designer cannot update documents', function () {
    $designerDocument = Document::factory()->create(['uploaded_by' => $this->designer->id]);
    expect($this->designer->can('update', $designerDocument))->toBeFalse();
});

test('designer cannot update other users documents', function () {
    expect($this->designer->can('update', $this->document))->toBeFalse();
});

test('subscriber cannot update documents', function () {
    expect($this->subscriber->can('update', $this->document))->toBeFalse();
});

test('super admin can delete documents', function () {
    expect($this->superAdmin->can('delete', $this->document))->toBeTrue();
});

test('admin can delete documents', function () {
    expect($this->admin->can('delete', $this->document))->toBeTrue();
});

test('author cannot delete documents', function () {
    expect($this->author->can('delete', $this->ownDocument))->toBeFalse();
});

test('designer cannot delete documents', function () {
    $designerDocument = Document::factory()->create(['uploaded_by' => $this->designer->id]);
    expect($this->designer->can('delete', $designerDocument))->toBeFalse();
});

test('subscriber cannot delete documents', function () {
    expect($this->subscriber->can('delete', $this->document))->toBeFalse();
});

test('super admin can restore documents', function () {
    expect($this->superAdmin->can('restore', $this->document))->toBeTrue();
});

test('admin can restore documents', function () {
    expect($this->admin->can('restore', $this->document))->toBeTrue();
});

test('author cannot restore documents', function () {
    expect($this->author->can('restore', $this->ownDocument))->toBeFalse();
});

test('super admin can force delete documents', function () {
    expect($this->superAdmin->can('forceDelete', $this->document))->toBeTrue();
});

test('admin cannot force delete documents', function () {
    expect($this->admin->can('forceDelete', $this->document))->toBeFalse();
});

test('author cannot force delete documents', function () {
    expect($this->author->can('forceDelete', $this->ownDocument))->toBeFalse();
});

test('subscriber with custom permission can create documents', function () {
    $this->subscriber->addPermission('documents.create');

    expect($this->subscriber->can('create', Document::class))->toBeTrue();
});
