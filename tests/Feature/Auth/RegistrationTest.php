<?php

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->withoutMiddleware()->post(route('register'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    // Check if user was created
    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
    ]);

    $response->assertRedirect();
});
