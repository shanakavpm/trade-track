<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Status Update</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4F46E5;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f9fafb;
            padding: 30px;
            border: 1px solid #e5e7eb;
            border-radius: 0 0 8px 8px;
        }
        .status {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
        }
        .status-completed {
            background-color: #10b981;
            color: white;
        }
        .status-failed {
            background-color: #ef4444;
            color: white;
        }
        .status-cancelled {
            background-color: #6b7280;
            color: white;
        }
        .details {
            margin: 20px 0;
            background-color: white;
            padding: 20px;
            border-radius: 4px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #6b7280;
        }
        .value {
            color: #111827;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            color: #6b7280;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Order Status Update</h1>
    </div>
    
    <div class="content">
        <p>Hello,</p>
        
        <p>Your order status has been updated.</p>
        
        <div class="details">
            <div class="detail-row">
                <span class="label">Order ID:</span>
                <span class="value">#{{ $orderId }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Customer ID:</span>
                <span class="value">{{ $customerId }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Status:</span>
                <span class="status status-{{ $status }}">{{ $status }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Total Amount:</span>
                <span class="value">${{ $total }}</span>
            </div>
        </div>
        
        @if($status === 'completed')
            <p>Thank you for your order! Your payment has been processed successfully.</p>
        @elseif($status === 'failed')
            <p>Unfortunately, we were unable to process your order. Please contact support for assistance.</p>
        @elseif($status === 'cancelled')
            <p>Your order has been cancelled as requested.</p>
        @endif
    </div>
    
    <div class="footer">
        <p>&copy; {{ date('Y') }} Trade Track. All rights reserved.</p>
    </div>
</body>
</html>
