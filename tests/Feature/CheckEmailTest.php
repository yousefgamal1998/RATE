<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

uses(RefreshDatabase::class);

it('returns unavailable for existing email and available for new email', function () {
    // create a user
    $user = User::factory()->create(['email' => 'existing@example.test']);

    // existing email -> available: false
    $this->getJson(route('api.check-email', ['email' => 'existing@example.test']))
        ->assertStatus(200)
        ->assertJson(['available' => false]);

    // new email -> available: true
    $this->getJson(route('api.check-email', ['email' => 'new@example.test']))
        ->assertStatus(200)
        ->assertJson(['available' => true]);
});
