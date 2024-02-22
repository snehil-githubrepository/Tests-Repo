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

    //pass 
    public function testIncompleteProductData()
    {
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
                'user_id' , 
                'product_name',
                'product_price' , // Missing 
                'product_description'
            ];
            // dd($actualKeys);
            $missingFields = array_diff($expectedKeys, $actualKeys);
            // dd($missingFields);

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

    //pass
    public function testUserResourceFailure()
    {
        $user =new User([
            'name' => 'Incomplete User', 
            // 'email' => 'test@example.com',
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
    }
    
}
