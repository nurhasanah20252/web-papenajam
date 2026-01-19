<?php

use App\Models\CourtSchedule;
use App\Models\SippCase;
use App\Models\SippJudge;
use App\Services\Sipp\SippApiClient;
use App\Services\Sipp\SippDataSync;
use Illuminate\Support\Facades\Http;

test('syncs court schedules from sipp', function () {
    Http::fake([
        'https://sipp.test/api/web/jadwal-sidang*' => Http::response([
            'success' => true,
            'data' => [
                [
                    'id' => 1,
                    'no_perkara' => '123/Pdt.G/2024',
                    'judul_perkara' => 'Cerai Gugat',
                    'jenis_perkara' => 'Cerai Gugat',
                    'ruangan' => 'Ruang Sidang I',
                    'tgl_sidang' => '2024-01-20',
                    'jam_sidang' => '09:00',
                    'agenda' => 'Pembacaan Dakwaan',
                    'nama_hakim' => 'Dr. H. Ahmad, S.H., M.H.',
                ],
            ],
        ]),
    ]);

    $client = new SippApiClient('https://sipp.test', 'test-key');
    $sync = new SippDataSync($client);

    $result = $sync->syncCourtSchedules();

    expect($result['success'])->toBeTrue()
        ->and($result['created'])->toBe(1)
        ->and(CourtSchedule::count())->toBe(1);

    $schedule = CourtSchedule::first();
    expect($schedule->external_id)->toBe(1)
        ->and($schedule->case_number)->toBe('123/Pdt.G/2024')
        ->and($schedule->court_room)->toBe('Ruang Sidang I');
});

test('updates existing court schedules', function () {
    CourtSchedule::factory()->create([
        'external_id' => 1,
        'case_number' => '123/Pdt.G/2024',
        'agenda' => 'Old Agenda',
    ]);

    Http::fake([
        'https://sipp.test/api/web/jadwal-sidang*' => Http::response([
            'success' => true,
            'data' => [
                [
                    'id' => 1,
                    'no_perkara' => '123/Pdt.G/2024',
                    'judul_perkara' => 'Cerai Gugat',
                    'jenis_perkara' => 'Cerai Gugat',
                    'ruangan' => 'Ruang Sidang I',
                    'tgl_sidang' => '2024-01-20',
                    'jam_sidang' => '09:00',
                    'agenda' => 'Updated Agenda',
                    'nama_hakim' => 'Dr. H. Ahmad, S.H., M.H.',
                ],
            ],
        ]),
    ]);

    $client = new SippApiClient('https://sipp.test', 'test-key');
    $sync = new SippDataSync($client);

    $result = $sync->syncCourtSchedules();

    expect($result['success'])->toBeTrue()
        ->and($result['updated'])->toBe(1)
        ->and(CourtSchedule::count())->toBe(1);

    $schedule = CourtSchedule::first();
    expect($schedule->agenda)->toBe('Updated Agenda');
});

test('syncs cases from sipp', function () {
    Http::fake([
        'https://sipp.test/api/web/perkara*' => Http::response([
            'success' => true,
            'data' => [
                [
                    'id' => 1,
                    'no_perkara' => '123/Pdt.G/2024',
                    'judul_perkara' => 'Cerai Gugat',
                    'jenis_perkara' => 'Cerai Gugat',
                    'tgl_pendaftaran' => '2024-01-01',
                ],
            ],
        ]),
    ]);

    $client = new SippApiClient('https://sipp.test', 'test-key');
    $sync = new SippDataSync($client);

    $result = $sync->syncCases();

    expect($result['success'])->toBeTrue()
        ->and($result['created'])->toBe(1)
        ->and(SippCase::count())->toBe(1);
});

test('syncs master data from sipp', function () {
    Http::fake([
        'https://sipp.test/api/web/hakim*' => Http::response([
            'success' => true,
            'data' => [
                [
                    'id' => 1,
                    'nama' => 'Dr. H. Ahmad, S.H., M.H.',
                    'aktif' => 'Y',
                ],
            ],
        ]),
        'https://sipp.test/api/web/jenis-perkara*' => Http::response([
            'success' => true,
            'data' => [
                [
                    'id' => 1,
                    'nama' => 'Cerai Gugat',
                    'aktif' => 'Y',
                ],
            ],
        ]),
        'https://sipp.test/api/web/ruang-sidang*' => Http::response([
            'success' => true,
            'data' => [
                [
                    'id' => 1,
                    'nama' => 'Ruang Sidang I',
                    'aktif' => 'Y',
                ],
            ],
        ]),
    ]);

    $client = new SippApiClient('https://sipp.test', 'test-key');
    $sync = new SippDataSync($client);

    $result = $sync->syncMasterData();

    expect($result['success'])->toBeTrue()
        ->and(SippJudge::count())->toBe(1);
});

test('creates sync log on successful sync', function () {
    Http::fake([
        '*/api/web/jadwal-sidang*' => Http::response([
            'success' => true,
            'data' => [
                [
                    'id' => 1,
                    'no_perkara' => '123/Pdt.G/2024',
                    'ruangan' => 'Ruang Sidang I',
                    'tgl_sidang' => '2024-01-20',
                ],
            ],
        ]),
    ]);

    $client = new SippApiClient('https://sipp.test', 'test-key');
    $sync = new SippDataSync($client);

    $sync->syncCourtSchedules();

    expect(\App\Models\SippSyncLog::count())->toBe(1);

    $log = \App\Models\SippSyncLog::first();
    expect($log->sync_type)->toBe('court_schedules')
        ->and($log->sync_mode)->toBe('incremental')
        ->and($log->records_created)->toBe(1)
        ->and($log->error_message)->toBeNull();
});

test('logs error on sync failure', function () {
    Http::fake([
        'https://sipp.test/api/web/jadwal-sidang*' => Http::response(['error' => 'Server Error'], 500),
    ]);

    $client = new SippApiClient('https://sipp.test', 'test-key');
    $sync = new SippDataSync($client);

    expect(fn () => $sync->syncCourtSchedules())->toThrow(\Exception::class);

    $log = \App\Models\SippSyncLog::latest()->first();
    expect($log)->not->toBeNull()
        ->and($log->error_message)->not->toBeNull();
});
