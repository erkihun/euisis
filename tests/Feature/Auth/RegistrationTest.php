<?php

// Public self-service registration is OFF by default in EUISIS. Account
// provisioning happens through the admin Users module. These tests verify
// both the disabled-by-default behaviour and that the feature still works
// when REGISTRATION_ENABLED is flipped on.

test('registration screen redirects to login when registration is disabled', function () {
    config(['security.registration_enabled' => false]);

    $response = $this->get('/register');

    $response->assertRedirect('/login');
});

test('registration screen can be rendered when registration is enabled', function () {
    config(['security.registration_enabled' => true]);

    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register when registration is enabled', function () {
    config(['security.registration_enabled' => true]);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});
