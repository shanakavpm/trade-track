<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'idempotency_key' => Str::uuid()->toString(),
            'customer_id' => Customer::factory(),
            'status' => $this->faker->randomElement(['pending', 'processing', 'completed', 'failed', 'cancelled']),
            'total' => $this->faker->randomFloat(2, 10, 1000),
            'completed_at' => $this->faker->optional()->dateTime(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
