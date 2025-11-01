<?php

use App\DTOs\RefundRequestPayload;
use App\Jobs\QueueRefundJob;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Refund;

test('refund is idempotent based on order and amount', function () {
    $customer = Customer::factory()->create();
    $product = Product::factory()->create();
    
    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'status' => 'completed',
        'total' => 100.00,
        'completed_at' => now(),
    ]);

    Payment::factory()->create([
        'order_id' => $order->id,
        'status' => 'completed',
        'amount' => 100.00,
    ]);

    $payload = new RefundRequestPayload(
        orderId: $order->id,
        amount: 50.00,
        reason: 'Customer request',
    );

    // First refund
    $job1 = new QueueRefundJob($payload);
    $job1->handle();

    $refund1 = Refund::where('order_id', $order->id)->first();
    expect($refund1)->not->toBeNull();

    // Second refund with same data
    $job2 = new QueueRefundJob($payload);
    $job2->handle();

    $refundCount = Refund::where('order_id', $order->id)->count();
    expect($refundCount)->toBe(1);
});

test('refund validates order status', function () {
    $customer = Customer::factory()->create();
    
    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'status' => 'pending', // Not completed
        'total' => 100.00,
    ]);

    $payload = new RefundRequestPayload(
        orderId: $order->id,
        amount: 50.00,
    );

    $job = new QueueRefundJob($payload);
    $job->handle();

    $refundCount = Refund::where('order_id', $order->id)->count();
    expect($refundCount)->toBe(0);
});

test('refund validates amount does not exceed order total', function () {
    $customer = Customer::factory()->create();
    
    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'status' => 'completed',
        'total' => 100.00,
        'completed_at' => now(),
    ]);

    Payment::factory()->create([
        'order_id' => $order->id,
        'status' => 'completed',
        'amount' => 100.00,
    ]);

    $payload = new RefundRequestPayload(
        orderId: $order->id,
        amount: 150.00, // Exceeds order total
    );

    $job = new QueueRefundJob($payload);
    $job->handle();

    $refundCount = Refund::where('order_id', $order->id)->count();
    expect($refundCount)->toBe(0);
});
