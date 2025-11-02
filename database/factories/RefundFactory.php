<?php

namespace Database\Factories;

use App\Enums\RefundStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Refund>
 */
class RefundFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $payment = Payment::factory()->completed()->create();
        $order = $payment->order;
        
        // Random partial or full refund
        $isFullRefund = fake()->boolean(60); // 60% chance of full refund
        $amount = $isFullRefund 
            ? $order->total 
            : bcdiv((string)$order->total, (string)rand(2, 5), 2);

        return [
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'amount' => $amount,
            'reason' => $this->getRandomReason(),
            'status' => RefundStatus::COMPLETED->value,
            'processed_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Get a random refund reason.
     */
    private function getRandomReason(): string
    {
        $reasons = [
            'Customer changed mind',
            'Product not as described',
            'Duplicate order',
            'Product damaged during shipping',
            'Order arrived too late',
            'Customer requested cancellation',
            'Wrong item shipped',
            'Quality issues',
        ];

        return $reasons[array_rand($reasons)];
    }

    /**
     * Indicate that the refund is for the full amount.
     */
    public function full(): static
    {
        return $this->state(function (array $attributes) {
            $payment = Payment::find($attributes['payment_id']);
            return [
                'amount' => $payment->order->total,
            ];
        });
    }

    /**
     * Indicate that the refund is partial.
     */
    public function partial(): static
    {
        return $this->state(function (array $attributes) {
            $payment = Payment::find($attributes['payment_id']);
            return [
                'amount' => bcdiv((string)$payment->order->total, (string)rand(2, 4), 2),
            ];
        });
    }

    /**
     * Indicate that the refund is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RefundStatus::PENDING->value,
            'processed_at' => null,
        ]);
    }
}
