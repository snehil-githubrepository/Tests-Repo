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
         $adminUser = User::factory()->create(['resource_type' => 'admin']);
         $this->actingAs($adminUser, 'web');
     
         $product = Product::factory()->create(['user_id' => $adminUser->id]);
     
         $productId = $product->id;
     
         $data = [
             'product_name' => 'Updated Product Name',
             'product_price' => 20.99,
             'product_description' => 'Updated product description.',
         ];
     
         $response = $this->json('PUT', "/api/product/update/{$productId}", $data);
     
         $response->assertStatus(200);
    }

    //pass 
    public function testNonAdminUpdateProductForbidden()
    {
        $nonAdminUser = User::factory()->create(['resource_type' => 'customer']);
        $this->actingAs($nonAdminUser);
        $request = Request::create('/api/product/update/1', 'PUT');
        Route::put('/api/product/update/{id}', [ManagementController::class, 'updateProduct'])->middleware('admin');
        $response = $this->call('PUT', '/api/product/update/1');
        $response->assertStatus(403);
    }

    /**
    * Test - show all products 
    */

    //pass
    public function testAdminShowProductsAllowed()
    {
        $adminUser = User::factory()->create(['resource_type' => 'admin']);

        $this->actingAs($adminUser, 'web');
    
        $response = $this->get('/api/products');
    
        $response->assertStatus(200);
    }

    //pass
    public function testNonAdminShowProductsForbidden()
    {
        $nonAdminUser = User::factory()->create(['resource_type' => 'customer']);

        $this->actingAs($nonAdminUser);

        Route::get('/api/products', [ManagementController::class, 'showAllProducts'])->middleware('admin');

        $response = $this->get('/api/products');
        $response->assertStatus(403);
    }

}
