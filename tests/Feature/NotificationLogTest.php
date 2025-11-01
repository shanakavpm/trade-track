<?php

use App\DTOs\OrderProcessedPayload;
use App\Jobs\SendOrderNotificationJob;
use App\Models\Customer;
use App\Models\NotificationLog;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
});

test('notification is logged when sent', function () {
    $customer = Customer::factory()->create([
        'email' => 'test@example.com',
    ]);

    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'status' => 'completed',
        'total' => 100.00,
    ]);

    $payload = new OrderProcessedPayload(
        orderId: $order->id,
        customerId: $customer->id,
        status: 'completed',
        total: 100.00,
    );

    $job = new SendOrderNotificationJob($payload);
    $job->handle();

    $log = NotificationLog::where('order_id', $order->id)->first();

    expect($log)->not->toBeNull()
        ->and($log->customer_id)->toBe($customer->id)
        ->and($log->status)->toBe('completed')
        ->and($log->total)->toBe('100.00')
        ->and($log->type)->toBe('email')
        ->and($log->sent_at)->not->toBeNull();
});

test('notification log contains correct payload', function () {
    $customer = Customer::factory()->create([
        'email' => 'test@example.com',
    ]);

    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'status' => 'completed',
        'total' => 150.50,
    ]);

    $payload = new OrderProcessedPayload(
        orderId: $order->id,
        customerId: $customer->id,
        status: 'completed',
        total: 150.50,
    );

    $job = new SendOrderNotificationJob($payload);
    $job->handle();

    $log = NotificationLog::where('order_id', $order->id)->first();

    expect($log->payload)->toBeArray()
        ->and($log->payload['order_id'])->toBe($order->id)
        ->and($log->payload['customer_id'])->toBe($customer->id)
        ->and($log->payload['status'])->toBe('completed')
        ->and($log->payload['total'])->toBe('150.50');
});
