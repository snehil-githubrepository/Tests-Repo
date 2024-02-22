<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

//response assertJsonStructure , assertJson

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * login tests
     */
    //pass
    public function testLoginSuccess()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(302); // Redirected after successful login
        $this->assertAuthenticated();
    }

    //pass
    public function testLoginFailureInvalidCredentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'invalidpassword',        
        ]);

        $response->assertStatus(422); // Unauthorized due to invalid credentials
        $this->assertGuest(); //user not authenticated
    }

    /**
     * Register Tests
     */
    //pass
    public function testRegisterSuccess()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
            'resource_type' => 'customer',
        ]);

        $response->assertStatus(200);
        $this->assertAuthenticated(); //user is authenticated
    }

    //pass
    public function testRegisterFailure()
    {
        // Attempt to register with existing email
        //created already a email id 
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'password' => 'password',
            'resource_type' => 'customer',
        ]);

        $response->assertStatus(422); // Unprocessable entity due to duplicate email
        $this->assertGuest();
    }

    /**
     * show profile tests
     */   

    //pass
    public function testShowProfile()
    {
        $user = User::factory()->create(['resource_type' => 'admin']);

        // Set a session ID cookie for the user 
        $sessionId = $this->faker->uuid;
        $user->session_id = $sessionId;
        $user->save();
        
        $this->actingAs($user);
        //authenticated
    
        $response = $this->getJson("/api/profile/{$user->id}");
    
        $response->assertStatus(200);
    }

    //pass
    public function testShowProfileFailure()
    {
        $user = User::factory()->create();
        //not authenticated

        $response = $this->getJson("/api/profile/{$user->id}");

        $response->assertStatus(401)
            ->assertJson(['error' => 'Unauthorized']);
    }

    /**
    * Update Profile Tests
    */
    //pass
    public function testUpdateProfileSuccess()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->putJson("/api/profile/{$user->id}", [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'password' => 'newpassword',
        ]);

        $response->assertStatus(200);
        $this->assertTrue(Hash::check('newpassword', $user->fresh()->password));
    }

    //pass
    public function testUpdateProfileFailure()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        // Send a request to update profile with invalid input (numeric name)
        $response = $this->putJson("/api/profile/{$user->id}", [
            'name' => 123, // Invalid numeric name
            // 'email' => ' ', // Missed this field
            'password' => 'newpassword',
        ]);

        // Assert that the response status code is 422 Unprocessable Entity
        $response->assertStatus(422);

        // Assert that the response contains validation errors
        $response->assertJsonValidationErrors(['name']);
    }

    /**
     * delete Profile Test
     */

    //pass
     public function testDeleteProfileSuccess()
     {
            $user = User::factory()->create();
            $userId = $user->id;

            $response = $this->deleteJson("/api/profile/{$userId}");
        
            $response->assertStatus(Response::HTTP_OK);
        
            // Assert that the response contains the success message
            $response->assertJson(['message' => 'Profile deleted successfully']);
        
            // Assert that the user record is deleted from the database
            $this->assertDatabaseMissing('users', ['id' => $user->id]);
     }

     //pass
    public function testDeleteProfileFailure()
    {
        $user = User::factory()->create();

        $invalidId = 9999;

        $response = $this->delete("/api/profile/{$invalidId}");

        $response->assertStatus(500);

        $response->assertJson(['message' => 'Failed to delete profile']);

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    /**
     * logout Tests 
     */
    //pass
    public function testLogoutWhenNotLoggedIn()
    {   
        $user = User::factory()->create();
        
        // Ensure the user is not logged in by setting session_id to null
        $user->session_id = null;
        $user->save();

        $response = $this->post('/api/logout');
        $response->assertStatus(302); //redirection
        
        $response->assertJson(['error' => 'User is not logged in']);
    }
    
    //pass
    public function testLogoutWhenLoggedIn()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $sessionId = 'test-session-id';
        $user->session_id = $sessionId;
        $user->save();

        $response = $this->post('/api/logout');

        // Check if the user has a session ID in the response cookies
        $this->assertNull($user->fresh()->session_id);

        $this->assertFalse(Auth::check());
    }
}
