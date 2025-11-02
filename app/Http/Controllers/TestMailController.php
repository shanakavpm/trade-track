<?php

namespace App\Http\Controllers;

use App\DTOs\OrderProcessedPayload;
use App\Mail\OrderStatusMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class TestMailController extends Controller
{
    public function sendTestEmail()
    {
        try {
            // Create a simple payload matching the DTO constructor
            $payload = new OrderProcessedPayload(
                orderId: 123,        // int
                customerId: 456,     // int
                status: 'completed',  // string
                total: 99.99         // float
            );

            Mail::to('shanakavpm@gmail.com')
                ->send(new OrderStatusMail($payload));

            return response()->json(['message' => 'Test email sent successfully!']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send email',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }
}
