<?php

namespace App\Jobs;

use App\Events\RefundProcessed;
use App\Jobs\Middleware\WithoutOverlappingOrder;
use App\Models\Refund;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ApplyRefundJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [10, 30, 60];

    public function __construct(
        public int $refundId,
    ) {
        $this->onQueue('refunds');
    }

    public function middleware(): array
    {
        $refund = Refund::find($this->refundId);
        return $refund ? [new WithoutOverlappingOrder($refund->order_id)] : [];
    }

    public function handle(): void
    {
        $refund = Refund::with('order')->find($this->refundId);

        if (!$refund) {
            Log::error('Refund not found', ['refund_id' => $this->refundId]);
            return;
        }

        if ($refund->status !== 'pending') {
            Log::info('Refund already processed', [
                'refund_id' => $this->refundId,
                'status' => $refund->status,
            ]);
            return;
        }

        // Simulate refund processing
        $refund->update([
            'status' => 'completed',
            'processed_at' => now(),
        ]);

        Log::info('Refund processed', [
            'refund_id' => $refund->id,
            'order_id' => $refund->order_id,
            'amount' => $refund->amount,
        ]);

        // Fire event for KPI updates
        event(new RefundProcessed($refund));
    }

    public function tags(): array
    {
        return ['refunds', 'apply', 'refund:' . $this->refundId];
    }
}
