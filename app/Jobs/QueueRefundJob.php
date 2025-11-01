<?php

namespace App\Jobs;

use App\DTOs\RefundRequestPayload;
use App\Models\Order;
use App\Models\Refund;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class QueueRefundJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [10, 30, 60];

    public function __construct(
        public RefundRequestPayload $payload,
    ) {
        $this->onQueue('refunds');
    }

    public function handle(): void
    {
        $idempotencyKey = $this->payload->getIdempotencyKey();

        // Check if refund already exists
        $existingRefund = Refund::where('idempotency_key', $idempotencyKey)->first();
        if ($existingRefund) {
            Log::info('Refund already exists, skipping', [
                'idempotency_key' => $idempotencyKey,
                'refund_id' => $existingRefund->id,
            ]);
            return;
        }

        $order = Order::with('payment')->find($this->payload->orderId);

        if (!$order) {
            Log::error('Order not found for refund', ['order_id' => $this->payload->orderId]);
            return;
        }

        if ($order->status !== 'completed') {
            Log::error('Order not completed, cannot refund', [
                'order_id' => $this->payload->orderId,
                'status' => $order->status,
            ]);
            return;
        }

        if ($this->payload->amount > (float) $order->total) {
            Log::error('Refund amount exceeds order total', [
                'order_id' => $this->payload->orderId,
                'refund_amount' => $this->payload->amount,
                'order_total' => $order->total,
            ]);
            return;
        }

        $refund = Refund::create([
            'idempotency_key' => $idempotencyKey,
            'order_id' => $order->id,
            'payment_id' => $order->payment?->id,
            'amount' => number_format($this->payload->amount, 2, '.', ''),
            'status' => 'pending',
            'reason' => $this->payload->reason,
        ]);

        Log::info('Refund queued', [
            'refund_id' => $refund->id,
            'order_id' => $order->id,
            'amount' => $refund->amount,
        ]);

        ApplyRefundJob::dispatch($refund->id);
    }

    public function tags(): array
    {
        return ['refunds', 'queue', 'order:' . $this->payload->orderId];
    }
}
