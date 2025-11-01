<?php

namespace App\DTOs;

class OrderImportRow
{
    public function __construct(
        public readonly string $orderId,
        public readonly int $customerId,
        public readonly string $sku,
        public readonly int $qty,
        public readonly float $unitPrice,
    ) {}

    public function toArray(): array
    {
        return [
            'order_id' => $this->orderId,
            'customer_id' => $this->customerId,
            'sku' => $this->sku,
            'qty' => $this->qty,
            'unit_price' => number_format($this->unitPrice, 2, '.', ''),
        ];
    }

    public function getIdempotencyKey(): string
    {
        return hash('sha256', json_encode($this->toArray()));
    }
}
