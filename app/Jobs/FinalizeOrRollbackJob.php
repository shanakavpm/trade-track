<?php

namespace App\Jobs;

use App\DTOs\OrderProcessedPayload;
use App\Events\OrderProcessed;
use App\Jobs\Middleware\WithoutOverlappingOrder;
use App\Models\Order;
use App\Services\Stock\StockService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FinalizeOrRollbackJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [10, 30, 60];

    public function __construct(
        public int $orderId,
        public bool $success,
    ) {
        $this->onQueue('orders');
    }

    public function middleware(): array
    {
        return [new WithoutOverlappingOrder($this->orderId)];
    }

    public function handle(StockService $stockService): void
    {
        $order = Order::with('items.product')->find($this->orderId);

        if (!$order) {
            Log::error('Order not found for finalization', ['order_id' => $this->orderId]);
            return;
        }

        if ($this->success) {
            $order->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            Log::info('Order completed successfully', [
                'order_id' => $this->orderId,
                'total' => $order->total,
            ]);
        } else {
            // Rollback stock
            foreach ($order->items as $item) {
                $stockService->restore($item->product_id, $item->quantity);
            }

            $order->update(['status' => 'failed']);

            Log::warning('Order failed and rolled back', ['order_id' => $this->orderId]);
        }

        // Fire event for KPI updates and notifications
        $payload = new OrderProcessedPayload(
            orderId: $order->id,
            customerId: $order->customer_id,
            status: $order->status,
            total: (float) $order->total,
        );

        event(new OrderProcessed($payload));
    }

    public function tags(): array
    {
        return ['orders', 'finalize', 'order:' . $this->orderId];
    }
}
