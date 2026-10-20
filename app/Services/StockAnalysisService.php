<?php

namespace App\Services;

use App\Models\GrnItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class StockAnalysisService
{
    /**
     * Resolve date range from preset period.
     */
    public function resolveDateRange(string $period, ?string $customStart = null, ?string $customEnd = null): array
    {
        $now = Carbon::now();

        switch ($period) {
            case 'today':
                return [
                    'start' => $now->copy()->startOfDay(),
                    'end' => $now->copy()->endOfDay(),
                    'label' => 'Today (' . $now->format('M d, Y') . ')',
                ];

            case 'this_week':
                return [
                    'start' => $now->copy()->startOfWeek(),
                    'end' => $now->copy()->endOfWeek(),
                    'label' => 'This Week (' . $now->copy()->startOfWeek()->format('M d') . ' - ' . $now->copy()->endOfWeek()->format('M d, Y') . ')',
                ];

            case 'this_month':
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth(),
                    'label' => 'This Month (' . $now->format('F Y') . ')',
                ];

            case 'last_month':
                $lastMonth = $now->copy()->subMonth();
                return [
                    'start' => $lastMonth->copy()->startOfMonth(),
                    'end' => $lastMonth->copy()->endOfMonth(),
                    'label' => 'Last Month (' . $lastMonth->format('F Y') . ')',
                ];

            case 'this_year':
                return [
                    'start' => $now->copy()->startOfYear(),
                    'end' => $now->copy()->endOfYear(),
                    'label' => 'This Year (' . $now->format('Y') . ')',
                ];

            case 'custom':
                $start = $customStart ? Carbon::parse($customStart)->startOfDay() : null;
                $end = $customEnd ? Carbon::parse($customEnd)->endOfDay() : null;
                $label = 'Custom Range (' . ($start ? $start->format('M d, Y') : 'Beginning') . ' to ' . ($end ? $end->format('M d, Y') : 'Present') . ')';

                return [
                    'start' => $start,
                    'end' => $end,
                    'label' => $label,
                ];

            case 'all':
            default:
                return [
                    'start' => null,
                    'end' => null,
                    'label' => 'All Time',
                ];
        }
    }

    /**
     * Run full product-wise analysis with filters.
     */
    public function analyze(
        string $period = 'all',
        ?string $customStart = null,
        ?string $customEnd = null,
        ?int $supplierId = null,
        ?int $categoryId = null,
        ?string $search = null
    ): array {
        $dates = $this->resolveDateRange($period, $customStart, $customEnd);
        $start = $dates['start'];
        $end = $dates['end'];

        $productQuery = Product::query()
            ->with(['supplier', 'brand', 'categories', 'grnItems.grn.purchaseOrder.supplier'])
            ->latest('id');

        if ($supplierId) {
            $productQuery->where('supplier_id', $supplierId);
        }

        if ($categoryId) {
            $productQuery->whereHas('categories', fn ($q) => $q->where('categories.id', $categoryId));
        }

        if ($search && trim($search) !== '') {
            $term = trim($search);
            $productQuery->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('product_code', 'like', "%{$term}%");
            });
        }

        $products = $productQuery->get();

        $rows = [];
        $totalExpense = 0.0;
        $totalPurchasedQty = 0;
        $totalRevenue = 0.0;
        $totalSoldQty = 0;
        $totalCogs = 0.0;
        $totalGrossProfit = 0.0;
        $totalStockValue = 0.0;
        $totalRemainingStock = 0;

        foreach ($products as $product) {
            // 1. Inward Purchases (GRNs)
            $grnItemQuery = GrnItem::query();
            if ($product->product_code) {
                $grnItemQuery->where('product_code', $product->product_code);
            } else {
                $grnItemQuery->where('product_name', $product->name);
            }

            // All time GRN purchases for stock tracking
            $allTimeGrns = (clone $grnItemQuery)->get();
            $allTimePurchasedQty = (int) $allTimeGrns->sum('product_quantity');
            $allTimePurchaseExpense = (float) $allTimeGrns->sum('total');

            // Period filtered GRN purchases
            if ($start && $end) {
                $grnItemQuery->whereBetween('created_at', [$start, $end]);
            }
            $periodGrns = $grnItemQuery->get();
            $purchasedQty = (int) $periodGrns->sum('product_quantity');
            $purchaseExpense = (float) $periodGrns->sum('total');

            // Fallback for expense if no GRNs recorded yet but product has inventory stock & grn_price
            if ($allTimePurchasedQty === 0 && (int) ($product->inventory_stock ?? 0) > 0) {
                $allTimePurchasedQty = (int) $product->inventory_stock;
                $allTimePurchaseExpense = $allTimePurchasedQty * (float) ($product->grn_price ?: $product->cost_price ?: 0);
                if (! $start && ! $end) {
                    $purchasedQty = $allTimePurchasedQty;
                    $purchaseExpense = $allTimePurchaseExpense;
                }
            }

            // Calculate average unit purchase price
            $avgPurchasePrice = 0.0;
            if ($purchasedQty > 0) {
                $avgPurchasePrice = round($purchaseExpense / $purchasedQty, 2);
            } elseif ($allTimePurchasedQty > 0) {
                $avgPurchasePrice = round($allTimePurchaseExpense / $allTimePurchasedQty, 2);
            } else {
                $avgPurchasePrice = (float) ($product->grn_price ?: $product->cost_price ?: 0);
            }

            // 2. Outward Sales (Paid Customer Orders)
            $orderItemQuery = OrderItem::query()
                ->where(function ($q) use ($product) {
                    $q->where('product_id', $product->id);
                    if ($product->product_code) {
                        $q->orWhere('product_name', $product->name);
                    }
                })
                ->whereHas('order', function ($q) {
                    $q->where('payment_status', 'Paid');
                });

            // All-time sales
            $allTimeSoldItems = (clone $orderItemQuery)->get();
            $allTimeSoldQty = (int) $allTimeSoldItems->sum('quantity');
            $allTimeSalesRevenue = (float) $allTimeSoldItems->sum('total_price');

            // Period filtered sales
            if ($start && $end) {
                $orderItemQuery->whereHas('order', function ($q) use ($start, $end) {
                    $q->where('payment_status', 'Paid')
                      ->whereBetween('created_at', [$start, $end]);
                });
            }
            $periodSoldItems = $orderItemQuery->get();
            $soldQty = (int) $periodSoldItems->sum('quantity');
            $salesRevenue = (float) $periodSoldItems->sum('total_price');

            // Calculate average selling price
            $avgSellingPrice = $soldQty > 0
                ? round($salesRevenue / $soldQty, 2)
                : (float) ($product->sale_price ?: $product->price ?: 0);

            // 3. Financial Computations
            // Cost of Goods Sold (purchase cost of items sold)
            $cogs = round($soldQty * $avgPurchasePrice, 2);
            $grossProfit = round($salesRevenue - $cogs, 2);
            $profitMarginPct = $salesRevenue > 0
                ? round(($grossProfit / $salesRevenue) * 100, 2)
                : 0.0;

            // Physical remaining inventory
            $currentStock = max(0, $allTimePurchasedQty - $allTimeSoldQty);
            $remainingStockValue = round($currentStock * $avgPurchasePrice, 2);

            // Cash position: Revenue minus Total Purchase Expense
            $netCashFlow = round($salesRevenue - $purchaseExpense, 2);

            // Progress towards recovering purchase expense
            $recoveryRate = $purchaseExpense > 0
                ? min(100.0, round(($salesRevenue / $purchaseExpense) * 100, 1))
                : ($salesRevenue > 0 ? 100.0 : 0.0);

            // Thumbnail
            $images = is_array($product->images) ? $product->images : [];
            $firstImg = count($images) > 0 ? $images[0] : null;
            $imgUrl = $firstImg
                ? ((str_starts_with($firstImg, 'http') || str_starts_with($firstImg, '/')) ? $firstImg : asset('storage/' . $firstImg))
                : '/images/placeholder.png';

            $rows[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'product_code' => $product->product_code ?? 'N/A',
                'condition' => $product->condition ?? 'Brand New',
                'supplier_name' => $product->supplier?->name ?? 'Default Supplier',
                'image_url' => $imgUrl,
                'current_stock' => $currentStock,
                'warehouse_stock' => (int) $product->balance_inventory_stock,
                'web_stock' => (int) ($product->quantity ?? 0),

                // Purchasing / Expense
                'purchased_qty' => $purchasedQty,
                'avg_purchase_price' => $avgPurchasePrice,
                'purchase_expense' => $purchaseExpense,

                // Sales / Revenue
                'sold_qty' => $soldQty,
                'avg_selling_price' => $avgSellingPrice,
                'sales_revenue' => $salesRevenue,

                // Profitability
                'cogs' => $cogs,
                'gross_profit' => $grossProfit,
                'profit_margin_pct' => $profitMarginPct,
                'net_cash_flow' => $netCashFlow,
                'recovery_rate' => $recoveryRate,
                'remaining_stock_value' => $remainingStockValue,
            ];

            // Accumulate summary totals
            $totalExpense += $purchaseExpense;
            $totalPurchasedQty += $purchasedQty;
            $totalRevenue += $salesRevenue;
            $totalSoldQty += $soldQty;
            $totalCogs += $cogs;
            $totalGrossProfit += $grossProfit;
            $totalStockValue += $remainingStockValue;
            $totalRemainingStock += $currentStock;
        }

        $overallProfitMargin = $totalRevenue > 0
            ? round(($totalGrossProfit / $totalRevenue) * 100, 2)
            : 0.0;

        $overallRecoveryRate = $totalExpense > 0
            ? min(100.0, round(($totalRevenue / $totalExpense) * 100, 1))
            : ($totalRevenue > 0 ? 100.0 : 0.0);

        return [
            'period_label' => $dates['label'],
            'period' => $period,
            'start_date' => $start?->format('Y-m-d'),
            'end_date' => $end?->format('Y-m-d'),
            'supplier_id' => $supplierId,
            'generated_at' => Carbon::now()->format('M d, Y h:i A'),

            'metrics' => [
                'total_expense' => $totalExpense,
                'total_purchased_qty' => $totalPurchasedQty,
                'total_revenue' => $totalRevenue,
                'total_sold_qty' => $totalSoldQty,
                'total_cogs' => $totalCogs,
                'total_gross_profit' => $totalGrossProfit,
                'overall_profit_margin' => $overallProfitMargin,
                'overall_recovery_rate' => $overallRecoveryRate,
                'total_stock_value' => $totalStockValue,
                'total_remaining_stock' => $totalRemainingStock,
                'total_products_count' => count($rows),
            ],

            'products' => $rows,
        ];
    }

    /**
     * Fetch granular inward GRN items and outward sales orders for a specific product.
     */
    public function getProductBreakdown(int $productId): array
    {
        $product = Product::with(['supplier', 'brand'])->findOrFail($productId);

        // 1. Inward GRNs
        $grnQuery = GrnItem::query()->with(['grn.purchaseOrder.supplier']);
        if ($product->product_code) {
            $grnQuery->where('product_code', $product->product_code);
        } else {
            $grnQuery->where('product_name', $product->name);
        }
        $grnItems = $grnQuery->latest('id')->get();

        $inwardBatches = $grnItems->map(function (GrnItem $item) {
            return [
                'grn_id' => $item->grn_id,
                'grn_code' => $item->grn?->grn_number ?? ('GRN-#' . $item->grn_id),
                'received_date' => $item->grn?->received_date ? Carbon::parse($item->grn->received_date)->format('M d, Y') : $item->created_at->format('M d, Y'),
                'supplier_name' => $item->grn?->purchaseOrder?->supplier?->name ?? 'Default Supplier',
                'quantity' => (int) $item->product_quantity,
                'unit_price' => (float) $item->price_per_unit,
                'total' => (float) $item->total,
                'condition' => $item->product_condition ?? 'Brandnew',
            ];
        })->toArray();

        // 2. Outward Sales Orders
        $orderItems = OrderItem::query()
            ->with(['order'])
            ->where(function ($q) use ($product) {
                $q->where('product_id', $product->id);
                if ($product->product_code) {
                    $q->orWhere('product_name', $product->name);
                }
            })
            ->whereHas('order', function ($q) {
                $q->where('payment_status', 'Paid');
            })
            ->latest('id')
            ->get();

        $outwardSales = $orderItems->map(function (OrderItem $item) {
            return [
                'order_id' => $item->order_id,
                'order_number' => $item->order?->order_number ?? ('ORD-#' . $item->order_id),
                'order_date' => $item->order?->created_at ? $item->order->created_at->format('M d, Y h:i A') : $item->created_at->format('M d, Y'),
                'customer_name' => $item->order?->customer_name ?? 'Guest Customer',
                'customer_phone' => $item->order?->customer_phone ?? '-',
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'total_price' => (float) $item->total_price,
                'payment_method' => $item->order?->payment_method ?? 'Card Payment',
                'status' => $item->order?->status ?? 'Completed',
            ];
        })->toArray();

        return [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'product_code' => $product->product_code ?? 'N/A',
                'condition' => $product->condition ?? 'Brand New',
                'supplier_name' => $product->supplier?->name ?? 'N/A',
            ],
            'inward_batches' => $inwardBatches,
            'outward_sales' => $outwardSales,
            'total_inward_qty' => array_sum(array_column($inwardBatches, 'quantity')),
            'total_inward_expense' => array_sum(array_column($inwardBatches, 'total')),
            'total_outward_qty' => array_sum(array_column($outwardSales, 'quantity')),
            'total_outward_revenue' => array_sum(array_column($outwardSales, 'total_price')),
        ];
    }
}
