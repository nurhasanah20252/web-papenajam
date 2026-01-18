<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Filament Path
    |--------------------------------------------------------------------------
    |
    | The path where Filament will be accessible from.
    */
    'path' => env('FILAMENT_PATH', 'admin'),

    /*
    |--------------------------------------------------------------------------
    | Auth Guard
    |--------------------------------------------------------------------------
    |
    | The authentication guard to use for Filament.
    */
    'auth_guard' => env('FILAMENT_AUTH_GUARD', 'web'),

    /*
    |--------------------------------------------------------------------------
    | Auth Password Timeout
    |--------------------------------------------------------------------------
    |
    | The time in minutes before the authentication session expires.
    */
    'auth_password_timeout' => (int) env('FILAMENT_AUTH_PASSWORD_TIMEOUT', 10800),

    /*
    |--------------------------------------------------------------------------
    | Brand Name
    |--------------------------------------------------------------------------
    */
    'brand' => env('FILAMENT_BRAND', 'Pengadilan Agama Penajam'),

    /*
    |--------------------------------------------------------------------------
    | Brand Logo
    |--------------------------------------------------------------------------
    */
    'brand_logo' => env('FILAMENT_BRAND_LOGO', null),

    /*
    |--------------------------------------------------------------------------
    | Registration
    |--------------------------------------------------------------------------
    |
    | Whether new users can register for the Filament panel.
    */
    'registration' => (bool) env('FILAMENT_REGISTRATION', false),
];
