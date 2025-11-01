<?php

namespace App\Providers;

use App\Events\OrderProcessed;
use App\Events\RefundProcessed;
use App\Listeners\QueueOrderNotification;
use App\Listeners\UpdateKpisAndLeaderboard;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        OrderProcessed::class => [
            QueueOrderNotification::class,
            UpdateKpisAndLeaderboard::class,
        ],
        RefundProcessed::class => [
            UpdateKpisAndLeaderboard::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}
