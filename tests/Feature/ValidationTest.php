<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ValidationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function create_product_requires_name_description_and_price()
    {
        $response = $this->postJson('/api/products', []);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'description', 'price']);

        $productData = [
            'name' => $this->faker->name,
            'description' => $this->faker->paragraph,
            'price' => 99.99,
        ];
        $response = $this->postJson('/api/products', $productData);
        $response->assertStatus(201);
    }

    /** @test */
    public function product_name_must_be_a_string()
    {
        $response = $this->postJson('/api/products', [
            'name' => 123, // Invalid data type
            'description' => $this->faker->paragraph,
            'price' => 99.99,
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);

        // Success case: Name is a string
        $productData = [
            'name' => $this->faker->name,
            'description' => $this->faker->paragraph,
            'price' => 99.99,
        ];
        $response = $this->postJson('/api/products', $productData);
        $response->assertStatus(201);
    }

    public function product_price_must_be_numeric()
    {
        $response = $this->postJson('/api/products', [
            'name' => $this->faker->name,
            'description' => $this->faker->paragraph,
            'price' => 'invalid', // Invalid data type
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);

        // Success case: Price is numeric
        $productData = [
            'name' => $this->faker->name,
            'description' => $this->faker->paragraph,
            'price' => 99.99,
        ];
        $response = $this->postJson('/api/products', $productData);
        $response->assertStatus(201);
    }
}
