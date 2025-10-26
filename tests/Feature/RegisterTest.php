<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

uses(RefreshDatabase::class);

it('registers a new user and redirects to dashboard', function () {
    $response = $this->post(route('signup.post'), [
        'name' => 'Test User',
        'email' => 'testuser@example.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'terms' => '1',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertDatabaseHas('users', ['email' => 'testuser@example.test']);
    $this->assertAuthenticated();
});

it('returns validation error when email already exists', function () {
    User::factory()->create(['email' => 'exists@example.test']);

    $response = $this->post(route('signup.post'), [
        'name' => 'Another',
        'email' => 'exists@example.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'terms' => '1',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});
