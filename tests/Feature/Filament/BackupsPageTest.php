<?php

use App\Enums\UserRole;
use App\Filament\Pages\Backups;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('local');
});

it('can access the backups page', function () {
    $user = User::factory()->create([
        'role' => UserRole::SuperAdmin,
    ]);

    $this->actingAs($user)
        ->get(Backups::getUrl())
        ->assertSuccessful();
});

it('can list backups', function () {
    $user = User::factory()->create([
        'role' => UserRole::SuperAdmin,
    ]);

    $this->actingAs($user);

    Livewire::test(Backups::class)
        ->assertStatus(200)
        ->assertViewHas('backups');
});

it('can create a backup', function () {
    $user = User::factory()->create([
        'role' => UserRole::SuperAdmin,
    ]);

    $this->actingAs($user);

    Livewire::test(Backups::class)
        ->callAction('createBackup')
        ->assertHasNoErrors();
});
