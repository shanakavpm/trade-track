<?php

namespace App\Jobs;

use App\Jobs\Middleware\WithoutOverlappingOrder;
use App\Models\Order;
use App\Services\Payment\MockPaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SimulatePaymentJob implements ShouldQueue
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

    public function handle(MockPaymentService $paymentService): void
    {
        $order = Order::find($this->orderId);

        if (!$order) {
            Log::error('Order not found for payment', ['order_id' => $this->orderId]);
            return;
        }

        if ($order->status !== 'processing') {
            Log::info('Order not in processing status, skipping payment', [
                'order_id' => $this->orderId,
                'status' => $order->status,
            ]);
            return;
        }

        $payment = $paymentService->createPendingPayment($order);
        $callbackUrl = $paymentService->generateSignedCallbackUrl($payment);

        Log::info('Payment initiated', [
            'order_id' => $this->orderId,
            'payment_id' => $payment->id,
            'callback_url' => $callbackUrl,
        ]);

        // For simulation purposes, auto-complete the payment
        // In production, this would be triggered by payment gateway callback
        $success = rand(1, 100) <= 90; // 90% success rate
        
        $payment->update([
            'status' => $success ? 'completed' : 'failed',
        ]);

        Log::info('Payment ' . ($success ? 'completed' : 'failed'), [
            'order_id' => $this->orderId,
            'payment_id' => $payment->id,
        ]);

        // Dispatch finalize job
        FinalizeOrRollbackJob::dispatch($this->orderId, $success);
    }

    public function tags(): array
    {
        return ['orders', 'payment', 'order:' . $this->orderId];
    }
}
