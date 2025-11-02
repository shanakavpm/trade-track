<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('Seeding minimal data for CSV import...');
        
        // Run seeders in order
        $this->call([
            UserSeeder::class,
            ProductSeeder::class,
        ]);
        
        $this->command->info('');
        $this->command->info('✅ Database ready for CSV import!');
        $this->command->info('   Run: php artisan orders:import orders_sample.csv');
    }
}
