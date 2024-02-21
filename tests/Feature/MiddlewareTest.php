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

    public function testAdminUpdateProductAllowed()
    {
        $adminUser = User::factory()->create(['resource_type' => 'admin']);

        $this->actingAs($adminUser, 'web');

        $request = Request::create('/product/update/1', 'PUT');

        Route::put('/product/update/{id}', [ManagementController::class, 'updateProduct'])->middleware('admin');

        $response = $this->call('PUT', '/product/update/1');
        $response->assertStatus(200);
    }

    public function testNonAdminUpdateProductForbidden()
    {
        $nonAdminUser = User::factory()->create(['resource_type' => 'customer']);
        $this->actingAs($nonAdminUser);
        $request = Request::create('/product/update/1', 'PUT');
        Route::put('/product/update/{id}', [ManagementController::class, 'updateProduct'])->middleware('admin');
        $response = $this->call('PUT', '/product/update/1');
        $response->assertStatus(403);
    }

    public function testAdminShowProductsAllowed()
    {
        $adminUser = User::factory()->create(['resource_type' => 'admin']);

        $this->actingAs($adminUser);

        Route::get('/products', [ManagementController::class, 'showAllProducts'])->middleware('admin');

        $response = $this->get('/products');
        $response->assertStatus(200);
    }

    public function testNonAdminShowProductsForbidden()
    {
        $nonAdminUser = User::factory()->create(['resource_type' => 'customer']);

        $this->actingAs($nonAdminUser);

        Route::get('/products', [ManagementController::class, 'showAllProducts'])->middleware('admin');

        $response = $this->get('/products');
        $response->assertStatus(403);
    }

    // public function admin_can_access_product_management_routes()
    // {
    //     $admin = User::factory()->create(['is_admin' => true]);

    //     $response = $this->actingAs($admin)->get('/admin/products');

    //     $response->assertStatus(200);
    // }

    // public function customer_cannot_access_product_management_routes()
    // {
    //     $user = User::factory()->create(); // Non-admin user

    //     $response = $this->actingAs($user)->get('/admin/products');

    //     $response->assertStatus(403); // Forbidden
    // }

    // public function admin_can_access_admin_dashboard()
    // {
    //     $admin = User::factory()->create(['is_admin' => true]);

    //     $response = $this->actingAs($admin, 'web')->get('/admin/dashboard');

    //     $response->assertStatus(200);
    // }

    // public function regular_user_cannot_access_admin_dashboard()
    // {
    //     $user = User::factory()->create();

    //     $response = $this->actingAs($user, 'web')->get('/admin/dashboard');

    //     $response->assertStatus(403);
    // }
}
