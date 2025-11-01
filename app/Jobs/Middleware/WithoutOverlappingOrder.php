<?php

namespace App\Jobs\Middleware;

use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Facades\Log;

class WithoutOverlappingOrder
{
    public function __construct(
        private int $orderId,
        private int $releaseAfter = 60,
    ) {}

    public function handle(object $job, callable $next): void
    {
        $cache = app(Cache::class);
        $key = 'order-lock:' . $this->orderId;

        if ($cache->add($key, true, $this->releaseAfter)) {
            try {
                $next($job);
            } finally {
                $cache->forget($key);
            }
        } else {
            Log::warning('Order job already processing', [
                'order_id' => $this->orderId,
                'job' => get_class($job),
            ]);
            $job->release($this->releaseAfter);
        }
    }
}
