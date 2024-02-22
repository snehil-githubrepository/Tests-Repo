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

    //pass
    public function testProductResourceStructure()
    {
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
    }

    //fail-check : Product_id not visible in $actualKeys 
    public function testIncompleteProductData()
    {
            $product = new Product([
                'product_id' => 1, 
                'user_id' => 1, 
                'product_name' => 'Incomplete Product',
                // 'product_price' => 10.99, // Missing 
                'product_description' => 'This product is missing some data.',
            ]);
        
            $resource = new ProductResource($product);

            // Use makeVisible to temporarily make product_id visible
            $product->makeVisible(['product_id']);

            $expectedKeys = array_keys($resource->toArray(request()));
            // dd($expectedKeys);
            $actualKeys = array_keys($product->toArray());
            // dd($actualKeys);
            $missingFields = array_diff($expectedKeys, $actualKeys);
            dd($missingFields);

            $this->assertEmpty($missingFields, 'Missing fields: ' . implode(', ', $missingFields));
    }


    /*
    * User Resource Layer Testing
    **/

    //pass
    public function testUserResourceStructure()
    {
        // Create a user for testing
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
            'Resource' => 'customer',
        ], $data);
    }

    //fail-check
    public function testUserResourceFailure()
    {
        $user = User::factory()->create([
            'name' => 'Incomplete User', 
            // 'email' => 'test@example.com',
            'resource_type' => 'customer',
        ]);
    
        $resource = new UserResource($user);
    
        $data = $resource->toArray(request());
    
        $this->assertArrayNotHasKey('username', $data);
        $this->assertArrayNotHasKey('email', $data);
        $this->assertArrayNotHasKey('Resource', $data);
    }
    
}
