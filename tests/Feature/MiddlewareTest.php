<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function admin_can_access_product_management_routes()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get('/admin/products');

        $response->assertStatus(200);
    }

    public function customer_cannot_access_product_management_routes()
    {
        $user = User::factory()->create(); // Non-admin user

        $response = $this->actingAs($user)->get('/admin/products');

        $response->assertStatus(403); // Forbidden
    }

    public function admin_can_access_admin_dashboard()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin, 'web')->get('/admin/dashboard');

        $response->assertStatus(200);
    }

    public function regular_user_cannot_access_admin_dashboard()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'web')->get('/admin/dashboard');

        $response->assertStatus(403);
    }
}
