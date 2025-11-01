<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'order_id' => Order::factory(),
            'type' => $this->faker->randomElement(['order_success', 'order_failed', 'refund_processed']),
            'channel' => $this->faker->randomElement(['email', 'sms', 'log']),
            'payload' => [
                'order_id' => $this->faker->numberBetween(1, 1000),
                'customer_id' => $this->faker->numberBetween(1, 100),
                'status' => $this->faker->randomElement(['completed', 'failed']),
                'total' => $this->faker->randomFloat(2, 10, 1000),
            ],
            'sent_at' => $this->faker->dateTime(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
