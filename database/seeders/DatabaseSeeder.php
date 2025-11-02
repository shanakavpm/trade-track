<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('Starting database seeding...');
        $startTime = microtime(true);
        
        Schema::disableForeignKeyConstraints();

        try {
            // Seed tables in dependency order
            $this->call([
                UsersTableSeeder::class,
                ProductsTableSeeder::class,
                OrdersTableSeeder::class,
                RefundsTableSeeder::class,
            ]);
            
            $this->displaySummary(round(microtime(true) - $startTime, 2));
            
        } catch (\Exception $e) {
            $this->command->error("Seeding failed: {$e->getMessage()}");
            throw $e;
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    /**
     * Display seeding summary with record counts.
     */
    private function displaySummary(float $executionTime): void
    {
        $tables = ['users', 'products', 'orders', 'order_items', 'payments', 'refunds'];
        
        $this->command->info("\nSeeding completed in {$executionTime}s");
        $this->command->table(
            ['Table', 'Records'],
            collect($tables)->map(fn($table) => [
                str_replace('_', ' ', ucfirst($table)),
                number_format(DB::table($table)->count())
            ])
        );
    }
}
