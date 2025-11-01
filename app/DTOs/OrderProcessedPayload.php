<?php

namespace App\DTOs;

class OrderProcessedPayload
{
    public function __construct(
        public readonly int $orderId,
        public readonly int $customerId,
        public readonly string $status,
        public readonly float $total,
    ) {}

    public function toArray(): array
    {
        return [
            'order_id' => $this->orderId,
            'customer_id' => $this->customerId,
            'status' => $this->status,
            'total' => number_format($this->total, 2, '.', ''),
        ];
    }
}
