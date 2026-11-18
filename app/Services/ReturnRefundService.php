<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CustomerReturn;
use App\Models\CustomerReturnItem;
use App\Models\GrnItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SupplierReturn;
use App\Models\SupplierReturnItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ReturnRefundService
{
    /**
     * Resolve the procurement unit cost of an OrderItem for accurate COGS & damaged loss tracking.
     */
    public function resolveItemUnitCost(OrderItem $orderItem): float
    {
        if ($orderItem->variant_id && $orderItem->variant && (float) $orderItem->variant->grn_price > 0) {
            return (float) $orderItem->variant->grn_price;
        }

        $product = $orderItem->product;
        if (! $product && $orderItem->product_name) {
            $product = Product::where('name', $orderItem->product_name)->first();
        }

        if ($product) {
            if ($orderItem->color && $product->relationLoaded('variants')) {
                $matchedVar = $product->variants->firstWhere('color_name', $orderItem->color);
                if ($matchedVar && (float) $matchedVar->grn_price > 0) {
                    return (float) $matchedVar->grn_price;
                }
            }

            $cost = (float) ($product->grn_price ?: $product->cost_price ?: 0);
            if ($cost <= 0 && $product->product_code) {
                $cost = (float) (GrnItem::where('product_code', $product->product_code)->latest()->value('price_per_unit') ?? 0);
            }
            if ($cost > 0) {
                return $cost;
            }
        }

        return round((float) $orderItem->unit_price * 0.75, 2);
    }

    /**
     * Process a customer return & refund for an order.
     *
     * @param  array<int, array{order_item_id: int, quantity: int, condition?: string, notes?: string|null}>  $itemsData
     * @param  array{refund_method?: string, reason?: string|null, admin_notes?: string|null, status?: string}  $meta
     */
    public function processCustomerReturn(Order $order, array $itemsData, array $meta = []): CustomerReturn
    {
        if (empty($itemsData)) {
            throw new InvalidArgumentException('At least one item must be selected for return.');
        }

        return DB::transaction(function () use ($order, $itemsData, $meta) {
            $totalRefundAmount = 0.0;
            $preparedItems = [];

            foreach ($itemsData as $entry) {
                $qty = (int) ($entry['quantity'] ?? 0);
                if ($qty <= 0) {
                    continue;
                }

                /** @var OrderItem $orderItem */
                $orderItem = OrderItem::with(['product', 'variant'])->findOrFail($entry['order_item_id']);
                if ((int) $orderItem->order_id !== (int) $order->id) {
                    throw new InvalidArgumentException("Order item #{$orderItem->id} does not belong to order #{$order->order_number}.");
                }

                $returnable = $orderItem->returnable_quantity;
                if ($qty > $returnable) {
                    throw new InvalidArgumentException("Cannot return {$qty} of '{$orderItem->product_name}'. Only {$returnable} unit(s) remain returnable.");
                }

                $unitPrice = (float) $orderItem->unit_price;
                $subtotal = round($unitPrice * $qty, 2);
                $unitCost = $this->resolveItemUnitCost($orderItem);
                $condition = ($entry['condition'] ?? 'damaged') === 'good' ? 'good' : 'damaged';
                $resolutionStatus = $condition === 'good' ? 'restocked' : 'in_damaged_stock';

                $totalRefundAmount += $subtotal;
                $preparedItems[] = [
                    'order_item_id' => $orderItem->id,
                    'product_id' => $orderItem->product_id,
                    'variant_id' => $orderItem->variant_id,
                    'product_name' => $orderItem->product_name,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'refund_subtotal' => $subtotal,
                    'unit_cost' => $unitCost,
                    'condition' => $condition,
                    'resolution_status' => $resolutionStatus,
                    'notes' => $entry['notes'] ?? null,
                ];
            }

            if (empty($preparedItems)) {
                throw new InvalidArgumentException('No valid return quantities were provided.');
            }

            // Allow optional manual override of refund amount (e.g. partial restocking fee), capped at order net remaining
            $maxRefundable = max(0.0, (float) $order->total_amount - (float) ($order->refunded_amount ?? 0));
            $finalRefundAmount = isset($meta['refund_amount']) && is_numeric($meta['refund_amount'])
                ? min($maxRefundable, max(0.0, (float) $meta['refund_amount']))
                : min($maxRefundable, round($totalRefundAmount, 2));

            $status = $meta['status'] ?? 'approved';

            $customerReturn = CustomerReturn::create([
                'return_number' => CustomerReturn::generateReturnNumber(),
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'refund_amount' => $finalRefundAmount,
                'refund_method' => $meta['refund_method'] ?? 'cash',
                'status' => $status,
                'requested_by_customer' => (bool) ($meta['requested_by_customer'] ?? false),
                'reason' => $meta['reason'] ?? 'Damaged / Defective Product',
                'admin_notes' => $meta['admin_notes'] ?? null,
                'payment_receipt_path' => $meta['payment_receipt_path'] ?? null,
                'evidence_image_path' => $meta['evidence_image_path'] ?? null,
                'bank_name' => $meta['bank_name'] ?? null,
                'bank_branch' => $meta['bank_branch'] ?? null,
                'account_number' => $meta['account_number'] ?? null,
                'account_name' => $meta['account_name'] ?? null,
                'refunded_at' => $status === 'approved' ? now() : null,
                'created_by' => Auth::id(),
            ]);

            foreach ($preparedItems as $itemData) {
                $customerReturn->items()->create($itemData);
            }

            if ($status === 'approved') {
                $this->syncOrderRefundSummary($order->fresh());
                Product::syncAllProductAvailableQuantities();
            }

            return $customerReturn->load('items');
        });
    }

    /**
     * Approve a pending customer refund request:
     * Updates status to approved, syncs Order refunded totals, and triggers Damaged Stock / P&L updates.
     */
    public function approveCustomerReturn(CustomerReturn $customerReturn, ?string $adminNotes = null): CustomerReturn
    {
        if ($customerReturn->status !== 'pending') {
            throw new InvalidArgumentException("Only pending refund requests can be approved (current status: {$customerReturn->status}).");
        }

        return DB::transaction(function () use ($customerReturn, $adminNotes) {
            $customerReturn->update([
                'status' => 'approved',
                'refunded_at' => now(),
                'admin_notes' => $adminNotes ? trim(($customerReturn->admin_notes ? $customerReturn->admin_notes . "\n" : '') . $adminNotes) : $customerReturn->admin_notes,
            ]);

            $this->syncOrderRefundSummary($customerReturn->order->fresh());
            Product::syncAllProductAvailableQuantities();

            return $customerReturn->fresh('items');
        });
    }

    /**
     * Reject a pending customer refund request without altering sales or stock.
     */
    public function rejectCustomerReturn(CustomerReturn $customerReturn, ?string $rejectionReason = null): CustomerReturn
    {
        if ($customerReturn->status !== 'pending') {
            throw new InvalidArgumentException("Only pending refund requests can be rejected (current status: {$customerReturn->status}).");
        }

        return DB::transaction(function () use ($customerReturn, $rejectionReason) {
            $customerReturn->update([
                'status' => 'rejected',
                'rejection_reason' => $rejectionReason ?: 'Refund request was declined upon review.',
            ]);

            return $customerReturn->fresh('items');
        });
    }

    /**
     * Recalculate Order refunded_amount and refund_status from approved CustomerReturns.
     */
    public function syncOrderRefundSummary(Order $order): void
    {
        $approvedRefundSum = (float) $order->customerReturns()
            ->where('status', 'approved')
            ->sum('refund_amount');

        $totalAmount = (float) $order->total_amount;
        $refundStatus = 'none';
        if ($approvedRefundSum > 0) {
            $refundStatus = $approvedRefundSum >= ($totalAmount - 0.01) ? 'full' : 'partial';
        }

        $order->updateQuietly([
            'refunded_amount' => round($approvedRefundSum, 2),
            'refund_status' => $refundStatus,
        ]);
    }

    /**
     * Create a Supplier Return (RMA) from damaged customer return items.
     *
     * @param  array<int, array{customer_return_item_id?: int|null, product_id?: int|null, variant_id?: int|null, grn_item_id?: int|null, product_name?: string, quantity: int, unit_cost: float}>  $itemsData
     * @param  array{status?: string, notes?: string|null}  $meta
     */
    public function createSupplierReturn(int $supplierId, ?int $grnId, array $itemsData, array $meta = []): SupplierReturn
    {
        if (empty($itemsData)) {
            throw new InvalidArgumentException('At least one damaged item must be added to the supplier return.');
        }

        return DB::transaction(function () use ($supplierId, $grnId, $itemsData, $meta) {
            $totalCostValue = 0.0;
            $preparedItems = [];

            foreach ($itemsData as $entry) {
                $qty = (int) ($entry['quantity'] ?? 0);
                if ($qty <= 0) {
                    continue;
                }

                $unitCost = (float) ($entry['unit_cost'] ?? 0);
                $productId = $entry['product_id'] ?? null;
                $variantId = $entry['variant_id'] ?? null;
                $productName = $entry['product_name'] ?? 'Unknown Product';
                $customerReturnItemId = $entry['customer_return_item_id'] ?? null;
                $grnItemId = $entry['grn_item_id'] ?? null;

                if ($customerReturnItemId) {
                    /** @var CustomerReturnItem $cri */
                    $cri = CustomerReturnItem::findOrFail($customerReturnItemId);
                    $remaining = $cri->remaining_damaged_quantity;
                    if ($qty > $remaining) {
                        throw new InvalidArgumentException("Cannot send {$qty} of '{$cri->product_name}' to supplier. Only {$remaining} damaged unit(s) remain.");
                    }
                    $productId = $productId ?: $cri->product_id;
                    $variantId = $variantId ?: $cri->variant_id;
                    $productName = $cri->product_name;
                    if ($unitCost <= 0) {
                        $unitCost = (float) $cri->unit_cost;
                    }
                }

                $lineTotal = round($qty * $unitCost, 2);
                $totalCostValue += $lineTotal;

                $preparedItems[] = [
                    'customer_return_item_id' => $customerReturnItemId,
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'grn_item_id' => $grnItemId,
                    'product_name' => $productName,
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'total_cost' => $lineTotal,
                ];
            }

            if (empty($preparedItems)) {
                throw new InvalidArgumentException('No valid supplier return items were provided.');
            }

            $status = $meta['status'] ?? 'dispatched';

            $supplierReturn = SupplierReturn::create([
                'rma_number' => SupplierReturn::generateRmaNumber(),
                'supplier_id' => $supplierId,
                'grn_id' => $grnId,
                'status' => $status,
                'total_cost_value' => round($totalCostValue, 2),
                'credit_amount' => 0,
                'dispatched_at' => in_array($status, ['dispatched', 'credited', 'replaced']) ? now() : null,
                'notes' => $meta['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            foreach ($preparedItems as $itemData) {
                $supplierReturn->items()->create($itemData);

                if ($itemData['customer_return_item_id']) {
                    CustomerReturnItem::where('id', $itemData['customer_return_item_id'])
                        ->update(['resolution_status' => 'sent_to_supplier']);
                }
            }

            return $supplierReturn->load('items');
        });
    }

    /**
     * Resolve a Supplier Return when the supplier issues a Credit/Refund, Replacement, or Rejects.
     */
    public function resolveSupplierReturn(
        SupplierReturn $supplierReturn,
        string $resolutionType,
        float $creditAmount = 0.0,
        ?string $notes = null
    ): SupplierReturn {
        return DB::transaction(function () use ($supplierReturn, $resolutionType, $creditAmount, $notes) {
            $statusMap = [
                'credit_refund' => 'credited',
                'replacement' => 'replaced',
                'rejected' => 'rejected',
            ];

            if (! isset($statusMap[$resolutionType])) {
                throw new InvalidArgumentException("Invalid resolution type: {$resolutionType}");
            }

            $newStatus = $statusMap[$resolutionType];
            $itemResolutionMap = [
                'credited' => 'supplier_credited',
                'replaced' => 'supplier_replaced',
                'rejected' => 'scrapped',
            ];

            $finalCredit = $newStatus === 'credited'
                ? ($creditAmount > 0 ? $creditAmount : (float) $supplierReturn->total_cost_value)
                : 0.0;

            $supplierReturn->update([
                'status' => $newStatus,
                'resolution_type' => $resolutionType,
                'credit_amount' => round($finalCredit, 2),
                'resolved_at' => now(),
                'notes' => $notes ? trim(($supplierReturn->notes ? $supplierReturn->notes . "\n" : '') . $notes) : $supplierReturn->notes,
            ]);

            $criIds = $supplierReturn->items()
                ->whereNotNull('customer_return_item_id')
                ->pluck('customer_return_item_id')
                ->all();

            if (! empty($criIds)) {
                CustomerReturnItem::whereIn('id', $criIds)
                    ->update(['resolution_status' => $itemResolutionMap[$newStatus]]);
            }

            if ($newStatus === 'replaced') {
                Product::syncAllProductAvailableQuantities();
            }

            return $supplierReturn->fresh('items');
        });
    }
}
