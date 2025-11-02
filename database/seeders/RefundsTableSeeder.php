<?php

namespace Database\Seeders;

use App\Enums\RefundStatus;
use App\Models\Order;
use App\Models\Refund;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RefundsTableSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info("Seeding refunds...");

        $orders = Order::where('status', 'completed')
            ->whereHas('payment')
            ->with('payment')
            ->limit(5)
            ->get();

        if ($orders->isEmpty()) {
            $this->command->warn('No completed orders found for refunds.');
            return;
        }

        $count = 0;
        foreach ($orders as $order) {
            Refund::create([
                'idempotency_key' => (string) Str::uuid(),
                'order_id' => $order->id,
                'payment_id' => $order->payment->id,
                'amount' => $order->total,
                'reason' => 'Customer requested refund',
                'status' => RefundStatus::COMPLETED->value,
                'processed_at' => now(),
            ]);
            $count++;
        }

        $this->command->info("✓ Created {$count} refunds");
    }
}
