<?php

use App\Jobs\ValidateRowJob;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    Queue::fake();
});

test('orders import command validates CSV headers', function () {
    $csv = "invalid,headers,here\n1,2,3";
    Storage::put('imports/orders.csv', $csv);

    $this->artisan('orders:import', ['file' => 'imports/orders.csv'])
        ->expectsOutput('Invalid CSV headers. Expected: order_id, customer_id, sku, qty, unit_price')
        ->assertExitCode(1);
});

test('orders import command dispatches validation jobs', function () {
    $customer = Customer::factory()->create();
    $product = Product::factory()->create(['sku' => 'TEST-SKU']);

    $csv = "order_id,customer_id,sku,qty,unit_price\n";
    $csv .= "ORD-001,{$customer->id},TEST-SKU,5,99.99\n";
    $csv .= "ORD-002,{$customer->id},TEST-SKU,3,49.99";
    
    Storage::put('imports/orders.csv', $csv);

    $this->artisan('orders:import', ['file' => 'imports/orders.csv'])
        ->assertExitCode(0);

    Queue::assertPushed(ValidateRowJob::class, 2);
});

test('orders import handles missing file gracefully', function () {
    $this->artisan('orders:import', ['file' => 'nonexistent.csv'])
        ->expectsOutput('File not found: nonexistent.csv')
        ->assertExitCode(1);
});
