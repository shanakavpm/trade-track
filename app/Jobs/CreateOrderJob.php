<?php

namespace App\Jobs;

use App\DTOs\OrderImportRow;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [10, 30, 60];

    public function __construct(
        public OrderImportRow $dto,
    ) {
        $this->onQueue('orders');
    }

    public function handle(): void
    {
        $idempotencyKey = $this->dto->getIdempotencyKey();

        // Check if order already exists
        $existingOrder = Order::where('idempotency_key', $idempotencyKey)->first();
        if ($existingOrder) {
            Log::info('Order already exists, skipping', [
                'idempotency_key' => $idempotencyKey,
                'order_id' => $existingOrder->id,
            ]);
            return;
        }

        DB::transaction(function () use ($idempotencyKey) {
            $product = Product::where('sku', $this->dto->sku)->lockForUpdate()->first();

            if (!$product) {
                Log::error('Product not found', ['sku' => $this->dto->sku]);
                return;
            }

            $subtotal = $this->dto->qty * $this->dto->unitPrice;

            $order = Order::create([
                'idempotency_key' => $idempotencyKey,
                'customer_id' => $this->dto->customerId,
                'status' => 'pending',
                'total' => number_format($subtotal, 2, '.', ''),
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $this->dto->qty,
                'unit_price' => number_format($this->dto->unitPrice, 2, '.', ''),
                'subtotal' => number_format($subtotal, 2, '.', ''),
            ]);

            Log::info('Order created', [
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'total' => $order->total,
            ]);

            // Start order workflow
            ReserveStockJob::dispatch($order->id);
        });
    }

    public function tags(): array
    {
        return ['orders', 'create', 'customer:' . $this->dto->customerId];
    }
}
