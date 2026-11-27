@component('mail::message')
# Order Status Update 📦

Dear {{ $order->customer_name }},

There is an update on your order **#{{ $order->order_number }}**:

@component('mail::panel')
**Order Number:** #{{ $order->order_number }}<br>
**Previous Status:** {{ $previousStatus }}<br>
**Current Status:** **{{ $newStatus }}**<br>
@if($order->courier_name && $order->tracking_number)
**Courier & Tracking:** {{ $order->courier_name }} — `{{ $order->tracking_number }}`<br>
@endif
**Total Amount:** Rs. {{ number_format((float) $order->total_amount, 2) }}
@endcomponent

@if($newStatus === 'Packaging')
Your items are being picked, quality-inspected, and securely packaged for dispatch.
@elseif($newStatus === 'Shipping')
Your package has been handed over to **{{ $order->courier_name ?? 'our courier partner' }}** and is on its way to you!
@elseif($newStatus === 'Delivered')
Your package has been marked as **Delivered**. We hope you enjoy your new hardware from ComLink!
@else
Your order is currently being processed by our team.
@endif

@component('mail::button', ['url' => $viewOrderUrl])
View My Order & Track Status
@endcomponent

You can also [view your receipt online]({{ $viewReceiptUrl }}) or request a refund from your order page if needed.

Best regards,<br>
{{ config('app.name') }} Support Team
@endcomponent
