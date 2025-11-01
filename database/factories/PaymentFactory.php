<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'status' => fake()->randomElement(['pending', 'completed', 'failed']),
            'amount' => fake()->randomFloat(2, 10, 1000),
            'transaction_id' => 'TXN-' . strtoupper(fake()->unique()->bothify('??########')),
            'callback_signature' => null,
            'callback_expires_at' => now()->addMinutes(15),
        ];
    }
}
