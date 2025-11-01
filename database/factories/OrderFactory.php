<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'idempotency_key' => hash('sha256', fake()->unique()->uuid()),
            'customer_id' => Customer::factory(),
            'status' => fake()->randomElement(['pending', 'processing', 'completed', 'failed']),
            'total' => fake()->randomFloat(2, 10, 1000),
            'completed_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }
}
