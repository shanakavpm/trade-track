<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class RefundFactory extends Factory
{
    public function definition(): array
    {
        return [
            'idempotency_key' => Str::uuid()->toString(),
            'order_id' => Order::factory(),
            'payment_id' => Payment::factory(),
            'amount' => $this->faker->randomFloat(2, 10, 500),
            'status' => $this->faker->randomElement(['pending', 'completed', 'failed']),
            'reason' => $this->faker->optional()->sentence(),
            'processed_at' => $this->faker->optional()->dateTime(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
