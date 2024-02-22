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
    
    /**
     * Store Product Check
     */

    //pass
    public function testStoreProduct()
    {   
        $user = User::factory()->create();
        $userId = $user->id;
        $this->actingAs($user);
        
        // Prepare data for the new product, using the user's ID
        $data = [
            'user_id' => $userId,
            'product_name' => 'Test Product',
            'product_price' => 10.99,
            'product_description' => 'This is a test product.',
        ];
        
        $response = $this->postJson('/api/product/store', $data);
        
        // Assert that the request was successful (status code 201)
        $response->assertStatus(201);        

        //assertJson to give both keys and values
        $response->assertJson([
            'message' => 'Product created successfully',
            'product' => [
                //prod_id can be dynamic as well 
                'product_id' => 1,
                'user_id' => $userId,
                'product_name' => 'Test Product',
                'product_price' => 10.99,
                'product_description' => 'This is a test product.',
            ],
        ]);
    }
    //pass
    public function testStoreProductFailed()
    {   
        $user = User::factory()->create();
        $userId = $user->id;
        $this->actingAs($user);
        
        // Prepare data for the new product, using the user's ID
        $data = [
            'user_id' => $userId,
            'product_name' => 'Test Product',
            // 'product_price' => 10.99,
            'product_description' => 'This is a test product.',
        ];
        
        $response = $this->postJson('/api/product/store', $data);
        
        $response->assertStatus(422);  //validation error
    }

    /**
     * Update Product Test
     */

    //pass
    public function testUpdateProductSuccessTest()
    {
        $user = User::factory()->create(['resource_type' => 'admin']);
        $userId = $user->id;

        $this->actingAs($user);

        $product = Product::factory()->create(['user_id' => $userId]);

        //updated data
        $data = [
            'product_name' => 'Updated Product',
            'product_price' => 15.99,
            'product_description' => 'This is an updated product.',
        ];

        $response = $this->putJson("/api/product/update/{$product->id}", $data);
        //jsonStructure to only give keys , no value
        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'product' => [
                    'product_id',
                    'product_name',
                    'product_price',
                    'product_description',
                ],
            ]);
    }

    //pass
    public function testUpdateProductFailureTest()
    {
        $user = User::factory()->create(['resource_type' => 'customer']);
        $userId = $user->id;

        $this->actingAs($user);

        $product = Product::factory()->create(['user_id' => $userId]);

        //updated data
        $data = [
            'product_name' => 'Updated Product 2',
            'product_price' => 15.992,
            'product_description' => 'This is an updated product 2.',
        ];

        $response = $this->putJson("/api/product/update/{$product->id}", $data);
        
        $response->assertStatus(403); //UnAuthorized
    }
    
    //pass
    public function testUpdateProductFailureTestForValidation()
    {
        $user = User::factory()->create(['resource_type' => 'admin']);
        $userId = $user->id;

        $this->actingAs($user);

        $product = Product::factory()->create(['user_id' => $userId]);

        //updated data
        $data = [
            // 'product_name' => 'Updated Product 3',
            'product_price' => 15.9921,
            'product_description' => 'This is an updated product 3.',
        ];

        $response = $this->putJson("/api/product/update/{$product->id}", $data);
        
        $response->assertStatus(422); //Validation error
    }

    /**
     * Delete product Test
     */

    //pass
    public function testDeleteProduct()
    {
        // Create a user
        $user = User::factory()->create([
            'resource_type' => 'admin'
        ]);
        $this->actingAs($user);
        
        // Create a product
        $product = Product::factory()->create(['user_id' => $user->id]);
        $productId = $product->id; // Get the ID before deletion
    
        // Send a DELETE request to delete the product
        $response = $this->delete("/api/product/{$product->id}");
    
        // Check if the product is deleted successfully
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Product deleted successfully',
                'deleted_product_id' => $productId // Check for the deleted product ID
            ]);
    
        // Ensure that the product is actually deleted from the database
        $this->assertDatabaseMissing('products', ['id' => $productId]);
    }

    //pass
    public function testDeleteProductForCustomerNotAllowed()
    {
        // Create a user
        $user = User::factory()->create([
            'resource_type' => 'customer'
        ]);
        $this->actingAs($user);
        
        // Create a product
        $product = Product::factory()->create(['user_id' => $user->id]);
        $productId = $product->id; // Get the ID before deletion
    
        // Send a DELETE request to delete the product
        $response = $this->delete("/api/product/{$product->id}");
    
        // Check if the product is deleted successfully
        $response->assertStatus(403);
    }

    /**
     * Show All products check
     */

    //pass
    public function testShowAllProductsForAdmin()
    {
        $adminUser = User::factory()->create(['resource_type' => 'admin']);
        $this->actingAs($adminUser);

        $regularUser = User::factory()->create(['resource_type' => 'customer']);

        // Create products for testing
        Product::factory()->create(['product_name' => 'Test Product 1', 'user_id' => $adminUser->id]);
        Product::factory()->create(['product_name' => 'Test Product 2', 'user_id' => $regularUser->id]);
        Product::factory()->create(['product_name' => 'Another Product', 'user_id' => $adminUser->id]);

        // Access the products endpoint
        $response = $this->getJson('/api/products');
        
        // Assert that the response is successful
        $response->assertStatus(200);

        // Assert that the response contains the expected number of products and has the correct structure
        $response->assertJsonCount(3, 'data'); // Check if all products are returned
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'product_id',
                    'product_name',
                    'product_price',
                    'product_description',
                ],
            ],
        ]);
    }

    //pass
    public function testShowAllProductsForCustomerFailed()
    {
        $customerUser = User::factory()->create(['resource_type' => 'customer']);
        $this->actingAs($customerUser);

        $adminUser = User::factory()->create(['resource_type' => 'admin']);

        // Create products for testing
        Product::factory()->create(['product_name' => 'Test Product 1', 'user_id' => $customerUser->id]);
        Product::factory()->create(['product_name' => 'Test Product 2', 'user_id' => $customerUser->id]);
        Product::factory()->create(['product_name' => 'Another Product', 'user_id' => $adminUser->id]);

        // Access the products endpoint
        $response = $this->getJson('/api/products');
        
        // Assert that the response is successful
        $response->assertStatus(403);
    }

    //pass
    public function testRegularUserCannotSeeAllProducts()
    {
        // Create a regular user
        $regularUser = User::factory()->create(['resource_type' => 'customer']);
        $this->actingAs($regularUser);

        // Try to access the products endpoint as a regular user
        $response = $this->getJson('/api/products');

        // Assert that the response status code is 403 Forbidden
        $response->assertStatus(403);
    }

    /**
     * Search Products Test
     */
    //pass
    public function testSearchProducts()
    {   
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create products for testing
        Product::factory()->create(['product_name' => 'Test Product 1', 'user_id' => $user->id]);
        Product::factory()->create(['product_name' => 'Test Product 2', 'user_id' => $user->id]);
        Product::factory()->create(['product_name' => 'Another Product', 'user_id' => $user->id]);

        // Search for products
        $response = $this->getJson('/api/products/search?query=Test');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data') // Check if the correct number of products are returned
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'product_id',
                        'product_name',
                        'product_price',
                        'product_description',
                    ],
                ],
            ]);
    }

}
