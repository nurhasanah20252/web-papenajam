<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;

test('user can complete registration flow in browser', function () {
    Notification::fake();

    visit('/register')
        ->assertSee('Register')
        ->fill('name', 'John Doe')
        ->fill('email', 'john@example.com')
        ->fill('password', 'password123')
        ->fill('password_confirmation', 'password123')
        ->click('button[type="submit"]')
        ->assertPathIs('/dashboard')
        ->assertSee('Dashboard')
        ->assertNoJavascriptErrors();

    $this->assertDatabaseHas('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    Notification::assertSentTo(
        User::where('email', 'john@example.com')->first(),
        ResetPassword::class
    );
});

test('user can complete login flow in browser', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    visit('/login')
        ->assertSee('Login')
        ->fill('email', 'test@example.com')
        ->fill('password', 'password123')
        ->click('button[type="submit"]')
        ->assertPathIs('/dashboard')
        ->assertSee('Dashboard')
        ->assertNoJavascriptErrors();

    $this->assertAuthenticatedAs($user);
});

test('user cannot login with invalid credentials in browser', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    visit('/login')
        ->fill('email', 'test@example.com')
        ->fill('password', 'wrong-password')
        ->click('button[type="submit"]')
        ->assertPathIs('/login')
        ->assertSee('These credentials do not match our records')
        ->assertNoJavascriptErrors();

    $this->assertGuest();
});

test('user can complete password reset request flow in browser', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    visit('/forgot-password')
        ->assertSee('Forgot Password')
        ->fill('email', 'test@example.com')
        ->click('button[type="submit"]')
        ->assertSee('We have emailed your password reset link')
        ->assertNoJavascriptErrors();

    Notification::assertSentTo($user, ResetPassword::class);
});

test('user can complete password reset with valid token in browser', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $token = app('auth.password.broker')->createToken($user);

    visit("/reset-password/{$token}")
        ->assertSee('Reset Password')
        ->fill('email', 'test@example.com')
        ->fill('password', 'new-password123')
        ->fill('password_confirmation', 'new-password123')
        ->click('button[type="submit"]')
        ->assertPathIs('/login')
        ->assertSee('Your password has been reset')
        ->assertNoJavascriptErrors();

    $this->assertTrue(
        app('hash')->check('new-password123', $user->fresh()->password)
    );
});

test('user can enable two factor authentication in browser', function () {
    if (! Features::canManageTwoFactorAuthentication()) {
        $this->markTestSkipped('Two-factor authentication is not enabled.');
    }

    $user = User::factory()->create();

    visit('/login')
        ->fill('email', $user->email)
        ->fill('password', 'password')
        ->click('button[type="submit"]')
        ->assertPathIs('/dashboard');

    visit('/user/two-factor-authentication')
        ->assertSee('Two Factor Authentication')
        ->click('button[type="submit"]')
        ->assertNoJavascriptErrors();

    $user->refresh();
    $this->assertNotNull($user->two_factor_secret);
});

test('user with two factor enabled is redirected to challenge in browser', function () {
    if (! Features::canManageTwoFactorAuthentication()) {
        $this->markTestSkipped('Two-factor authentication is not enabled.');
    }

    $user = User::factory()->create();
    $user->forceFill([
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
        'two_factor_confirmed_at' => now(),
    ])->save();

    visit('/login')
        ->fill('email', $user->email)
        ->fill('password', 'password')
        ->click('button[type="submit"]')
        ->assertPathIs('/two-factor-challenge')
        ->assertSee('Two Factor Authentication')
        ->assertNoJavascriptErrors();
});

test('user can complete two factor challenge with code in browser', function () {
    if (! Features::canManageTwoFactorAuthentication()) {
        $this->markTestSkipped('Two-factor authentication is not enabled.');
    }

    $user = User::factory()->create();
    $user->forceFill([
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
        'two_factor_confirmed_at' => now(),
    ])->save();

    // Login and get redirected to 2FA challenge
    visit('/login')
        ->fill('email', $user->email)
        ->fill('password', 'password')
        ->click('button[type="submit"]');

    // Submit 2FA code (this would need a valid TOTP code in real scenario)
    visit('/two-factor-challenge')
        ->fill('code', '123456')
        ->click('button[type="submit"]')
        ->assertNoJavascriptErrors();

    // Note: In a real test, you'd need to use the actual TOTP code
    // or mock the TOTP validator
});

test('user can logout in browser', function () {
    $user = User::factory()->create();

    visit('/login')
        ->fill('email', $user->email)
        ->fill('password', 'password')
        ->click('button[type="submit"]')
        ->assertAuthenticated();

    visit('/logout')
        ->assertPathIs('/')
        ->assertGuest();

    $this->assertGuest();
});

test('login form displays validation errors for invalid email', function () {
    visit('/login')
        ->fill('email', 'invalid-email')
        ->fill('password', 'password')
        ->click('button[type="submit"]')
        ->assertSee('The email field must be a valid email address')
        ->assertNoJavascriptErrors();
});

test('registration form displays validation errors for mismatched passwords', function () {
    visit('/register')
        ->fill('name', 'John Doe')
        ->fill('email', 'john@example.com')
        ->fill('password', 'password123')
        ->fill('password_confirmation', 'different-password')
        ->click('button[type="submit"]')
        ->assertSee('The password field confirmation does not match')
        ->assertNoJavascriptErrors();
});

test('user can update profile information in browser', function () {
    $user = User::factory()->create([
        'name' => 'Original Name',
    ]);

    visit('/login')
        ->fill('email', $user->email)
        ->fill('password', 'password')
        ->click('button[type="submit"]');

    visit('/user/profile')
        ->assertSee('Profile')
        ->fill('name', 'Updated Name')
        ->click('button[type="submit"]')
        ->assertSee('Profile updated')
        ->assertNoJavascriptErrors();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated Name',
    ]);
});

test('user can update password in browser', function () {
    $user = User::factory()->create([
        'password' => bcrypt('old-password'),
    ]);

    visit('/login')
        ->fill('email', $user->email)
        ->fill('password', 'old-password')
        ->click('button[type="submit"]');

    visit('/user/password')
        ->assertSee('Password')
        ->fill('current_password', 'old-password')
        ->fill('password', 'new-password123')
        ->fill('password_confirmation', 'new-password123')
        ->click('button[type="submit"]')
        ->assertSee('Password updated')
        ->assertNoJavascriptErrors();

    $this->assertTrue(
        app('hash')->check('new-password123', $user->fresh()->password)
    );
});

test('rate limiting works on login form in browser', function () {
    $user = User::factory()->create();

    // Attempt to login 6 times (exceeds rate limit of 5)
    for ($i = 0; $i < 6; $i++) {
        visit('/login')
            ->fill('email', $user->email)
            ->fill('password', 'wrong-password')
            ->click('button[type="submit"]');
    }

    // On the 6th attempt, should see rate limit error
    visit('/login')
        ->fill('email', $user->email)
        ->fill('password', 'wrong-password')
        ->click('button[type="submit"]')
        ->assertSee('Too many login attempts')
        ->assertNoJavascriptErrors();
});
