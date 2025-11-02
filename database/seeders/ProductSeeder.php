<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create products matching CSV SKUs
        $products = [
            ['sku' => 'LAPTOP-001', 'name' => 'Premium Laptop', 'price' => 1299.99, 'stock_quantity' => 100],
            ['sku' => 'PHONE-001', 'name' => 'Smartphone Pro', 'price' => 999.99, 'stock_quantity' => 200],
            ['sku' => 'TABLET-001', 'name' => 'Tablet Ultra', 'price' => 599.99, 'stock_quantity' => 150],
            ['sku' => 'WATCH-001', 'name' => 'Smart Watch', 'price' => 399.99, 'stock_quantity' => 300],
            ['sku' => 'HEADPHONE-001', 'name' => 'Wireless Headphones', 'price' => 349.99, 'stock_quantity' => 250],
        ];
        
        foreach ($products as $product) {
            Product::create($product);
        }
        
        $this->command->info('✓ Created ' . count($products) . ' products');
    }
}
