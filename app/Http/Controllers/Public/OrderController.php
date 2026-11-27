<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\ReturnRefundService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

class OrderController extends Controller
{
    /**
     * Display the logged-in user's orders list from the database.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $query = Order::query()->with(['items.customerReturnItems.customerReturn', 'customerReturns.items']);

        if ($user) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('customer_email', $user->email);
            });
        } else {
            $query->whereRaw('1 = 0');
        }

        $orders = $query->latest('id')
            ->get()
            ->map(fn (Order $o) => $this->formatOrderForView($o))
            ->values()
            ->all();

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
            'guestToken' => null,
        ]);
    }

    /**
     * Display a guest customer's order via their private tokenized email link.
     */
    public function showGuestOrder(string $token): Response
    {
        $order = Order::with(['items.customerReturnItems.customerReturn', 'customerReturns.items'])
            ->where('is_guest', true)
            ->where('guest_token', $token)
            ->firstOrFail();

        return Inertia::render('Orders/Index', [
            'orders' => [$this->formatOrderForView($order)],
            'guestToken' => $token,
        ]);
    }

    /**
     * Download or preview official PDF receipt for a logged-in customer's order.
     */
    public function downloadReceipt(Request $request, $order)
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Please log in to download receipt.');
        }

        $orderModel = Order::with('items')
            ->where(function ($q) use ($order) {
                $q->where('id', $order)
                    ->orWhere('order_number', $order);
            })
            ->firstOrFail();

        $isAdmin = in_array($user->role ?? '', ['admin', 'super-admin']);
        $isOwner = $orderModel->user_id === $user->id
            || strtolower((string) $orderModel->customer_email) === strtolower((string) $user->email);

        if (! $isAdmin && ! $isOwner) {
            abort(403, 'Unauthorized access to this order receipt.');
        }

        return $this->buildReceiptPdf($orderModel, $request);
    }

    /**
     * Download or preview official PDF receipt for a guest order via token.
     */
    public function downloadGuestReceipt(Request $request, string $token)
    {
        $orderModel = Order::with('items')
            ->where('is_guest', true)
            ->where('guest_token', $token)
            ->firstOrFail();

        return $this->buildReceiptPdf($orderModel, $request);
    }

    /**
     * Handle logged-in customer refund request submission from /orders.
     */
    public function submitRefundRequest(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Please log in to submit a refund request.'], 401);
        }

        $isOwner = (int) $order->user_id === (int) $user->id
            || ($order->customer_email && strtolower((string) $order->customer_email) === strtolower((string) $user->email));

        if (! $isOwner) {
            return response()->json(['message' => 'Unauthorized access to this order.'], 403);
        }

        return $this->handleRefundSubmission($request, $order);
    }

    /**
     * Handle guest customer refund request submission via private token.
     */
    public function submitGuestRefundRequest(Request $request, string $token): JsonResponse
    {
        $order = Order::where('is_guest', true)
            ->where('guest_token', $token)
            ->firstOrFail();

        return $this->handleRefundSubmission($request, $order);
    }

    /**
     * Format an Order model into the frontend Orders/Index payload.
     */
    private function formatOrderForView(Order $o): array
    {
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
            'refundStatus' => $o->refund_status ?? 'none',
            'refundedAmount' => (float) ($o->refunded_amount ?? 0),
            'canRequestRefund' => $o->canAcceptRefundRequest(),
            'refundDeadline' => $o->refund_deadline?->format('M d, Y h:i A'),
            'refundRequests' => $o->customerReturns->sortByDesc('id')->map(function ($ret) {
                return [
                    'id' => $ret->id,
                    'returnNumber' => $ret->return_number,
                    'status' => $ret->status,
                    'refundAmount' => (float) $ret->refund_amount,
                    'reason' => $ret->reason,
                    'rejectionReason' => $ret->rejection_reason,
                    'bankName' => $ret->bank_name,
                    'bankBranch' => $ret->bank_branch,
                    'accountNumber' => $ret->account_number,
                    'accountName' => $ret->account_name,
                    'createdAt' => $ret->created_at ? $ret->created_at->format('M d, Y h:i A') : '',
                    'refundedAt' => $ret->refunded_at ? $ret->refunded_at->format('M d, Y h:i A') : null,
                    'paymentReceiptUrl' => $ret->payment_receipt_url,
                    'evidenceImageUrl' => $ret->evidence_image_url,
                    'items' => $ret->items->map(fn ($ri) => [
                        'productName' => $ri->product_name,
                        'quantity' => (int) $ri->quantity,
                        'refundSubtotal' => (float) $ri->refund_subtotal,
                        'condition' => $ri->condition,
                    ])->values()->all(),
                ];
            })->values()->all(),
            'subtotal' => (float) $o->subtotal,
            'shippingFee' => (float) $o->shipping_fee,
            'discount' => (float) $o->discount_amount,
            'total' => (float) $o->total_amount,
            'shippingAddress' => [
                'recipient' => $o->customer_name,
                'address' => trim($o->shipping_address_line_1.($o->shipping_address_line_2 ? ', '.$o->shipping_address_line_2 : '')),
                'city' => trim($o->shipping_city.($o->shipping_postal_code ? ', '.$o->shipping_postal_code : '')),
                'phone' => $o->customer_phone.($o->customer_secondary_phone ? " / {$o->customer_secondary_phone}" : ''),
            ],
            'courier' => [
                'name' => $o->courier_name ?: 'Pronto Courier Service',
                'trackingNumber' => $o->tracking_number ?: ('PRN-LK-'.$o->id),
            ],
            'items' => $o->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'productId' => (string) ($item->product_id ?? $item->id),
                    'name' => $item->product_name,
                    'image' => $item->product_image ?: '/images/placeholder.png',
                    'category' => $item->category_name ?: ($item->product?->categories->firstWhere('parent_id', null)?->name ?: 'Accessories'),
                    'quantity' => (int) $item->quantity,
                    'returnableQuantity' => (int) $item->returnable_quantity,
                    'unitPrice' => (float) $item->unit_price,
                    'totalPrice' => (float) $item->total_price,
                ];
            })->values()->all(),
        ];
    }

    /**
     * Generate and stream/download the official PDF receipt.
     */
    private function buildReceiptPdf(Order $orderModel, Request $request)
    {
        $pdf = Pdf::loadView('receipts.order-receipt', [
            'order' => $orderModel,
            'currency' => 'Rs. ',
        ])
            ->setPaper('a4', 'portrait')
            ->setOption('isRemoteEnabled', true)
            ->setOption('dpi', 150);

        $filename = "Receipt-{$orderModel->order_number}.pdf";

        if ($request->query('preview')) {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename);
    }

    /**
     * Validate and process a customer/guest refund request submission.
     */
    private function handleRefundSubmission(Request $request, Order $order): JsonResponse
    {
        if ($order->payment_status !== 'Paid') {
            return response()->json(['message' => 'Only paid orders are eligible for a refund request.'], 422);
        }

        if ($order->status !== 'Delivered') {
            return response()->json(['message' => 'Refund requests can only be submitted after the order has been marked as Delivered.'], 422);
        }

        if (! $order->isWithinRefundWindow()) {
            return response()->json(['message' => 'The 7-day refund request window for this order has expired.'], 422);
        }

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.order_item_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:0'],
            'reason' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'bank_name' => ['required', 'string', 'max:120'],
            'bank_branch' => ['required', 'string', 'max:120'],
            'account_number' => ['required', 'string', 'max:60'],
            'account_name' => ['required', 'string', 'max:120'],
            'payment_receipt' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
            'evidence_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
        ], [
            'payment_receipt.required' => 'Please upload your payment receipt image.',
            'payment_receipt.max' => 'Payment receipt image must be smaller than 1 MB.',
            'payment_receipt.image' => 'Payment receipt must be a valid image (JPG, PNG, WEBP).',
            'evidence_image.required' => 'Please upload an evidence image of the product for return.',
            'evidence_image.max' => 'Evidence image must be smaller than 1 MB.',
            'evidence_image.image' => 'Evidence image must be a valid image (JPG, PNG, WEBP).',
        ]);

        $selectedItems = collect($validated['items'])
            ->filter(fn ($row) => (int) ($row['quantity'] ?? 0) > 0)
            ->map(fn ($row) => [
                'order_item_id' => (int) $row['order_item_id'],
                'quantity' => (int) $row['quantity'],
                'condition' => 'damaged',
                'notes' => $validated['notes'] ?? null,
            ])
            ->values()
            ->all();

        if (empty($selectedItems)) {
            return response()->json(['message' => 'Please select at least one item with a quantity greater than 0.'], 422);
        }

        $receiptPath = null;
        $evidencePath = null;

        try {
            $receiptPath = $request->file('payment_receipt')->store('refunds/receipts', 'public');
            $evidencePath = $request->file('evidence_image')->store('refunds/evidence', 'public');

            $customerReturn = app(ReturnRefundService::class)->processCustomerReturn(
                $order,
                $selectedItems,
                [
                    'status' => 'pending',
                    'requested_by_customer' => true,
                    'refund_method' => 'bank_transfer',
                    'reason' => $validated['reason'],
                    'admin_notes' => $validated['notes'] ?? null,
                    'payment_receipt_path' => $receiptPath,
                    'evidence_image_path' => $evidencePath,
                    'bank_name' => $validated['bank_name'],
                    'bank_branch' => $validated['bank_branch'],
                    'account_number' => $validated['account_number'],
                    'account_name' => $validated['account_name'],
                ]
            );

            return response()->json([
                'message' => 'Your refund request has been submitted and is pending admin review.',
                'return_number' => $customerReturn->return_number,
            ], 201);
        } catch (InvalidArgumentException $e) {
            if ($receiptPath) {
                Storage::disk('public')->delete($receiptPath);
            }
            if ($evidencePath) {
                Storage::disk('public')->delete($evidencePath);
            }

            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            if ($receiptPath) {
                Storage::disk('public')->delete($receiptPath);
            }
            if ($evidencePath) {
                Storage::disk('public')->delete($evidencePath);
            }
            report($e);

            return response()->json(['message' => 'Could not submit refund request. Please check your inputs and try again.'], 422);
        }
    }
}
