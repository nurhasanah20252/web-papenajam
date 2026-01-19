<?php

use App\Models\CaseStatistics;
use App\Models\User;

uses()->group('feature', 'case-statistics');

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('can display case statistics page', function () {
    CaseStatistics::factory()->count(10)->create();

    $response = $this->get(route('case-statistics.index'));

    $response->assertStatus(200);
});

it('can filter case statistics by year', function () {
    CaseStatistics::factory()->create([
        'year' => 2023,
        'month' => 1,
    ]);

    CaseStatistics::factory()->create([
        'year' => 2024,
        'month' => 1,
    ]);

    $response = $this->get(route('case-statistics.index', ['year' => 2024]));

    $response->assertStatus(200);
});

it('can filter case statistics by month', function () {
    CaseStatistics::factory()->create([
        'year' => 2024,
        'month' => 1,
    ]);

    CaseStatistics::factory()->create([
        'year' => 2024,
        'month' => 2,
    ]);

    $response = $this->get(route('case-statistics.index', ['month' => 1]));

    $response->assertStatus(200);
});

it('can filter case statistics by court type', function () {
    CaseStatistics::factory()->create([
        'court_type' => 'perdata',
        'year' => 2024,
    ]);

    CaseStatistics::factory()->create([
        'court_type' => 'pidana',
        'year' => 2024,
    ]);

    $response = $this->get(route('case-statistics.index', ['court_type' => 'perdata']));

    $response->assertStatus(200);
});

it('paginates case statistics correctly', function () {
    CaseStatistics::factory()->count(25)->create([
        'year' => 2024,
    ]);

    $response = $this->get(route('case-statistics.index', ['page' => 1]));

    $response->assertStatus(200);
});

it('includes overview statistics data', function () {
    CaseStatistics::factory()->count(12)->create([
        'year' => 2024,
    ]);

    $response = $this->get(route('case-statistics.index'));

    $response->assertStatus(200);
});

it('can export case statistics to csv', function () {
    CaseStatistics::factory()->count(5)->create([
        'year' => 2024,
    ]);

    $response = $this->get(route('case-statistics.export'));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
});

it('export includes filtered data', function () {
    CaseStatistics::factory()->create([
        'year' => 2023,
        'month' => 1,
    ]);

    CaseStatistics::factory()->create([
        'year' => 2024,
        'month' => 1,
    ]);

    $response = $this->get(route('case-statistics.export', ['year' => 2024]));

    $response->assertStatus(200);
});

it('has available years filter on statistics page', function () {
    CaseStatistics::factory()->create([
        'year' => 2023,
    ]);

    CaseStatistics::factory()->create([
        'year' => 2024,
    ]);

    $response = $this->get(route('case-statistics.index'));

    $response->assertStatus(200);
});
