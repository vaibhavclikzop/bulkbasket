<!DOCTYPE html>
<html>

<head>
    <title>Order Status Update</title>
</head>

<body>
    <h2>Dear Customer,</h2>
    <p>Your {{ $orderType  }} (ID: {{ $order->id }}) status has been updated.</p>
    <p><strong>Order Status:</strong> {{ ucfirst($order->order_status) }}</p>
    <p><strong>Total Amount:</strong> ₹{{ number_format($order->total_amount, 2) }}</p>

    <p>Thank you for shopping with us! BulkBasketIndia</p>
</body>

</html>
