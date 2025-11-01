<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Jobs\FinalizeOrRollbackJob;
use App\Models\Payment;
use App\Services\Payment\MockPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MockCallbackController extends Controller
{
    public function __construct(
        private MockPaymentService $paymentService,
    ) {}

    public function __invoke(Request $request, Payment $payment): JsonResponse
    {
        // Verify signature
        $signature = $request->query('signature');
        
        if (!$signature || !$this->paymentService->verifySignature($payment, $signature)) {
            Log::error('Invalid payment callback signature', [
                'payment_id' => $payment->id,
            ]);
            
            return response()->json([
                'error' => 'Invalid signature',
            ], 403);
        }

        // Check if callback has expired
        if ($payment->callback_expires_at && $payment->callback_expires_at->isPast()) {
            Log::error('Payment callback expired', [
                'payment_id' => $payment->id,
                'expired_at' => $payment->callback_expires_at,
            ]);
            
            return response()->json([
                'error' => 'Callback expired',
            ], 403);
        }

        // Check if already processed
        if ($payment->status !== 'pending') {
            Log::info('Payment already processed', [
                'payment_id' => $payment->id,
                'status' => $payment->status,
            ]);
            
            return response()->json([
                'message' => 'Payment already processed',
                'status' => $payment->status,
            ]);
        }

        // Simulate payment result (80% success rate)
        $success = $request->input('success', rand(1, 100) <= 80);

        $payment->update([
            'status' => $success ? 'completed' : 'failed',
        ]);

        Log::info('Payment callback processed', [
            'payment_id' => $payment->id,
            'order_id' => $payment->order_id,
            'success' => $success,
        ]);

        // Finalize or rollback order
        FinalizeOrRollbackJob::dispatch($payment->order_id, $success);

        return response()->json([
            'message' => 'Payment processed',
            'status' => $payment->status,
            'order_id' => $payment->order_id,
        ]);
    }
}
