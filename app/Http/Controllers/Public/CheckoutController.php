<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    /**
     * Render the Checkout page with pre-filled profile data if available.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $address = null;

        if ($user) {
            $address = $user->addresses()->where('is_default', true)->first()
                ?? $user->addresses()->first();
        }

        $fullName = '';
        if ($user) {
            $fullName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
            if (empty($fullName)) {
                $fullName = $user->name ?? '';
            }
        }

        $prefillData = [
            'name' => $fullName,
            'email' => $user?->email ?? '',
            'phone' => $user?->phone ?? $address?->phone ?? '',
            'secondary_phone' => $user?->secondary_phone ?? $address?->secondary_phone ?? '',
            'address_line_1' => $address?->address_line_1 ?? '',
            'address_line_2' => $address?->address_line_2 ?? '',
            'city' => $address?->city ?? '',
            'state' => $address?->state ?? '',
            'postal_code' => $address?->postal_code ?? '',
            'country' => $address?->country ?? 'Sri Lanka',
        ];

        return Inertia::render('Checkout/Index', [
            'prefill' => $prefillData,
            'stripeKey' => config('services.stripe.key'),
        ]);
    }

    /**
     * Create a Stripe PaymentIntent for the total order amount.
     */
    public function createPaymentIntent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'email' => ['nullable', 'email'],
            'name' => ['nullable', 'string'],
        ]);

        $amountInLkr = (float) $validated['amount'];
        // Smallest currency unit for LKR is cents (1 LKR = 100 cents)
        $amountInCents = (int) round($amountInLkr * 100);

        $secretKey = config('services.stripe.secret');

        try {
            $response = Http::withoutVerifying()
                ->withBasicAuth($secretKey, '')
                ->asForm()
                ->post('https://api.stripe.com/v1/payment_intents', [
                    'amount' => $amountInCents,
                    'currency' => 'lkr',
                    'payment_method_types' => ['card'],
                    'receipt_email' => $validated['email'] ?? null,
                    'description' => "ComLink Store Order payment - " . ($validated['name'] ?? 'Customer'),
                ]);

            if ($response->failed()) {
                Log::error('Stripe PaymentIntent Creation Failed', ['body' => $response->body()]);
                return response()->json([
                    'error' => $response->json('error.message') ?? 'Failed to initialize payment gateway.',
                ], 422);
            }

            return response()->json([
                'clientSecret' => $response->json('client_secret'),
                'paymentIntentId' => $response->json('id'),
            ]);
        } catch (\Throwable $e) {
            Log::error('Stripe Connection Exception', ['message' => $e->getMessage()]);
            return response()->json([
                'error' => 'Unable to communicate with payment processor: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process and store the completed order in Processing status.
     */
    public function processOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'customer_secondary_phone' => ['nullable', 'string', 'max:50'],
            'shipping_address_line_1' => ['required', 'string', 'max:255'],
            'shipping_address_line_2' => ['nullable', 'string', 'max:255'],
            'shipping_city' => ['required', 'string', 'max:100'],
            'shipping_state' => ['nullable', 'string', 'max:100'],
            'shipping_postal_code' => ['nullable', 'string', 'max:20'],
            'shipping_country' => ['nullable', 'string', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.productId' => ['nullable'],
            'items.*.name' => ['required', 'string'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.image' => ['nullable', 'string'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'shipping_fee' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'payment_intent_id' => ['nullable', 'string'],
        ]);

        $paymentIntentId = $validated['payment_intent_id'] ?? null;
        $paymentStatus = 'Paid';

        // Optional verify with Stripe if payment_intent_id is provided
        if (! empty($paymentIntentId)) {
            $secretKey = config('services.stripe.secret');
            try {
                $stripeRes = Http::withoutVerifying()
                    ->withBasicAuth($secretKey, '')
                    ->get("https://api.stripe.com/v1/payment_intents/{$paymentIntentId}");

                if ($stripeRes->successful()) {
                    $status = $stripeRes->json('status');
                    if (! in_array($status, ['succeeded', 'processing', 'requires_capture'])) {
                        Log::warning('Stripe PaymentIntent status not succeeded', ['status' => $status]);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Stripe Intent verification notice', ['message' => $e->getMessage()]);
            }
        }

        $order = DB::transaction(function () use ($validated, $request, $paymentStatus, $paymentIntentId) {
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $request->user()?->id,
                'status' => 'Processing',
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],
                'customer_secondary_phone' => $validated['customer_secondary_phone'] ?? null,
                'shipping_address_line_1' => $validated['shipping_address_line_1'],
                'shipping_address_line_2' => $validated['shipping_address_line_2'] ?? null,
                'shipping_city' => $validated['shipping_city'],
                'shipping_state' => $validated['shipping_state'] ?? null,
                'shipping_postal_code' => $validated['shipping_postal_code'] ?? null,
                'shipping_country' => $validated['shipping_country'] ?? 'Sri Lanka',
                'subtotal' => $validated['subtotal'],
                'shipping_fee' => $validated['shipping_fee'] ?? 0.00,
                'discount_amount' => $validated['discount_amount'] ?? 0.00,
                'total_amount' => $validated['total_amount'],
                'payment_method' => 'Stripe Card Payment',
                'payment_status' => $paymentStatus,
                'stripe_payment_intent_id' => $paymentIntentId,
                'courier_name' => 'Pronto Courier Service',
                'tracking_number' => 'PRN-' . strtoupper(substr(uniqid(), -6)),
            ]);

            foreach ($validated['items'] as $item) {
                $qty = (int) $item['quantity'];
                $price = (float) $item['price'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => is_numeric($item['productId'] ?? null) ? (int) $item['productId'] : null,
                    'product_name' => $item['name'],
                    'product_image' => $item['image'] ?? '/images/placeholder.png',
                    'category_name' => $item['category'] ?? 'Hardware',
                    'unit_price' => $price,
                    'quantity' => $qty,
                    'total_price' => round($price * $qty, 2),
                ]);
            }

            // Save address to user profile if user has none saved
            $user = $request->user();
            if ($user && ! $user->addresses()->exists()) {
                $user->addresses()->create([
                    'type' => 'shipping',
                    'is_default' => true,
                    'address_line_1' => $validated['shipping_address_line_1'],
                    'address_line_2' => $validated['shipping_address_line_2'] ?? null,
                    'city' => $validated['shipping_city'],
                    'state' => $validated['shipping_state'] ?? null,
                    'postal_code' => $validated['shipping_postal_code'] ?? null,
                    'country' => $validated['shipping_country'] ?? 'Sri Lanka',
                    'phone' => $validated['customer_phone'],
                    'secondary_phone' => $validated['customer_secondary_phone'] ?? null,
                ]);
            }

            return $order;
        });

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'redirect_url' => route('orders.index'),
        ]);
    }
}
