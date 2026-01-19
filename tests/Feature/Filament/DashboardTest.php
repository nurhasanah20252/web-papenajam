<?php

use App\Models\User;
use App\Enums\UserRole;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\UserRegistrationChart;
use App\Filament\Widgets\ContentCreationChart;
use App\Filament\Widgets\RecentActivityWidget;
use App\Filament\Widgets\RecentContentWidget;
use Livewire\Livewire;

it('can access admin dashboard', function () {
    $user = User::factory()->create([
        'role' => UserRole::SuperAdmin,
    ]);

    $this->actingAs($user)
        ->get('/admin')
        ->assertSuccessful();
});

it('can see dashboard widgets', function () {
    $user = User::factory()->create([
        'role' => UserRole::SuperAdmin,
    ]);

    $this->actingAs($user);

    Livewire::test(StatsOverviewWidget::class)->assertStatus(200);
    Livewire::test(UserRegistrationChart::class)->assertStatus(200);
    Livewire::test(ContentCreationChart::class)->assertStatus(200);
    Livewire::test(RecentActivityWidget::class)->assertStatus(200);
    Livewire::test(RecentContentWidget::class)->assertStatus(200);
});
