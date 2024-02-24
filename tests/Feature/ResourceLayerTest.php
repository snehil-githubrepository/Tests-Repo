<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Resources\ProductResource;
use App\Http\Resources\UserResource;
use Tests\TestCase;

class ResourceLayerTest extends TestCase
{
    use RefreshDatabase;

    /*
    * Product Resource Layer Testing
    **/

    //pass-done
    public function testProductResourceStructure()
    {   try {
        $product = new Product([
            'product_id' => 1, 
            'user_id' => 1, 
            'product_name' => 'Test Product',
            'product_price' => 10.99, 
            'product_description' => 'This is a test product description.',
        ]);
        $productArray = $product->toArray();

        $resource = new ProductResource($product);
        $data = $resource->toArray(request());
        
        $this->assertEquals([
            'product_id' => $product->id,
            'user_id' => $product->user_id,
            'product_name' => 'Test Product',
            'product_price' => 10.99,
            'product_description' => 'This is a test product description.',
        ], $data);

        } catch (\Exception $e) {
            $this->fail('An unexpected exception occurred: ' . $e->getMessage());
        } finally {
            if (isset($product)) {
                $product->delete();
                $this->assertDatabaseMissing('products', ['id' => $product->id]);
            }
        }

    }

    //pass-done (is it supposed to fail) or we can make it otherways
    public function testIncompleteProductData()
    {
        try {
            $product = new Product([
                'user_id' => 1, 
                'product_name' => 'Incomplete Product',
                'product_price' => 10.99, // Missing 
                'product_description' => 'This product is missing some data.',
            ]);
        
            $resource = new ProductResource($product);
            // dd($resource);
    
            $expectedKeys = array_keys($resource->toArray(request()));
            
            $actualKeys = [
                'product_id' ,
                // 'user_id' , 
                'product_name',
                'product_price' , // Missing 
                'product_description'
            ];
            // dd($actualKeys);
            $missingFields = array_diff($expectedKeys, $actualKeys);
            // dd($missingFields);
    
            $this->assertEmpty($missingFields, 'Missing fields: ' . implode(', ', $missingFields));
        } catch (\Exception $e) {
            // Handle any exceptions that occur during the test
            $this->fail('An unexpected exception occurred: ' . $e->getMessage());
        } finally {
            if (isset($product)) {
                $product->delete();
                $this->assertDatabaseMissing('products', ['id' => $product->id]);
            }
        }
    }

    /*
    * User Resource Layer Testing
    **/

    //pass-done
    public function testUserResourceStructure()
    {
        try {
            $user = User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'resource_type' => 'customer',
            ]);
    
            $resource = new UserResource($user);
            $data = $resource->toArray(request());
            $this->assertEquals([
                'id' => $user->id,
                'username' => 'Test User',
                'email' => 'test@example.com',
                'resource' => 'customer',
            ], $data);
        } catch (\Exception $e) {
            // Handle any exceptions that occur during the test
            $this->fail('An unexpected exception occurred: ' . $e->getMessage());
        } finally {
            // Clean up: Delete the user
            if (isset($user)) {
                $user->delete();
                $this->assertDatabaseMissing('users', ['id' => $user->id]);
            }
        }
    }

    //pass //supposed-to-fail? 
    public function testUserResourceFailure()
    {
        try {
            $user =new User([
                'name' => 'Incomplete User', 
                'email' => 'test@example.com', //Missing
                'password' => 'testpass',
                'resource_type' => 'customer',
            ]);

            $resource = new UserResource($user);
            
            $expectedKeys = array_keys($resource->toArray(request()));
                
            $actualKeys = [
                'id' , 
                'username',
                // 'email' , // Missing , just remove this to have fail in test
                'resource'
            ];
        
            $missingFields = array_diff($expectedKeys, $actualKeys);

            $this->assertEmpty($missingFields, 'Missing fields: ' . implode(', ', $missingFields));
        }   catch (\Exception $e) {
            $this->fail('An unexpected exception occurred: ' . $e->getMessage());
        } finally {
            if (isset($user)) {
                $user->delete();
                $this->assertDatabaseMissing('users', ['id' => $user->id]);
            }
        }
    }
}