<?php

return [
    'api' => [
        'base_url' => env('SIPP_API_BASE_URL', ''),
        'api_key' => env('SIPP_API_KEY', ''),
        'bearer_token' => env('SIPP_API_BEARER_TOKEN', ''),
        'timeout' => (int) env('SIPP_API_TIMEOUT', 30),
        'retry_attempts' => (int) env('SIPP_API_RETRY_ATTEMPTS', 3),
        'retry_delay' => (int) env('SIPP_API_RETRY_DELAY', 1000),
        'rate_limit_requests' => (int) env('SIPP_API_RATE_LIMIT_REQUESTS', 100),
        'rate_limit_window' => (int) env('SIPP_API_RATE_LIMIT_WINDOW', 60),
    ],

    'sync' => [
        'batch_size' => (int) env('SIPP_SYNC_BATCH_SIZE', 100),
        'conflict_resolution' => (int) env('SIPP_SYNC_CONFLICT_RESOLUTION', 3),
        'enabled' => (bool) env('SIPP_SYNC_ENABLED', true),
        'schedule' => env('SIPP_SYNC_SCHEDULE', 'every_five_minutes'),

        'notifications' => [
            'enabled' => (bool) env('SIPP_SYNC_NOTIFICATIONS_ENABLED', true),
            'emails' => array_filter(array_map('trim', explode(',', env('SIPP_SYNC_NOTIFICATION_EMAILS', '')))),
        ],
    ],

    'logging' => [
        'enabled' => (bool) env('SIPP_LOGGING_ENABLED', true),
        'channel' => env('SIPP_LOG_CHANNEL', 'stack'),
    ],
];
