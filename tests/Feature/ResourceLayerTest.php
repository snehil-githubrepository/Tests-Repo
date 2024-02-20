<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResourceLayerTest extends TestCase
{
    use RefreshDatabase;

    public function product_resource_structure()
    {
        $product = Product::factory()->create();

        $response = $this->json('GET', '/api/products/' . $product->id);
        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'name',
            'description',
            'price',
        ]);
    }

    public function user_resource_structure()
    {
        $user = User::factory()->create();

        $response = $this->json('GET', '/api/users/' . $user->id);
        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'name',
            'email',
            'resource_type',
            // 'products' => [
            //     '*' => [
            //         'id',
            //         'name',
            //         'description',
            //         'price',
            //         // Add more product attributes as needed
            //     ],
            // ], // for multiple product items
        ]);
    }
}
