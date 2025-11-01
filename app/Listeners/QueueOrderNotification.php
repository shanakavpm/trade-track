<?php

namespace App\Listeners;

use App\Events\OrderProcessed;
use App\Jobs\SendOrderNotificationJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueueOrderNotification implements ShouldQueue
{
    public function handle(OrderProcessed $event): void
    {
        SendOrderNotificationJob::dispatch($event->payload);
    }
}
