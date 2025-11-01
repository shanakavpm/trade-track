<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Create some sample products
        $products = [
            ['sku' => 'PROD001', 'name' => 'Premium Widget', 'price' => 99.99, 'stock_quantity' => 100],
            ['sku' => 'PROD002', 'name' => 'Basic Widget', 'price' => 49.99, 'stock_quantity' => 200],
            ['sku' => 'PROD003', 'name' => 'Deluxe Widget', 'price' => 199.99, 'stock_quantity' => 50],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        // Create additional random products
        Product::factory(7)->create();
    }
}
