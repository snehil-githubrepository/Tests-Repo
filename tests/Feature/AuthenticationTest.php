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
    //pass-done
    public function testLoginSuccess()
    {
        try {
            $user = User::factory()->create([
                'password' => Hash::make('password'),
            ]);
    
            $response = $this->postJson('/api/login', [
                'email' => $user->email,
                'password' => 'password',
            ]);
    
            $response->assertStatus(302); // Redirected after successful login
            $this->assertAuthenticated();
    
            $this->assertDatabaseHas('users', [
                'email' => $user->email,
            ]);
        } catch (\Exception $e) {
            throw $e;
        }

        $user->delete();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    //pass-done
    public function testLoginFailureInvalidCredentials()
    {
        try {
            $response = $this->postJson('/api/login', [
                'email' => 'nonexistent@example.com',
                'password' => 'invalidpassword',        
            ]);

            $response->assertStatus(422); // Unauthorized due to invalid credentials
            $this->assertGuest(); //user not authenticated
        } catch (\Exception $e) {
            // If any exception occurs during the test, it will be caught here
            throw $e;
        }

        $this->assertDatabaseCount('users', 0);
    }


    /**
     * Register Tests
     */
    //pass-done

    public function testRegisterSuccess()
    {
        try {
            $response = $this->postJson('/api/register', [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'password',
                'resource_type' => 'customer',
            ]);

            $response->assertStatus(200);
            $this->assertAuthenticated(); // User is authenticated

            $user = User::where([
                'name' => 'John Doe',
            ])->first();

            // Assert that the registered user exists in the database
            $this->assertDatabaseHas('users', [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'resource_type' => 'customer',
            ]);

        } catch (\Exception $e) {
            // If any exception occurs during the test, it will be caught here
            throw $e;
        }
        $user->delete();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }



    //pass-done
    public function testRegisterFailure()
    {
        try {
            // Attempt to register with existing email
            //created already a email id 
            $existingUser = User::factory()->create(['email' => 'existing@example.com']);

            $response = $this->postJson('/api/register', [
                'name' => 'John Doe',
                'email' => 'existing@example.com',
                'password' => 'password',
                'resource_type' => 'customer',
            ]);

            $response->assertStatus(422); // Unprocessable entity due to duplicate email
            $this->assertGuest();         

            $this->assertDatabaseMissing('users', [
                'name' => 'John Doe',
            ]);       


        } catch (\Exception $e) {
            throw $e;
        }
        $existingUser->delete();
        // Assert that the user record is not present in the database
        $this->assertDatabaseMissing('users', ['id' => $existingUser->id]);
    }


    /**
     * show profile tests
     */   

    //pass-done
    public function testShowProfile()
    {
        try {
            $user = User::factory()->create(['resource_type' => 'admin']);

            // Set a session ID cookie for the user 
            $sessionId = $this->faker->uuid;
            $user->session_id = $sessionId;
            $user->save();
            
            $this->actingAs($user);
            //authenticated
        
            $response = $this->getJson("/api/profile/{$user->id}");
        
            $response->assertStatus(200);

            // Assert that the registered user exists in the database
            $this->assertDatabaseHas('users', [
                'id' => $user->id
            ]);

        } catch (\Exception $e) {
            // If any exception occurs during the test, it will be caught here
            throw $e;
        }

        $user->delete();
        // Assert that the user record is not present in the database
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }


    //pass-done
    public function testShowProfileFailure()
    {
        try {
            $user = User::factory()->create();
            //not authenticated

            $response = $this->getJson("/api/profile/{$user->id}");

            $response->assertStatus(403)
                ->assertJson(['error' => 'Unauthorized']);
            $this->assertGuest();            

        } catch (\Exception $e) {
            // If any exception occurs during the test, it will be caught here
            throw $e;
        }

        $user->delete();
        // Assert that the user record is not present in the database
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }


    /**
    * Update Profile Tests
    */
    //pass-done
    public function testUpdateProfileSuccess()
    {
        try {
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
            
            // Assert that the database has been updated with the new name
            $this->assertDatabaseHas('users', [
                'id' => $user->id,
                'name' => 'Updated Name',
            ]);

            
        } catch (\Exception $e) {
            // If any exception occurs during the test, it will be caught here
            throw $e;
        }

        $user->delete();

        $this->assertDatabaseMissing('users', [
            'id' => $user->id
        ]);
    }


    //pass
    public function testUpdateProfileFailure()
    {
        try {
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

            // Assert that the database has not been updated with invalid data
            $this->assertDatabaseHas('users', [
                'id' => $user->id,
                'name' => $user->name, // Check that the name remains unchanged
            ]);

        } catch (\Exception $e) {
            // If any exception occurs during the test, it will be caught here
            throw $e;
        }

        $user->delete();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }


    /**
     * delete Profile Test
     */

    //pass-done
    public function testDeleteProfileSuccess()
    {
        try {
            $user = User::factory()->create();
            $userId = $user->id;
            $this->actingAs($user);
    
            $response = $this->deleteJson("/api/profile/{$userId}");
        
            $response->assertStatus(Response::HTTP_OK);
        
            // Assert that the response contains the success message
            $response->assertJson(['message' => 'Profile deleted successfully']);
        
            // Assert that the user record is deleted from the database
            $this->assertDatabaseMissing('users', ['id' => $user->id]);

        } catch (\Exception $e) {
            // If any exception occurs during the test, it will be caught here
            throw $e;
        }
    }
    

     //pass-done
     public function testDeleteProfileFailure()
     {
         try {
             $user = User::factory()->create();
             $this->actingAs($user);

             $invalidId = 9999;
     
             $response = $this->delete("/api/profile/{$invalidId}");
     
             $response->assertStatus(500);
     
             $response->assertJson(['message' => 'Failed to delete profile']);
     
             $this->assertDatabaseHas('users', ['id' => $user->id]);

             $user->delete();

             $this->assertDatabaseMissing('users', ['id' => $user->id]);

         } catch (\Exception $e) {
             // If any exception occurs during the test, it will be caught here
             throw $e;
         }
     }
     

    /**
     * logout Tests 
     */
    //pass-done
    public function testLogoutWhenNotLoggedIn()
    {
        try {
            $user = User::factory()->create();
            
            // Ensure the user is not logged in by setting session_id to null
            $user->session_id = null;
            $user->save();

            $response = $this->post('/api/logout');
            $response->assertStatus(302); //redirection
            
            $response->assertJson(['error' => 'User is not logged in']);

            // Assert that the user's session_id remains unchanged
            $this->assertDatabaseHas('users', ['id' => $user->id, 'session_id' => null]);

            $user->delete();

            $this->assertDatabaseMissing('users', ['id' => $user->id]);
        } catch (\Exception $e) {
            // If any exception occurs during the test, it will be caught here
            throw $e;
        }
    }

    
    //pass-done
    public function testLogoutWhenLoggedIn()
    {
        try {
            $user = User::factory()->create();
            $this->actingAs($user);

            $sessionId = 'test-session-id';
            $user->session_id = $sessionId;
            $user->save();

            $response = $this->post('/api/logout');

            // Check if the user has a session ID in the response cookies
            $this->assertNull($user->fresh()->session_id);

            // Assert that the user's session ID in the database is removed after logout
            $this->assertDatabaseMissing('users', ['id' => $user->id, 'session_id' => $sessionId]);

            $this->assertFalse(Auth::check());

            //removing the user from database
            $user->delete();

            $this->assertDatabaseMissing('users', ['id' => $user->id]);
        } catch (\Exception $e) {
            // If any exception occurs during the test, it will be caught here
            throw $e;
        }
    }

}
