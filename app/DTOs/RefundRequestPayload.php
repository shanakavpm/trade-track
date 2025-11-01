<?php

namespace App\DTOs;

class RefundRequestPayload
{
    public function __construct(
        public readonly int $orderId,
        public readonly float $amount,
        public readonly ?string $reason = null,
    ) {}

    public function toArray(): array
    {
        return [
            'order_id' => $this->orderId,
            'amount' => number_format($this->amount, 2, '.', ''),
            'reason' => $this->reason,
        ];
    }

    public function getIdempotencyKey(): string
    {
        return hash('sha256', $this->orderId . ':' . number_format($this->amount, 2, '.', ''));
    }
}
