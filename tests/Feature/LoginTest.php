<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_valid_credentials_is_redirected_to_dashboard()
    {
        $password = 'secret123';
        $user = User::factory()->create([
            'email' => 'tester@example.com',
            'password' => Hash::make($password),
        ]);

        $response = $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => $password,
            'remember' => '1',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        // When remember is used, Laravel sets a cookie named 'remember_' . sha1($name) under the session cookie name scheme.
        $cookies = $response->headers->getCookies();
        $hasRemember = false;
        foreach ($cookies as $cookie) {
            if (stripos($cookie->getName(), 'remember_') === 0) {
                $hasRemember = true;
                break;
            }
        }
        $this->assertTrue($hasRemember, 'Remember cookie was not set on login with remember flag');
    }

    public function test_invalid_credentials_return_with_errors()
    {
        $user = User::factory()->create([
            'email' => 'exists@example.com',
            'password' => Hash::make('correct-password'),
        ]);

        $response = $this->from(route('login'))->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }
}
