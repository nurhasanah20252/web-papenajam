<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Site Settings
        Setting::updateOrCreate(
            ['key' => 'site_name'],
            [
                'group' => 'site',
                'value' => 'Pengadilan Agama Penajam',
                'type' => 'text',
                'is_public' => true,
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'site_description'],
            [
                'group' => 'site',
                'value' => 'Website Resmi Pengadilan Agama Penajam',
                'type' => 'text',
                'is_public' => true,
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'site_email'],
            [
                'group' => 'site',
                'value' => 'info@pa-penajam.go.id',
                'type' => 'text',
                'is_public' => true,
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'site_phone'],
            [
                'group' => 'site',
                'value' => '+62 542 123456',
                'type' => 'text',
                'is_public' => true,
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'site_address'],
            [
                'group' => 'site',
                'value' => 'Jl. Protokol No. 123, Penajam, Kalimantan Timur',
                'type' => 'text',
                'is_public' => true,
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'maintenance_mode'],
            [
                'group' => 'site',
                'value' => '0',
                'type' => 'boolean',
                'is_public' => false,
            ]
        );

        // SEO Settings
        Setting::updateOrCreate(
            ['key' => 'meta_title'],
            [
                'group' => 'seo',
                'value' => 'Pengadilan Agama Penajam',
                'type' => 'text',
                'is_public' => false,
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'meta_description'],
            [
                'group' => 'seo',
                'value' => 'Website Resmi Pengadilan Agama Penajam - Melayani dengan Integritas dan Profesionalisme',
                'type' => 'text',
                'is_public' => false,
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'meta_keywords'],
            [
                'group' => 'seo',
                'value' => 'pengadilan agama, penajam, peradilan agama, mahkamah agung',
                'type' => 'text',
                'is_public' => false,
            ]
        );

        // Social Media Settings
        Setting::updateOrCreate(
            ['key' => 'facebook_url'],
            [
                'group' => 'social',
                'value' => 'https://facebook.com/papenajam',
                'type' => 'text',
                'is_public' => true,
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'twitter_url'],
            [
                'group' => 'social',
                'value' => 'https://twitter.com/papenajam',
                'type' => 'text',
                'is_public' => true,
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'instagram_url'],
            [
                'group' => 'social',
                'value' => 'https://instagram.com/papenajam',
                'type' => 'text',
                'is_public' => true,
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'youtube_url'],
            [
                'group' => 'social',
                'value' => 'https://youtube.com/@papenajam',
                'type' => 'text',
                'is_public' => true,
            ]
        );
    }
}
