<?php

namespace Tests\Feature\Mail;

use App\DTOs\OrderProcessedPayload;
use App\Mail\OrderStatusMail;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderStatusMailTest extends TestCase
{
    public function test_order_status_mail_can_be_sent()
    {
        // Fake the Mail facade to prevent actual sending
        Mail::fake();

        // Create a test payload
        $payload = new OrderProcessedPayload(
            orderId: '123',
            customerId: '456',
            status: 'completed',
            total: 99.99
        );

        // Send the mail
         Mail::to('shanakavpm@gmail.com')->send(new OrderStatusMail($payload));

        // Assert the mail was sent
        Mail::assertSent(OrderStatusMail::class, function ($mail) use ($payload) {
            return $mail->hasTo('shanakavpm@gmail.com') &&
                   $mail->payload === $payload;
        });
    }

    public function test_order_status_mail_has_correct_subject()
    {
        $testCases = [
            ['status' => 'completed', 'expected' => 'Order Completed Successfully'],
            ['status' => 'failed', 'expected' => 'Order Processing Failed'],
            ['status' => 'cancelled', 'expected' => 'Order Cancelled'],
            ['status' => 'processing', 'expected' => 'Order Status Update'],
        ];

        foreach ($testCases as $case) {
            $payload = new OrderProcessedPayload(
                orderId: '123',
                customerId: '456',
                status: $case['status'],
                total: 99.99
            );

            $mail = new OrderStatusMail($payload);
            $this->assertEquals($case['expected'], $mail->envelope()->subject);
        }
    }

    public function test_order_status_mail_has_correct_content()
    {
        $payload = new OrderProcessedPayload(
            orderId: '123',
            customerId: '456',
            status: 'completed',
            total: 99.99
        );

        $mail = new OrderStatusMail($payload);
        $content = $mail->render();

        // Assert the content contains the order ID and total
        $this->assertStringContainsString('123', $content);
        $this->assertStringContainsString('456', $content);
        $this->assertStringContainsString('99.99', $content);
        $this->assertStringContainsString('completed', $content);
    }
}
