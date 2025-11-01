<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'sku' => 'LAPTOP-001',
                'name' => 'Dell XPS 15',
                'description' => 'High-performance laptop for professionals',
                'price' => 1299.99,
                'stock_quantity' => 50,
            ],
            [
                'sku' => 'PHONE-001',
                'name' => 'iPhone 15 Pro',
                'description' => 'Latest Apple smartphone',
                'price' => 999.99,
                'stock_quantity' => 100,
            ],
            [
                'sku' => 'TABLET-001',
                'name' => 'iPad Air',
                'description' => 'Versatile tablet for work and play',
                'price' => 599.99,
                'stock_quantity' => 75,
            ],
            [
                'sku' => 'WATCH-001',
                'name' => 'Apple Watch Series 9',
                'description' => 'Advanced smartwatch',
                'price' => 399.99,
                'stock_quantity' => 120,
            ],
            [
                'sku' => 'HEADPHONE-001',
                'name' => 'Sony WH-1000XM5',
                'description' => 'Premium noise-cancelling headphones',
                'price' => 349.99,
                'stock_quantity' => 80,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        // Create additional random products
        Product::factory(15)->create();
    }
}
