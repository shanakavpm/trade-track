<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\URL;

class MockPaymentService
{
    public function createPendingPayment(Order $order): Payment
    {
        $payment = Payment::create([
            'order_id' => $order->id,
            'status' => 'pending',
            'amount' => $order->total,
            'transaction_id' => 'TXN-' . strtoupper(uniqid()),
            'callback_expires_at' => now()->addMinutes(15),
        ]);

        return $payment;
    }

    public function generateSignedCallbackUrl(Payment $payment): string
    {
        $signature = $this->generateSignature($payment);
        $payment->update(['callback_signature' => $signature]);

        return URL::temporarySignedRoute(
            'payments.mock.callback',
            now()->addMinutes(15),
            [
                'payment' => $payment->id,
                'signature' => $signature,
            ]
        );
    }

    public function verifySignature(Payment $payment, string $signature): bool
    {
        return hash_equals($payment->callback_signature ?? '', $signature);
    }

    private function generateSignature(Payment $payment): string
    {
        $data = $payment->id . ':' . $payment->transaction_id . ':' . $payment->amount;
        return hash_hmac('sha256', $data, config('app.key'));
    }
}
