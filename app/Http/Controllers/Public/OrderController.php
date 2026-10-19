<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    /**
     * Display the user's orders list from the database.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $query = Order::query()->with('items');

        if ($user) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('customer_email', $user->email);
            });
        } else {
            // Guest or non-logged-in
            $query->whereRaw('1 = 0');
        }

        $dbOrders = $query->latest('id')->get();

        $orders = $dbOrders->map(function (Order $o) {
            return [
                'id' => (string) $o->id,
                'orderNumber' => $o->order_number,
                'createdAt' => $o->created_at ? $o->created_at->format('M d, Y \a\t h:i A') : '',
                'status' => $o->status ?? 'Processing',
                'statusLabel' => match ($o->status) {
                    'Processing' => 'Payment Confirmed & Order Verification in Progress',
                    'Packaging' => 'Items Picked, Quality Inspected & Securely Packaged',
                    'Shipping' => 'Dispatched with Courier - On the Way',
                    'Delivered' => 'Package Successfully Delivered to Customer',
                    default => 'Order in Progress',
                },
                'estimatedDelivery' => 'Estimated 2 - 4 business days',
                'paymentMethod' => $o->payment_method ?: 'Stripe Card Payment',
                'paymentStatus' => $o->payment_status ?: 'Paid',
                'subtotal' => (float) $o->subtotal,
                'shippingFee' => (float) $o->shipping_fee,
                'discount' => (float) $o->discount_amount,
                'total' => (float) $o->total_amount,
                'shippingAddress' => [
                    'recipient' => $o->customer_name,
                    'address' => trim($o->shipping_address_line_1 . ($o->shipping_address_line_2 ? ', ' . $o->shipping_address_line_2 : '')),
                    'city' => trim($o->shipping_city . ($o->shipping_postal_code ? ', ' . $o->shipping_postal_code : '')),
                    'phone' => $o->customer_phone . ($o->customer_secondary_phone ? " / {$o->customer_secondary_phone}" : ''),
                ],
                'courier' => [
                    'name' => $o->courier_name ?: 'Pronto Courier Service',
                    'trackingNumber' => $o->tracking_number ?: ('PRN-LK-' . $o->id),
                ],
                'items' => $o->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'productId' => (string) ($item->product_id ?? $item->id),
                        'name' => $item->product_name,
                        'image' => $item->product_image ?: '/images/placeholder.png',
                        'category' => $item->category_name ?: 'Hardware',
                        'quantity' => (int) $item->quantity,
                        'unitPrice' => (float) $item->unit_price,
                        'totalPrice' => (float) $item->total_price,
                    ];
                })->values()->all(),
            ];
        })->values()->all();

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
        ]);
    }
}
