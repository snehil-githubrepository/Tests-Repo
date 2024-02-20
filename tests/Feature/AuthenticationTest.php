<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function user_can_login_with_valid_credentials()
    {
        // Create a user
        $user = User::factory()->create();

        // Attempt to log in
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Assert successful login
        $response->assertRedirect('/home');
        $this->assertAuthenticatedAs($user);
    }

    public function user_cannot_login_with_invalid_credentials()
    {
        // Create a user
        $user = User::factory()->create(['password' => Hash::make('password')]);

        // Attempt to log in with invalid password
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'invalidpassword',
        ]);

        // Assert login fails and user remains unauthenticated
        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function user_can_logout()
    {
        // Create a logged-in user
        $user = User::factory()->create();
        $this->actingAs($user);

        // Attempt to log out
        $response = $this->post('/logout');

        // Assert successful logout
        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function unauthenticated_user_redirected_to_login()
    {
        // Attempt to access authenticated route without logging in
        $response = $this->get('/home');

        // Assert redirected to login
        $response->assertRedirect('/login');
    }

    public function user_can_register()
    {
        // Generate user data for registration
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        // Attempt to register user
        $response = $this->post('/register', $userData);

        // Assert successful registration
        $response->assertRedirect('/home');
        $this->assertAuthenticated();
    }

    public function user_cannot_register_with_existing_email()
    {
        // Create a user
        $existingUser = User::factory()->create();

        // Attempt to register with existing user's email
        $userData = [
            'name' => $this->faker->name,
            'email' => $existingUser->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ];
        $response = $this->post('/register', $userData);

        // Assert registration fails and user remains unauthenticated
        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function remember_me_functionality_works()
    {
        // Create a user
        $user = User::factory()->create(['password' => Hash::make('password')]);

        // Attempt to log in with remember me
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'remember' => 'on',
        ]);

        // Assert successful login and session cookie exists
        $response->assertRedirect('/home');
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull(session()->getId());
    }
}
