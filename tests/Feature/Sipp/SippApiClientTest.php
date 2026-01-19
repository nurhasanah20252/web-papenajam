<?php

use App\Services\Sipp\Exceptions\SippApiException;
use App\Services\Sipp\Exceptions\SippAuthenticationException;
use App\Services\Sipp\Exceptions\SippRateLimitException;
use App\Services\Sipp\SippApiClient;
use Illuminate\Support\Facades\Http;

test('can get court schedules from sipp api', function () {
    Http::fake([
        'https://sipp.test/api/web/jadwal-sidang*' => Http::response([
            'success' => true,
            'data' => [
                [
                    'id' => 1,
                    'no_perkara' => '123/Pdt.G/2024',
                    'ruangan' => 'Ruang Sidang I',
                    'tgl_sidang' => '2024-01-20',
                    'jam_sidang' => '09:00',
                ],
            ],
        ]),
    ]);

    $client = new SippApiClient('https://sipp.test', 'test-key');
    $schedules = $client->getCourtSchedules();

    expect($schedules)->toBeArray()
        ->and($schedules)->toHaveCount(1)
        ->and($schedules[0]['no_perkara'])->toBe('123/Pdt.G/2024');
});

test('can get cases from sipp api', function () {
    Http::fake([
        'https://sipp.test/api/web/perkara*' => Http::response([
            'success' => true,
            'data' => [
                [
                    'id' => 1,
                    'no_perkara' => '123/Pdt.G/2024',
                    'jenis_perkara' => 'Cerai Gugat',
                ],
            ],
        ]),
    ]);

    $client = new SippApiClient('https://sipp.test', 'test-key');
    $cases = $client->getCases();

    expect($cases)->toBeArray()
        ->and($cases)->toHaveCount(1);
});

test('throws authentication exception on 401', function () {
    Http::fake([
        'https://sipp.test/*' => Http::response(['error' => 'Unauthorized'], 401),
    ]);

    $client = new SippApiClient('https://sipp.test', 'invalid-key');

    expect(fn () => $client->getCourtSchedules())
        ->toThrow(SippAuthenticationException::class);
});

test('throws rate limit exception on 429', function () {
    Http::fake([
        'https://sipp.test/*' => Http::response(['error' => 'Too Many Requests'], 429),
    ]);

    $client = new SippApiClient('https://sipp.test', 'test-key');

    expect(fn () => $client->getCourtSchedules())
        ->toThrow(SippRateLimitException::class);
});

test('retries on rate limit with exponential backoff', function () {
    $attempts = 0;

    Http::fake(function () use (&$attempts) {
        $attempts++;

        if ($attempts < 3) {
            return Http::response(['error' => 'Too Many Requests'], 429);
        }

        return Http::response([
            'success' => true,
            'data' => [],
        ]);
    });

    // Don't call Http::fake() again in beforeEach

    $client = new SippApiClient('https://sipp.test', 'test-key');
    $schedules = $client->getCourtSchedules();

    expect($attempts)->toBe(3)
        ->and($schedules)->toBeArray();
});

test('throws api exception on server error', function () {
    Http::fake([
        'https://sipp.test/*' => Http::response(['error' => 'Internal Server Error'], 500),
    ]);

    $client = new SippApiClient('https://sipp.test', 'test-key');

    expect(fn () => $client->getCourtSchedules())
        ->toThrow(SippApiException::class);
});

test('can ping api', function () {
    Http::fake([
        'https://sipp.test/api/health' => Http::response(['status' => 'ok'], 200),
    ]);

    $client = new SippApiClient('https://sipp.test', 'test-key');

    expect($client->ping())->toBeTrue();
});

test('returns false on ping failure', function () {
    Http::fake([
        'https://sipp.test/*' => Http::response(null, 500),
    ]);

    $client = new SippApiClient('https://sipp.test', 'test-key');

    expect($client->ping())->toBeFalse();
});
