<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        Customer::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+94771234567',
        ]);

        Customer::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'phone' => '+94777654321',
        ]);

        Customer::create([
            'name' => 'Bob Johnson',
            'email' => 'bob@example.com',
            'phone' => '+94771111111',
        ]);

        // Create additional random customers
        Customer::factory(7)->create();
    }
}
