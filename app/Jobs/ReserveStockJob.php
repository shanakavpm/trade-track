<?php

namespace App\Jobs;

use App\Jobs\Middleware\WithoutOverlappingOrder;
use App\Models\Order;
use App\Services\Stock\StockService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ReserveStockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [10, 30, 60];

    public function __construct(
        public int $orderId,
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
            Log::error('Order not found for stock reservation', ['order_id' => $this->orderId]);
            return;
        }

        if ($order->status !== 'pending') {
            Log::info('Order not in pending status, skipping stock reservation', [
                'order_id' => $this->orderId,
                'status' => $order->status,
            ]);
            return;
        }

        $allReserved = true;

        foreach ($order->items as $item) {
            $reserved = $stockService->reserve($item->product_id, $item->quantity);
            if (!$reserved) {
                $allReserved = false;
                break;
            }
        }

        if ($allReserved) {
            $order->update(['status' => 'processing']);
            Log::info('Stock reserved successfully', ['order_id' => $this->orderId]);
            
            SimulatePaymentJob::dispatch($this->orderId);
        } else {
            $order->update(['status' => 'failed']);
            Log::error('Stock reservation failed', ['order_id' => $this->orderId]);
            
            FinalizeOrRollbackJob::dispatch($this->orderId, false);
        }
    }

    public function tags(): array
    {
        return ['orders', 'reserve-stock', 'order:' . $this->orderId];
    }
}
