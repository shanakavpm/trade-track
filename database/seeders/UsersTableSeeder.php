<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        $count = (int) env('SEED_USERS', 5);
        
        $this->command->info("Seeding {$count} users...");

        // Create admin user
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        // Create remaining users
        User::factory()->count($count - 1)->create();

        $this->command->info("✓ Created {$count} users");
    }
}
