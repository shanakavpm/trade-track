<?php

namespace App\Jobs;

use App\DTOs\OrderImportRow;
use App\Models\User;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ValidateRowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [10, 30, 60];

    public function __construct(
        public array $rowData,
        public int $rowNumber,
    ) {
        $this->onQueue('import');
    }

    public function handle(): void
    {
        $validator = Validator::make($this->rowData, [
            'order_id' => 'required|string|max:255',
            'customer_id' => 'required|integer|exists:users,id',
            'sku' => 'required|string|exists:products,sku',
            'qty' => 'required|integer|min:1|max:10000',
            'unit_price' => 'required|numeric|min:0|max:999999.99',
        ]);

        if ($validator->fails()) {
            Log::error('CSV row validation failed', [
                'row' => $this->rowNumber,
                'data' => $this->rowData,
                'errors' => $validator->errors()->toArray(),
            ]);
            return;
        }

        $validated = $validator->validated();

        $dto = new OrderImportRow(
            orderId: $validated['order_id'],
            customerId: $validated['customer_id'],
            sku: $validated['sku'],
            qty: $validated['qty'],
            unitPrice: (float) $validated['unit_price'],
        );

        CreateOrderJob::dispatch($dto);
    }

    public function tags(): array
    {
        return ['import', 'validate', 'row:' . $this->rowNumber];
    }
}
