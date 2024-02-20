<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;
    
    public function a_user_can_register()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    public function a_user_can_login()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $loginData = [
            'email' => $user->email,
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(200)
            ->assertJsonStructure(['token']);
    }

    public function an_authenticated_user_can_update_own_profile()
    {
        $user = User::factory()->create();

        $updateData = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ];

        $response = $this->actingAs($user)->putJson('/api/user', $updateData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);
    }

    public function an_authenticated_user_can_delete_own_account()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->deleteJson('/api/user');

        $response->assertStatus(204);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function an_admin_can_create_a_product()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $productData = [
            'name' => 'Sample Product',
            'description' => 'This is a sample product',
            'price' => 99.99,
        ];

        $response = $this->actingAs($admin)->postJson('/api/products', $productData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('products', ['name' => 'Sample Product']);
    }

    public function an_admin_can_view_a_product()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $product = Product::factory()->create();

        $response = $this->actingAs($admin)->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
            ]);
    }

    public function an_admin_can_update_a_product()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $product = Product::factory()->create();

        $updateData = [
            'name' => 'Updated Product Name',
            'price' => 129.99,
        ];

        $response = $this->actingAs($admin)->putJson("/api/products/{$product->id}", $updateData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product Name',
            'price' => 129.99,
        ]);
    }

    public function an_admin_can_delete_a_product()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $product = Product::factory()->create();

        $response = $this->actingAs($admin)->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

}
