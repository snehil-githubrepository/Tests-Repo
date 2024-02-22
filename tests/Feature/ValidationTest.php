<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ValidationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function testProductNamePriceDescription()
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

    /**
     * Pass
     */
    public function testProductNameIsString()
    {
        $response = $this->postJson('/api/products', [
            'name' => 123, // Invalid data type
            'description' => $this->faker->paragraph,
            'price' => 99.99,
        ]);
        $response->assertStatus(405);


        // Success case: Name is a string
        $productData = [
            'name' => $this->faker->name,
            'description' => $this->faker->paragraph,
            'price' => 99.99,
        ];
        $response = $this->postJson('/api/products', $productData);
        $response->assertStatus(201);
    }

    public function testProductPriceMustBeNumeric()
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
