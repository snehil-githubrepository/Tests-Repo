<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * login tests
     */
    public function testLoginSuccess()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(302); // Redirected after successful login
        $this->assertAuthenticated();
    }

    public function testLoginFailureInvalidCredentials()
    {
        $response = $this->postJson('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'invalidpassword',
        ]);

        $response->assertStatus(401); // Unauthorized due to invalid credentials
        $this->assertGuest();
    }

    /**
     * Register Tests
     */

    public function testRegisterSuccess()
    {
        $response = $this->postJson('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
            'resource_type' => 'customer',
        ]);

        $response->assertStatus(200);
        $this->assertAuthenticated();
    }

    public function testRegisterFailure()
    {
        // Attempt to register with existing email
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->postJson('/register', [
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'password' => 'password',
            'resource_type' => 'customer',
        ]);

        $response->assertStatus(403); // Unprocessable entity due to duplicate email
        $this->assertGuest();
    }

    /**
     * show profile tests
     */
    public function testShowProfile()
    {
        $user = User::factory()->create();

        $response = $this->getJson("/profile/{$user->id}");

        $response->assertStatus(200);
        $response->assertJson(['user' => $user->toArray()]);
    }

    public function testShowProfileFailure()
    {
        $user = User::factory()->create();

        // Send a request without the required session_id cookie
        $response = $this->getJson("/profile/{$user->id}");

        // Assert that the response has a status code of 401
        $response->assertStatus(401)
            ->assertJson(['error' => 'Unauthorized']);
    }

    /**
     * Update Profile Tests
     */

    public function testUpdateProfileSuccess()
    {
        $user = User::factory()->create();

        $response = $this->putJson("/profile/update", [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'password' => 'newpassword',
        ]);

        $response->assertStatus(200);
        $this->assertTrue(Hash::check('newpassword', $user->fresh()->password));
    }

    public function testUpdateProfileFailure()
    {
        $user = User::factory()->create();

        // Attempt to update with invalid input (no email provided)
        $response = $this->putJson("/profile/update", [
            'id' => $user->id,
            'name' => 'Updated Name',
            'password' => 'newpassword',
        ]);

        $response->assertStatus(500); // Unprocessable entity due to missing email
    }

    /**
     * delete Profile Test
     */

     public function testDeleteProfileSuccess()
     {
         $user = User::factory()->create();
 
         $response = $this->delete("/profile/{$user->id}");
 
         $response->assertStatus(200);
 
         $response->assertSee('Deleted');
 
         $this->assertDeleted($user); //deleted from database check
     }

     public function testDeleteProfileFailure()
    {
        $user = User::factory()->create();

        $invalidId = 9999;

        $response = $this->delete("/profile/{$invalidId}");

        $response->assertStatus(500);

        $response->assertJson(['message' => 'Failed to delete profile']);

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    /**
     * logout Tests 
     */

    public function testLogoutWhenNotLoggedIn()
    {
        $response = $this->post('/logout');

        // Check if the user has a session ID in the response cookies
        $sessionIdCookie = $response->cookies->get('session_id');

        $response->assertStatus(302); // Redirected since not logged in
    }

    public function testLogoutWhenLoggedIn()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertStatus(302); // Redirected after successful logout
        $this->assertGuest();
    }
}
