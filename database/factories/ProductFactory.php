<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $faker = \Faker\Factory::create();
        
        $productNames = [
            'Laptop', 'Smartphone', 'Tablet', 'Headphones', 'Keyboard',
            'Mouse', 'Monitor', 'Webcam', 'Microphone', 'Speaker',
        ];

        return [
            'sku' => strtoupper($faker->unique()->bothify('PRD-####??')),
            'name' => $faker->randomElement($productNames) . ' ' . $faker->word(),
            'description' => $faker->optional(0.7)->sentence(12),
            'price' => $faker->randomFloat(2, 9.99, 999.99),
            'stock_quantity' => $faker->numberBetween(0, 500),
        ];
    }
}
