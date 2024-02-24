<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Http\Middleware\CheckUserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;

use Tests\TestCase;

class MiddlewareTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test - Update product 
     */
    //pass
    public function testAdminUpdateProductAllowed()
    {
        try {
            // Create an admin user
            $adminUser = User::factory()->create(['resource_type' => 'admin']);
            $this->actingAs($adminUser, 'web');

            // Create a product owned by the admin user
            $product = Product::factory()->create(['user_id' => $adminUser->id]);
            $productId = $product->id;

            // Updated data for the product
            $data = [
                'product_name' => 'Updated Product Name',
                'product_price' => 20.99,
                'product_description' => 'Updated product description.',
            ];

            // Send a PUT request to update the product
            $response = $this->json('PUT', "/api/product/update/{$productId}", $data);

            // Assert that the response status is 200 OK
            $response->assertStatus(200);

            // Assert that the product in the database has been updated
            $this->assertDatabaseHas('products', [
                'id' => $productId,
                'product_name' => 'Updated Product Name',
                'product_price' => 20.99,
                'product_description' => 'Updated product description.',
            ]);
        } catch (\Exception $e) {
            // If any exception occurs during the test, it will be caught here
            throw $e;
        } finally {
            // Clean up: Delete the admin user and the product created for the test
            if (isset($adminUser)) {
                $adminUser->delete();
                $this->assertDatabaseMissing('users', ['id' => $adminUser->id]);
            }
            if (isset($product)) {
                $product->delete();
                $this->assertDatabaseMissing('products', ['id' => $productId]);
            }
        }
    }


    //pass 
    public function testNonAdminUpdateProductForbidden()
    {
        try {
            $nonAdminUser = User::factory()->create(['resource_type' => 'customer']);
            $this->actingAs($nonAdminUser);

            $product = Product::factory()->create(['user_id' => $nonAdminUser->id]);
            $productId = $product->id;

            $requestUrl = "/api/product/update/{$productId}";
            $request = Request::create($requestUrl, 'PUT');

            Route::put('/api/product/update/{id}', [ManagementController::class, 'updateProduct'])->middleware('admin');

            $response = $this->call('PUT', $requestUrl);

            $response->assertStatus(403);

        } catch (\Exception $e) {
            // If any exception occurs during the test, it will be caught here
            throw $e;
        } finally {
            // Clean up: Delete the non-admin user
            if (isset($nonAdminUser)) {
                $nonAdminUser->delete();
                $this->assertDatabaseMissing('users', ['id' => $nonAdminUser->id]);
            }

            if (isset($product)) {
                $product->delete();
                $this->assertDatabaseMissing('products', ['id' => $product->id]);
            }
        }
    }

    /**
    * Test - show all products 
    */

    //pass-done 
    public function testAdminShowProductsAllowed()
    {
        try {
            // Create an admin user
            $adminUser = User::factory()->create(['resource_type' => 'admin']);
            $this->actingAs($adminUser, 'web');

            $adminProducts = Product::factory()->count(3)->create(['user_id' => $adminUser->id]);

            $response = $this->get('/api/products');

            $response->assertStatus(200);

            foreach ($adminProducts as $product) {
                $response->assertJsonFragment([
                    'product_id' => $product->id,
                    'product_name' => $product->product_name,
                    // Add more fields as needed
                ]);
            }

            // Assert that the response contains products from all users
            $allProducts = Product::all();
            foreach ($allProducts as $product) {
                $response->assertJsonFragment([
                    'product_id' => $product->id,
                    'product_name' => $product->product_name,
                    // Add more fields as needed
                ]);
            }

            // Assert that the admin products exist in the database
            foreach ($adminProducts as $product) {
                $this->assertDatabaseHas('products', [
                    'id' => $product->id,
                    'user_id' => $adminUser->id,
                    'product_name' => $product->product_name,
                    // Add more fields as needed
                ]);
            }
        } catch (\Exception $e) {
            throw $e;
        } finally {
            // Clean up - delete admin products and admin user
            if (isset($adminProducts)) {
                foreach ($adminProducts as $product) {
                    $product->delete();
                    $this->assertDatabaseMissing('products', ['id' => $product->id]);
                }
            }

            if (isset($adminUser)) {
                $adminUser->delete();
                $this->assertDatabaseMissing('users', ['id' => $adminUser->id]);
            }
        }
    }



    //pass
    public function testNonAdminShowProductsForbidden()
    {
        try {
            // Create a non-admin user
            $nonAdminUser = User::factory()->create(['resource_type' => 'customer']);
            $this->actingAs($nonAdminUser);

            // Define the route with the admin middleware
            Route::get('/api/products', [ManagementController::class, 'showAllProducts'])->middleware('admin');

            // Send a GET request to the endpoint
            $response = $this->get('/api/products');

            // Assert that the response status is 403 Forbidden
            $response->assertStatus(403);

            // Assert that the database does not contain any products
            $this->assertDatabaseCount('products', 0);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            // Clean up - delete the non-admin user
            if (isset($nonAdminUser)) {
                $nonAdminUser->delete();
                $this->assertDatabaseMissing('users', ['id' => $nonAdminUser->id]);
            }
        }
    }
}
