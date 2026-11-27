@component('mail::message')
# Thank You for Your Order! 🎉

Dear {{ $order->customer_name }},

Your order **#{{ $order->order_number }}** has been confirmed and is currently **{{ $order->status }}**.

@component('mail::panel')
**Order Number:** #{{ $order->order_number }}<br>
**Order Status:** {{ $order->status }}<br>
**Payment Method:** {{ $order->payment_method }} ({{ $order->payment_status }})<br>
**Total Amount:** Rs. {{ number_format((float) $order->total_amount, 2) }}<br>
@if($order->courier_name && $order->tracking_number)
**Courier & Tracking:** {{ $order->courier_name }} — `{{ $order->tracking_number }}`<br>
@endif
**Shipping To:** {{ $order->shipping_address_line_1 }}@if($order->shipping_address_line_2), {{ $order->shipping_address_line_2 }}@endif, {{ $order->shipping_city }}
@endcomponent

### Order Items
@foreach($order->items as $item)
- **{{ $item->product_name }}** × {{ $item->quantity }} — Rs. {{ number_format((float) $item->total_price, 2) }}
@endforeach

You can track your order status, **view or download your PDF receipt**, or **submit a refund request** anytime using the button below:

@component('mail::button', ['url' => $viewOrderUrl])
View My Order & Track Status
@endcomponent

**Quick Receipt Links:**<br>
- [View Receipt Online]({{ $viewReceiptUrl }})
- [Download Receipt (PDF)]({{ $downloadReceiptUrl }})

*(Keep this email safe — your private order link above gives you direct access to your order without needing to log in.)*

Best regards,<br>
{{ config('app.name') }} Support Team
@endcomponent
