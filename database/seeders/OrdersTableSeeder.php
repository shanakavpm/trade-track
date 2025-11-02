<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrdersTableSeeder extends Seeder
{
    public function run(): void
    {
        $count = (int) env('SEED_ORDERS', 20);
        
        $this->command->info("Seeding {$count} orders...");

        $customers = User::all();
        $products = Product::all();

        if ($customers->isEmpty() || $products->isEmpty()) {
            $this->command->error('No users or products found. Run previous seeders first.');
            return;
        }

        for ($i = 0; $i < $count; $i++) {
            $this->createOrder($customers->random(), $products);
        }

        $this->command->info("✓ Created {$count} orders");
    }

    private function createOrder(User $customer, $products): void
    {
        $status = $this->getRandomStatus();
        
        $order = Order::create([
            'idempotency_key' => (string) Str::uuid(),
            'customer_id' => $customer->id,
            'status' => $status->value,
            'total' => 0,
        ]);

        // Add 1-3 items
        $total = 0;
        for ($i = 0; $i < rand(1, 3); $i++) {
            $product = $products->random();
            $quantity = rand(1, 2);
            $subtotal = $product->price * $quantity;

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $product->price,
                'subtotal' => $subtotal,
            ]);

            $total += $subtotal;
        }

        $order->update(['total' => $total]);

        // Create payment for completed orders
        if ($status === OrderStatus::COMPLETED) {
            Payment::create([
                'order_id' => $order->id,
                'amount' => $total,
                'status' => 'completed',
                'transaction_id' => 'PAY-' . strtoupper(uniqid())
            ]);
        }
    }

    private function getRandomStatus(): OrderStatus
    {
        $rand = rand(1, 100);
        return match (true) {
            $rand <= 70 => OrderStatus::COMPLETED,
            $rand <= 85 => OrderStatus::PENDING,
            default => OrderStatus::PROCESSING,
        };
    }
}
