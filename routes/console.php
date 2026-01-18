<?php

use App\Console\Commands\SippSyncCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| SIPP Scheduled Commands
|--------------------------------------------------------------------------
*/
if (config('sipp.sync.enabled', true)) {
    $schedule = app(\Illuminate\Console\Scheduling\Schedule::class);

    $schedule->command(SippSyncCommand::class, ['incremental'])
        ->cron(config('sipp.sync.schedule', '*/5 * * * *'))
        ->withoutOverlapping()
        ->runInBackground()
        ->onOneServer()
        ->appendOutputTo(storage_path('logs/sipp-sync.log'))
        ->onSuccess(function () {
            \Illuminate\Support\Facades\Log::info('SIPP incremental sync completed successfully');
        })
        ->onFailure(function () {
            \Illuminate\Support\Facades\Log::error('SIPP incremental sync failed');
        });
}
