<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductsTableSeeder extends Seeder
{
    public function run(): void
    {
        $count = (int) env('SEED_PRODUCTS', 10);
        
        $this->command->info("Seeding {$count} products...");

        Product::factory()->count($count)->create();

        $this->command->info("✓ Created {$count} products");
    }
}
