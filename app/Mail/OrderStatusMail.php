<?php

namespace App\Mail;

use App\DTOs\OrderProcessedPayload;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public OrderProcessedPayload $payload,
    ) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->payload->status) {
            'completed' => 'Order Completed Successfully',
            'failed' => 'Order Processing Failed',
            'cancelled' => 'Order Cancelled',
            default => 'Order Status Update',
        };

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.order_status',
            with: [
                'orderId' => $this->payload->orderId,
                'customerId' => $this->payload->customerId,
                'status' => $this->payload->status,
                'total' => number_format($this->payload->total, 2),
            ],
        );
    }
}
