<?php

use App\Models\Setting;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create([
        'role' => 'super_admin',
    ]);
    actingAs($user);
});

test('settings page can be rendered', function () {
    $response = $this->get('/admin/settings');

    $response->assertStatus(200);
});

test('can create setting', function () {
    $response = $this->post('/admin/settings', [
        'key' => 'test_setting',
        'value' => 'test_value',
        'group' => 'general',
        'type' => 'text',
        'is_public' => false,
    ]);

    $response->assertStatus(302);
    assertDatabaseHas('settings', [
        'key' => 'test_setting',
        'value' => 'test_value',
    ]);
});

test('can update setting', function () {
    $setting = Setting::factory()->create([
        'key' => 'test_setting',
        'value' => 'original_value',
    ]);

    $response = $this->put("/admin/settings/{$setting->id}", [
        'key' => 'test_setting',
        'value' => 'updated_value',
        'group' => 'general',
        'type' => 'text',
        'is_public' => false,
    ]);

    $response->assertStatus(302);
    assertDatabaseHas('settings', [
        'id' => $setting->id,
        'value' => 'updated_value',
    ]);
});

test('can delete setting', function () {
    $setting = Setting::factory()->create();

    $response = $this->delete("/admin/settings/{$setting->id}");

    $response->assertStatus(302);
    $this->assertDatabaseMissing('settings', [
        'id' => $setting->id,
    ]);
});

test('can get setting value using helper', function () {
    Setting::factory()->create([
        'key' => 'test_key',
        'value' => 'test_value',
        'type' => 'text',
    ]);

    expect(Setting::get('test_key'))->toBe('test_value');
    expect(Setting::get('non_existent_key', 'default'))->toBe('default');
});

test('can set setting value using helper', function () {
    $setting = Setting::set('new_key', 'new_value');

    expect($setting->key)->toBe('new_key');
    expect($setting->value)->toBe('new_value');
    assertDatabaseHas('settings', [
        'key' => 'new_key',
        'value' => 'new_value',
    ]);
});

test('boolean setting is cast correctly', function () {
    $setting = Setting::factory()->create([
        'key' => 'boolean_test',
        'value' => '1',
        'type' => 'boolean',
    ]);

    expect(Setting::get('boolean_test'))->toBeTrue();
});

test('integer setting is cast correctly', function () {
    $setting = Setting::factory()->create([
        'key' => 'integer_test',
        'value' => '42',
        'type' => 'integer',
    ]);

    expect(Setting::get('integer_test'))->toBe(42);
});

test('json setting is cast correctly', function () {
    $jsonValue = json_encode(['foo' => 'bar']);
    $setting = Setting::factory()->create([
        'key' => 'json_test',
        'value' => $jsonValue,
        'type' => 'json',
    ]);

    $result = Setting::get('json_test');
    expect($result)->toBeArray();
    expect($result['foo'])->toBe('bar');
});

test('settings seeder creates default settings', function () {
    $this->seed(\Database\Seeders\SettingsSeeder::class);

    assertDatabaseHas('settings', ['key' => 'site_name']);
    assertDatabaseHas('settings', ['key' => 'meta_title']);
    assertDatabaseHas('settings', ['key' => 'facebook_url']);
});
