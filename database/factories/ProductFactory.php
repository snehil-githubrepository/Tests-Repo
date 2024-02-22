<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition()
    {
        return [
            'user_id' => $this->faker->numberBetween(1, 10),
            'product_name' => $this->faker->name,
            'product_price' => $this->faker->randomFloat(2, 1, 100),
            'product_description' => $this->faker->sentence,
        ];
    }
}
