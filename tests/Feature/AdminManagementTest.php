<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Exceptions\TestException;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * Store Product Check
     */

    //pass-done
    public function testStoreProduct() //try catch implementation 
    {   
        try {
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
            
            $response->assertJsonStructure([
                'message', 
                'product' => [
                    'product_id'
                ]
            ]);
            
            $product = Product::where([
                'user_id' => $userId,
                'product_name' => 'Test Product',
                'product_price' => 10.99,
                'product_description' => 'This is a test product.',
            ])->first();

            // Assert that the product is stored in the database
            $this->assertDatabaseHas('products', ['id' => $product->id]);           

        } catch (\Exception $e) {
            throw $e;
        } finally {
            // Ensure records are deleted from the database after the try-catch block
            if (isset($user)) {
                $user->delete();
                $this->assertDatabaseMissing('users', ['id' => $userId]);
            }
            if (isset($product)) {
                $product->delete();
                $this->assertDatabaseMissing('products', ['id' => $product->id]);
            }
        }
    }
    // we only want product_id while asserting json

    //in our project
    //show api hit using this id  

    //pass-done be-back
    public function testStoreProductFailed()
    {   
        try {
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
            
            // Assert that the request was unsuccessful due to validation error
            $response->assertStatus(422);  // Validation error

            $this->assertDatabaseMissing('products', [
                'user_id' => $userId,
                'product_name' => 'Test Product',
                'product_description' => 'This is a test product.',
            ]);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            if (isset($user)) {
                $user->delete();
                $this->assertDatabaseMissing('users', ['id' => $userId]);
            }
            
            $this->assertDatabaseCount('products', 0);
            
        }
        
        //where is the error in which field 

        //create case where product description is wrong 

        //figure out in maximum 2 methods and create each case where is it failing for product desc? product name? prod_price? ....

        //no if else condition , 
    }

    /**
     * Update Product Test
     */

    //pass-done
    public function testUpdateProductSuccessTest() {   
        try {
            $user = User::factory()->create(['resource_type' => 'admin']);
            $userId = $user->id;

            $this->actingAs($user);

            $product = Product::factory()->create(['user_id' => $userId]);

            // Updated data
            $data = [
                'product_name' => 'Updated Product',
                'product_price' => 15.99,
                'product_description' => 'This is an updated product.',
            ];

            $response = $this->putJson("/api/product/update/{$product->id}", $data);

            // Assert response status and JSON structure
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

            // Assert that the database has the updated product name
            $this->assertDatabaseHas('products', [
                'id' => $product->id,
                'product_name' => 'Updated Product',
            ]);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            if (isset($user)) {
                $user->delete();
                $this->assertDatabaseMissing('users', ['id' => $userId]);
            }
            if (isset($product)) {
                $product->delete();
                $this->assertDatabaseMissing('products', ['id' => $product->id]);
            }
        }
    }


    //pass-done
    public function testUpdateProductFailureTest()
    {   
        try {
            $user = User::factory()->create(['resource_type' => 'customer']);
            $userId = $user->id;

            $this->actingAs($user);

            $product = Product::factory()->create(['user_id' => $userId]);

            // Updated data
            $data = [
                'product_name' => 'Updated Product 2',
                'product_price' => 15.992,
                'product_description' => 'This is an updated product 2.',
            ];

            $response = $this->putJson("/api/product/update/{$product->id}", $data);
            
            // Assert response status
            $response->assertStatus(403); // Unauthorized

            // Ensure that the product is not updated in the database with any of the $data fields
            $this->assertDatabaseMissing('products', [
                'id' => $product->id,
                'product_name' => $data['product_name'],
                'product_price' => $data['product_price'],
                'product_description' => $data['product_description'],
            ]);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            // Ensure records are deleted from the database after the try-catch block
            if (isset($user)) {
                $user->delete();
                $this->assertDatabaseMissing('users', ['id' => $userId]);
            }
            if (isset($product)) {
                $product->delete();
                $this->assertDatabaseMissing('products', ['id' => $product->id]);
            }
        }
    }

    
    //pass-done
    public function testUpdateProductFailureTestForValidation()
    {   
        try {
            $user = User::factory()->create(['resource_type' => 'admin']);
            $userId = $user->id;

            $this->actingAs($user);

            $product = Product::factory()->create(['user_id' => $userId]);

            // Updated data
            $data = [
                // 'product_name' => 'Updated Product 3',
                'product_price' => 15.9921,
                'product_description' => 'This is an updated product 3.',
            ];

            $response = $this->putJson("/api/product/update/{$product->id}", $data);
            
            // Assert response status
            $response->assertStatus(422); // Validation error

             // Ensure that the product is not updated in the database with any of the $data fields
             $databaseCheck = ['id' => $product->id];

            // Add the provided fields to the database check array
            if (isset($data['product_name'])) {
                $databaseCheck['product_name'] = $data['product_name'];
            }

            if (isset($data['product_price'])) {
                $databaseCheck['product_price'] = $data['product_price'];
            }

            if (isset($data['product_description'])) {
                $databaseCheck['product_description'] = $data['product_description'];
            }

            // Ensure that the product is not updated in the database with any of the provided fields
            $this->assertDatabaseMissing('products', $databaseCheck); 

        } catch (\Exception $e) {
            throw $e;
        } finally {
            // Ensure records are deleted from the database after the try-catch block
            if (isset($user)) {
                $user->delete();
                $this->assertDatabaseMissing('users', ['id' => $userId]);
            }
            if (isset($product)) {
                $product->delete();
                $this->assertDatabaseMissing('products', ['id' => $product->id]);
            }
        } 
    }

    /**
     * Delete product Test
     */

    //pass-done
    public function testDeleteProduct()
    {
        try {
            $user = User::factory()->create([
                'resource_type' => 'admin'
            ]);
            $this->actingAs($user);
            
            $product = Product::factory()->create(['user_id' => $user->id]);
            $productId = $product->id; // Get the ID before deletion
        
            $response = $this->delete("/api/product/{$product->id}");
        
            $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Product deleted successfully',
                    'deleted_product_id' => $productId // Check for the deleted product ID
                ]);
        
            $this->assertDatabaseMissing('products', ['id' => $productId]);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            // Ensure records are deleted from the database after the try-catch block
            if (isset($user)) {
                $user->delete();
                $this->assertDatabaseMissing('users', ['id' => $user->id]);
            }
        }
    }


    //pass-done
    public function testDeleteProductForCustomerNotAllowed()
    {   
        try {
            $user = User::factory()->create([
                'resource_type' => 'customer'
            ]);
            $this->actingAs($user);
            
            $product = Product::factory()->create(['user_id' => $user->id]);
            $productId = $product->id; // Get the ID before deletion
        
            $response = $this->delete("/api/product/{$product->id}");
        
            $response->assertStatus(403);

            $this->assertDatabaseHas('products', ['id' => $productId]);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            // Ensure records are deleted from the database after the try-catch block
            if (isset($user)) {
                $user->delete();
                $this->assertDatabaseMissing('users', ['id' => $user->id]);
            }
            if (isset($product)) {
                $product->delete();
                $this->assertDatabaseMissing('products', ['id' => $product->id]);
            }
        }
    }

    /**
     * Show All products check
     */

    //pass-done
    public function testShowAllProductsForAdmin()
    {   
        try{
            $adminUser = User::factory()->create(['resource_type' => 'admin']);
            $this->actingAs($adminUser);

            $regularUser = User::factory()->create(['resource_type' => 'customer']);

            $prod1 = Product::factory()->create(['product_name' => 'Test Product 1', 'user_id' => $adminUser->id]);
            $prod2 = Product::factory()->create(['product_name' => 'Test Product 2', 'user_id' => $regularUser->id]);
            $prod3 = Product::factory()->create(['product_name' => 'Another Product', 'user_id' => $adminUser->id]);

            $response = $this->getJson('/api/products');
            
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

        } catch (\Exception $e) {
            throw $e;
        } finally {
            // Ensure records are deleted from the database after the try-catch block
            if (isset($adminUser)) {
                $adminUser->delete();
                $this->assertDatabaseMissing('users', ['id' => $adminUser->id]);
            }

            if (isset($regularUser)) {
                $regularUser->delete();
                $this->assertDatabaseMissing('users', ['id' => $regularUser->id]);
            }

            if (isset($prod1)) {
                $prod1->delete();
                $this->assertDatabaseMissing('products', ['id' => $prod1->id]);
            }
        
            if (isset($prod2)) {
                $prod2->delete();
                $this->assertDatabaseMissing('products', ['id' => $prod2->id]);
            }
        
            if (isset($prod3)) {
                $prod3->delete();
                $this->assertDatabaseMissing('products', ['id' => $prod3->id]);
            }
        }
    }

    //pass-done
    public function testShowAllProductsForCustomerFailed()
    {   
        try {

            $customerUser = User::factory()->create(['resource_type' => 'customer']);
            $this->actingAs($customerUser);

            $adminUser = User::factory()->create(['resource_type' => 'admin']);

            $prod1 = Product::factory()->create(['product_name' => 'Test Product 1', 'user_id' => $customerUser->id]);
            $prod2 =Product::factory()->create(['product_name' => 'Test Product 2', 'user_id' => $customerUser->id]);
            $prod3 =Product::factory()->create(['product_name' => 'Another Product', 'user_id' => $adminUser->id]);

            $response = $this->getJson('/api/products');
            
            $response->assertStatus(403); //unauthorized 

        } catch (\Exception $e) {
            throw $e;
        } finally {
            // Ensure records are deleted from the database after the try-catch block
            if (isset($customerUser)) {
                $customerUser->delete();
                $this->assertDatabaseMissing('users', ['id' => $customerUser->id]);
            }
        
            if (isset($adminUser)) {
                $adminUser->delete();
                $this->assertDatabaseMissing('users', ['id' => $adminUser->id]);
            }
        
            // Delete all products created by both users
            if (isset($prod1)) {
                $prod1->delete();
                $this->assertDatabaseMissing('products', ['id' => $prod1->id]);
            }
        
            if (isset($prod2)) {
                $prod2->delete();
                $this->assertDatabaseMissing('products', ['id' => $prod2->id]);
            }
        
            if (isset($prod3)) {
                $prod3->delete();
                $this->assertDatabaseMissing('products', ['id' => $prod3->id]);
            }
        }
        
    }

    //pass-done
    public function testRegularUserCannotSeeAllProducts()
    {
        try {
            $regularUser = User::factory()->create(['resource_type' => 'customer']);
            $this->actingAs($regularUser);

            $adminUser = User::factory()->create(['resource_type' => 'admin']);

            $prod1 = Product::factory()->create(['product_name' => 'Test Product 1', 'user_id' => $regularUser->id]);
            $prod2 = Product::factory()->create(['product_name' => 'Test Product 2', 'user_id' => $adminUser->id]);
            $prod3 = Product::factory()->create(['product_name' => 'Another Product', 'user_id' => $adminUser->id]);

            $response = $this->getJson('/api/products');

            $response->assertStatus(403);

        } catch (\Exception $e) {
            throw $e;
        } finally {
            // Ensure the regular user is deleted from the database
            if (isset($regularUser)) {
                $regularUser->delete();
                $this->assertDatabaseMissing('users', ['id' => $regularUser->id]);
            }
            if (isset($adminUser)) {
                $adminUser->delete();
                $this->assertDatabaseMissing('users', ['id' => $adminUser->id]);
            }
        
            // Ensure the products created for the test are deleted from the database
            if (isset($prod1)) {
                $prod1->delete();
                $this->assertDatabaseMissing('products', ['id' => $prod1->id]);
            }
            if (isset($prod2)) {
                $prod2->delete();
                $this->assertDatabaseMissing('products', ['id' => $prod2->id]);
            }
            if (isset($prod3)) {
                $prod3->delete();
                $this->assertDatabaseMissing('products', ['id' => $prod3->id]);
            }
        }             
    } 

    /**
     * Search Products Test
     */
    //pass-done
    public function testSearchProducts()
    {      
        try {

        $user = User::factory()->create();
        $this->actingAs($user);

        // Create products for testing
        $prod1 = Product::factory()->create(['product_name' => 'Test Product 1', 'user_id' => $user->id]);
        $prod2 = Product::factory()->create(['product_name' => 'Test Product 2', 'user_id' => $user->id]);
        $prod3 = Product::factory()->create(['product_name' => 'Another Product', 'user_id' => $user->id]);

        // Search for products
        $response = $this->getJson('/api/products/search?query=Test');

        // throw new \Exception("Failed to get 200 OK status code.");

        \Log::info("assert status is called");
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

        $this->assertDatabaseHas('products', ['product_name' => 'Test Product 1']);
        $this->assertDatabaseHas('products', ['product_name' => 'Test Product 2']);
        $this->assertDatabaseHas('products', ['product_name' => 'Another Product']);

        } catch (\Exception $e) {
            \Log::info("inside catch block");
            throw new TestException($e->getMessage());
        } finally {
            if (isset($user)) {
                $user->delete();
                $this->assertDatabaseMissing('users', ['id' => $user->id]);
            }

            // Delete the products created for the test
            if (isset($prod1)) {
                $prod1->delete();
                $this->assertDatabaseMissing('products', ['id' => $prod1->id]);
            }
            if (isset($prod2)) {
                $prod2->delete();
                $this->assertDatabaseMissing('products', ['id' => $prod2->id]);
            }
            if (isset($prod3)) {
                $prod3->delete();
                $this->assertDatabaseMissing('products', ['id' => $prod3->id]);
            }
        }
    } 
}
