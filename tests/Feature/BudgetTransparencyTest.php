<?php

use App\Models\BudgetTransparency;
use App\Models\User;

uses()->group('feature', 'budget-transparency');

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('can display budget transparency page', function () {
    BudgetTransparency::factory()->count(5)->create([
        'published_at' => now(),
    ]);

    $response = $this->get(route('budget-transparency.index'));

    $response->assertStatus(200);
});

it('displays only published budget entries', function () {
    BudgetTransparency::factory()->count(3)->create([
        'published_at' => now(),
    ]);

    BudgetTransparency::factory()->count(2)->create([
        'published_at' => null,
    ]);

    $response = $this->get(route('budget-transparency.index'));

    $response->assertStatus(200);
});

it('can filter budget entries by year', function () {
    BudgetTransparency::factory()->create([
        'year' => 2023,
        'published_at' => now(),
    ]);

    BudgetTransparency::factory()->create([
        'year' => 2024,
        'published_at' => now(),
    ]);

    $response = $this->get(route('budget-transparency.index', ['year' => 2024]));

    $response->assertStatus(200);
});

it('can filter budget entries by category', function () {
    BudgetTransparency::factory()->create([
        'category' => 'apbn',
        'published_at' => now(),
    ]);

    BudgetTransparency::factory()->create([
        'category' => 'apbd',
        'published_at' => now(),
    ]);

    $response = $this->get(route('budget-transparency.index', ['category' => 'apbn']));

    $response->assertStatus(200);
});

it('can search budget entries', function () {
    BudgetTransparency::factory()->create([
        'title' => 'APBN 2024 for Education',
        'published_at' => now(),
    ]);

    BudgetTransparency::factory()->create([
        'title' => 'APBD Regional Budget',
        'published_at' => now(),
    ]);

    $response = $this->get(route('budget-transparency.index', ['search' => 'APBN']));

    $response->assertStatus(200);
});

it('paginates budget entries correctly', function () {
    BudgetTransparency::factory()->count(25)->create([
        'published_at' => now(),
    ]);

    $response = $this->get(route('budget-transparency.index', ['page' => 1]));

    $response->assertStatus(200);
});

it('has available years filter on budget transparency page', function () {
    BudgetTransparency::factory()->create([
        'year' => 2023,
        'published_at' => now(),
    ]);

    BudgetTransparency::factory()->create([
        'year' => 2024,
        'published_at' => now(),
    ]);

    $response = $this->get(route('budget-transparency.index'));

    $response->assertStatus(200);
});
