<?php

use App\Models\CourtSchedule;

use function Pest\Laravel\get;

beforeEach(function () {
    CourtSchedule::factory()->create([
        'case_number' => '123/Pdt.G/2024',
        'case_type' => 'Cerai Gugat',
        'schedule_date' => '2024-01-20',
        'schedule_time' => '09:00',
        'court_room' => 'Ruang Sidang I',
        'judge_name' => 'Dr. H. Ahmad, S.H., M.H.',
        'schedule_status' => 'scheduled',
    ]);
});

test('displays court schedules index page', function () {
    get('/jadwal-sidang')
        ->assertStatus(200)
        ->assertInertia(
            fn ($page) => $page
                ->component('schedules')
                ->has('schedules')
                ->has('judges')
                ->has('courtrooms')
                ->has('caseTypes')
        );
});

test('displays court schedules with filters', function () {
    get('/jadwal-sidang?date=2024-01-20&case_type=Cerai+Gugat')
        ->assertStatus(200)
        ->assertInertia(
            fn ($page) => $page
                ->component('schedules')
                ->has('schedules')
                ->where('filters.date', '2024-01-20')
                ->where('filters.case_type', 'Cerai Gugat')
        );
});

test('displays single schedule detail page', function () {
    $schedule = CourtSchedule::first();

    get("/jadwal-sidang/{$schedule->id}")
        ->assertStatus(200)
        ->assertInertia(
            fn ($page) => $page
                ->component('schedules/[id]')
                ->has('schedule')
                ->where('schedule.id', $schedule->id)
                ->where('schedule.case_number', $schedule->case_number)
        );
});

test('returns 404 for non-existent schedule', function () {
    get('/jadwal-sidang/999')
        ->assertStatus(404);
});

test('filters schedules by case type', function () {
    CourtSchedule::factory()->create([
        'case_number' => '456/Pdt.G/2024',
        'case_type' => 'Cerai Talak',
        'schedule_date' => '2024-01-20',
        'schedule_time' => '10:00',
    ]);

    get('/jadwal-sidang?date=2024-01-20&case_type=Cerai+Gugat')
        ->assertStatus(200)
        ->assertInertia(
            fn ($page) => $page
                ->where('schedules', fn ($schedules) => $schedules->count() === 1)
        );
});

test('filters schedules by judge', function () {
    CourtSchedule::factory()->create([
        'case_number' => '456/Pdt.G/2024',
        'judge_name' => 'Dr. H. Fatimah, S.H., M.H.',
        'schedule_date' => '2024-01-20',
        'schedule_time' => '10:00',
    ]);

    get('/jadwal-sidang?date=2024-01-20&judge=Dr.+H.+Ahmad')
        ->assertStatus(200)
        ->assertInertia(
            fn ($page) => $page
                ->where('schedules', fn ($schedules) => $schedules->count() === 1)
        );
});

test('searches schedules by case number', function () {
    get('/jadwal-sidang?date=2024-01-20&search=123')
        ->assertStatus(200)
        ->assertInertia(
            fn ($page) => $page
                ->where('schedules', fn ($schedules) => $schedules->count() === 1)
        );
});
