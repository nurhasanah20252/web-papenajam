<?php

use App\Enums\UserRole;
use App\Models\Document;
use App\Models\User;

test('it can filter documents by tag', function () {
    Document::factory()->create([
        'title' => 'Tagged Document',
        'tags' => ['important', 'legal'],
    ]);
    Document::factory()->create([
        'title' => 'Other Document',
        'tags' => ['news'],
    ]);

    expect(Document::withTag('important')->count())->toBe(1);
    expect(Document::withTag('legal')->count())->toBe(1);
    expect(Document::withTag('important')->first()->title)->toBe('Tagged Document');
    expect(Document::withTag('non-existent')->count())->toBe(0);
});

test('it can filter documents by user role', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $subscriber = User::factory()->create([
        'role' => UserRole::Subscriber,
    ]);

    $designer = User::factory()->create([
        'role' => UserRole::Designer,
    ]);

    // Public document
    Document::factory()->create([
        'title' => 'Public Doc',
        'is_public' => true,
        'allowed_roles' => null,
    ]);

    // Admin only document
    Document::factory()->create([
        'title' => 'Admin Doc',
        'is_public' => false,
        'allowed_roles' => ['admin'],
    ]);

    // Subscriber only document
    Document::factory()->create([
        'title' => 'Subscriber Doc',
        'is_public' => false,
        'allowed_roles' => ['subscriber'],
    ]);

    // Admin should see Public and Admin docs
    $adminDocs = Document::allowedForUser($admin)->get();
    expect($adminDocs->count())->toBe(2);
    expect($adminDocs->pluck('title'))->toContain('Public Doc', 'Admin Doc');

    // Subscriber should see Public and Subscriber docs
    $subscriberDocs = Document::allowedForUser($subscriber)->get();
    expect($subscriberDocs->count())->toBe(2);
    expect($subscriberDocs->pluck('title'))->toContain('Public Doc', 'Subscriber Doc');

    // Designer should only see Public doc
    $designerDocs = Document::allowedForUser($designer)->get();
    expect($designerDocs->count())->toBe(1);
    expect($designerDocs->pluck('title'))->toContain('Public Doc');
});

test('it allows access if allowed_roles is null and document is private (behavioral check)', function () {
    // Current implementation:
    // $query->where('is_public', true)
    //        ->orWhereNull('allowed_roles')
    //        ->orWhereJsonContains('allowed_roles', $user->role);

    // This means if allowed_roles is null, it's visible to everyone even if is_public is false.
    // Let's verify this behavior or adjust if needed.

    Document::factory()->create([
        'title' => 'Private Null Roles Doc',
        'is_public' => false,
        'allowed_roles' => null,
    ]);

    $user = User::factory()->create(['role' => UserRole::Subscriber]);

    expect(Document::allowedForUser($user)->count())->toBe(1);
});
