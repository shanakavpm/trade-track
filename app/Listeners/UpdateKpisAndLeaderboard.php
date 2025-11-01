<?php

namespace App\Listeners;

use App\Events\OrderProcessed;
use App\Events\RefundProcessed;
use App\Services\Kpi\KpiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class UpdateKpisAndLeaderboard implements ShouldQueue
{
    public function __construct(
        private KpiService $kpiService,
    ) {}

    public function handle(OrderProcessed|RefundProcessed $event): void
    {
        if ($event instanceof OrderProcessed) {
            $this->handleOrderProcessed($event);
        } elseif ($event instanceof RefundProcessed) {
            $this->handleRefundProcessed($event);
        }
    }

    private function handleOrderProcessed(OrderProcessed $event): void
    {
        if ($event->payload->status !== 'completed') {
            return;
        }

        $date = now()->format('Y-m-d');
        $month = now()->format('Y-m');

        $this->kpiService->incrementRevenue($date, $event->payload->total);
        $this->kpiService->incrementOrderCount($date);
        $this->kpiService->incrementSumTotal($date, $event->payload->total);
        $this->kpiService->addToLeaderboard($month, $event->payload->customerId, $event->payload->total);

        Log::info('KPIs and leaderboard updated for order', [
            'order_id' => $event->payload->orderId,
            'date' => $date,
            'month' => $month,
        ]);
    }

    private function handleRefundProcessed(RefundProcessed $event): void
    {
        $refund = $event->refund->load('order');
        
        if (!$refund->order || !$refund->order->completed_at) {
            return;
        }

        $date = $refund->order->completed_at->format('Y-m-d');
        $month = $refund->order->completed_at->format('Y-m');

        $this->kpiService->decrementRevenue($date, (float) $refund->amount);
        $this->kpiService->decrementSumTotal($date, (float) $refund->amount);
        $this->kpiService->removeFromLeaderboard($month, $refund->order->customer_id, (float) $refund->amount);

        Log::info('KPIs and leaderboard updated for refund', [
            'refund_id' => $refund->id,
            'order_id' => $refund->order_id,
            'date' => $date,
            'month' => $month,
        ]);
    }
}
