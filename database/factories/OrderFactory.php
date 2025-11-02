<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => User::factory(),
            'status' => OrderStatus::random()->value,
            'total' => 0, // Will be calculated after items are created
            'idempotency_key' => Str::uuid()->toString(),
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this;
    }

    /**
     * Indicate that the order should have items created automatically.
     */
    public function withItems(): static
    {
        return $this->afterCreating(function (\App\Models\Order $order) {
            // Create 1-5 order items
            $itemsCount = rand(1, 5);
            $total = 0;

            for ($i = 0; $i < $itemsCount; $i++) {
                $product = Product::inRandomOrder()->first() ?? Product::factory()->create();
                $quantity = rand(1, 3);
                $price = $product->price;
                $subtotal = bcmul((string)$price, (string)$quantity, 2);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $price,
                    'subtotal' => $subtotal,
                ]);

                $total = bcadd((string)$total, (string)$subtotal, 2);
            }

            // Update order total
            $order->update(['total' => $total]);
        });
    }

    /**
     * Indicate that the order should not auto-create items.
     */
    public function withoutItems(): static
    {
        return $this;
    }

    /**
     * Indicate that the order is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::PENDING->value,
        ]);
    }

    /**
     * Indicate that the order is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::COMPLETED->value,
        ]);
    }

    /**
     * Indicate that the order is failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::FAILED->value,
        ]);
    }
}
