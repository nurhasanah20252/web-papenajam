<?php

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('setting has fillable attributes', function () {
    $setting = new Setting;

    expect($setting->getFillable())->toBe([
        'group',
        'key',
        'value',
        'type',
        'is_public',
    ]);
});

test('setting casts is_public to boolean', function () {
    Setting::factory()->create([
        'key' => 'test_cast',
        'is_public' => 1,
    ]);

    $setting = Setting::where('key', 'test_cast')->first();

    expect($setting->is_public)->toBeBool();
    expect($setting->is_public)->toBeTrue();
});

test('setting scope group filters by group', function () {
    Setting::factory()->create(['group' => 'site', 'key' => 'setting1']);
    Setting::factory()->create(['group' => 'seo', 'key' => 'setting2']);

    $siteSettings = Setting::group('site')->get();

    expect($siteSettings)->toHaveCount(1);
    expect($siteSettings->first()->group)->toBe('site');
});

test('setting scope public filters public settings', function () {
    Setting::factory()->create(['key' => 'public_setting', 'is_public' => true]);
    Setting::factory()->create(['key' => 'private_setting', 'is_public' => false]);

    $publicSettings = Setting::public()->get();

    expect($publicSettings)->toHaveCount(1);
    expect($publicSettings->first()->is_public)->toBeTrue();
});

test('setting get helper returns correct value', function () {
    Setting::factory()->create([
        'key' => 'test_get',
        'value' => 'test_value',
        'type' => 'text',
    ]);

    expect(Setting::get('test_get'))->toBe('test_value');
});

test('setting get helper returns default when not found', function () {
    expect(Setting::get('non_existent', 'default_value'))->toBe('default_value');
});

test('setting set helper creates new setting', function () {
    $setting = Setting::set('new_key', 'new_value', 'general', 'text', false);

    expect($setting->key)->toBe('new_key');
    expect($setting->value)->toBe('new_value');
    expect($setting->group)->toBe('general');
    expect($setting->type)->toBe('text');
    expect($setting->is_public)->toBeFalse();
});

test('setting set helper updates existing setting', function () {
    Setting::factory()->create([
        'key' => 'existing_key',
        'value' => 'old_value',
    ]);

    $setting = Setting::set('existing_key', 'new_value', 'general', 'text', false);

    expect($setting->value)->toBe('new_value');
    expect(Setting::where('key', 'existing_key')->count())->toBe(1);
});

test('setting get helper casts boolean correctly', function () {
    Setting::factory()->create([
        'key' => 'bool_true',
        'value' => '1',
        'type' => 'boolean',
    ]);

    Setting::factory()->create([
        'key' => 'bool_false',
        'value' => '0',
        'type' => 'boolean',
    ]);

    expect(Setting::get('bool_true'))->toBeTrue();
    expect(Setting::get('bool_false'))->toBeFalse();
});

test('setting get helper casts integer correctly', function () {
    Setting::factory()->create([
        'key' => 'int_value',
        'value' => '123',
        'type' => 'integer',
    ]);

    expect(Setting::get('int_value'))->toBe(123);
    expect(Setting::get('int_value'))->toBeInt();
});

test('setting get helper casts json correctly', function () {
    $data = ['foo' => 'bar', 'number' => 42];
    Setting::factory()->create([
        'key' => 'json_value',
        'value' => json_encode($data),
        'type' => 'json',
    ]);

    $result = Setting::get('json_value');

    expect($result)->toBeArray();
    expect($result['foo'])->toBe('bar');
    expect($result['number'])->toBe(42);
});
