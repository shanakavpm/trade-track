<?php

use App\DTOs\OrderImportRow;
use App\Jobs\CreateOrderJob;
use App\Jobs\FinalizeOrRollbackJob;
use App\Jobs\ReserveStockJob;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Services\Stock\StockService;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
});

test('order workflow creates order successfully', function () {
    $customer = Customer::factory()->create();
    $product = Product::factory()->create([
        'sku' => 'TEST-001',
        'price' => 100.00,
        'stock_quantity' => 10,
    ]);

    $dto = new OrderImportRow(
        orderId: 'ORD-001',
        customerId: $customer->id,
        sku: 'TEST-001',
        qty: 2,
        unitPrice: 100.00,
    );

    $job = new CreateOrderJob($dto);
    $job->handle();

    $order = Order::where('customer_id', $customer->id)->first();
    
    expect($order)->not->toBeNull()
        ->and($order->total)->toBe('200.00')
        ->and($order->status)->toBe('pending');

    Queue::assertPushed(ReserveStockJob::class);
});

test('order workflow handles stock reservation', function () {
    $customer = Customer::factory()->create();
    $product = Product::factory()->create([
        'sku' => 'TEST-001',
        'stock_quantity' => 10,
    ]);

    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'status' => 'pending',
        'total' => 200.00,
    ]);

    $order->items()->create([
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_price' => 100.00,
        'subtotal' => 200.00,
    ]);

    $stockService = app(StockService::class);
    $job = new ReserveStockJob($order->id);
    $job->handle($stockService);

    $product->refresh();
    $order->refresh();

    expect($product->stock_quantity)->toBe(8)
        ->and($order->status)->toBe('processing');
});

test('order workflow rolls back on failure', function () {
    $customer = Customer::factory()->create();
    $product = Product::factory()->create([
        'stock_quantity' => 10,
    ]);

    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'status' => 'processing',
        'total' => 200.00,
    ]);

    $order->items()->create([
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_price' => 100.00,
        'subtotal' => 200.00,
    ]);

    // Simulate stock reservation
    $product->update(['stock_quantity' => 8]);

    $stockService = app(StockService::class);
    $job = new FinalizeOrRollbackJob($order->id, false);
    $job->handle($stockService);

    $product->refresh();
    $order->refresh();

    expect($product->stock_quantity)->toBe(10)
        ->and($order->status)->toBe('failed');
});
