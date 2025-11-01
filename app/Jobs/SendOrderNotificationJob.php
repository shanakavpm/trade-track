<?php

namespace App\Jobs;

use App\DTOs\OrderProcessedPayload;
use App\Mail\OrderStatusMail;
use App\Models\Customer;
use App\Models\NotificationLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [10, 30, 60];

    public function __construct(
        public OrderProcessedPayload $payload,
    ) {
        $this->onQueue('notifications');
    }

    public function handle(): void
    {
        $customer = Customer::find($this->payload->customerId);

        if (!$customer) {
            Log::error('Customer not found for notification', [
                'customer_id' => $this->payload->customerId,
            ]);
            return;
        }

        try {
            Mail::to($customer->email)->send(new OrderStatusMail($this->payload));

            NotificationLog::create([
                'order_id' => $this->payload->orderId,
                'customer_id' => $this->payload->customerId,
                'type' => 'email',
                'status' => $this->payload->status,
                'total' => number_format($this->payload->total, 2, '.', ''),
                'payload' => $this->payload->toArray(),
                'sent_at' => now(),
            ]);

            Log::info('Order notification sent', [
                'order_id' => $this->payload->orderId,
                'customer_id' => $this->payload->customerId,
                'email' => $customer->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send order notification', [
                'order_id' => $this->payload->orderId,
                'error' => $e->getMessage(),
            ]);

            NotificationLog::create([
                'order_id' => $this->payload->orderId,
                'customer_id' => $this->payload->customerId,
                'type' => 'log',
                'status' => $this->payload->status,
                'total' => number_format($this->payload->total, 2, '.', ''),
                'payload' => array_merge($this->payload->toArray(), ['error' => $e->getMessage()]),
                'sent_at' => now(),
            ]);

            throw $e;
        }
    }

    public function tags(): array
    {
        return ['notifications', 'order:' . $this->payload->orderId];
    }
}
