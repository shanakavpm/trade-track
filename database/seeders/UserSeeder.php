<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create();
        
        // Create test users (customers for orders)
        User::factory()->create([
            'id' => 1,
            'email' => 'shanakavpm@gmail.com',
            'name' => $faker->name('male'),
        ]);
        
        User::factory()->create([
            'id' => 2,
            'email' => 'customer2@example.com',
            'name' => $faker->name('female'),
        ]);
        
        User::factory()->create([
            'id' => 3,
            'email' => 'customer3@example.com',
            'name' => $faker->name(),
        ]);

        $this->command->info('✓ Created 3 users with random names');
    }
}
