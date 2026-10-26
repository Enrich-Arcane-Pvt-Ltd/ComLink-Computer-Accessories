<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\GrnItem;
use App\Models\InventoryProduct;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class StockManagementService
{
    /**
     * Generate full stock-wise report for a product or inventory product.
     */
    public function generateReportForProduct(Product|InventoryProduct|string $productOrCode): array
    {
        $resolved = $this->resolveProductData($productOrCode);
        $product = $resolved['product'];
        $invProduct = $resolved['invProduct'];
        $productCode = $resolved['productCode'];
        $productName = $resolved['productName'];
        $latestPrice = $resolved['latestPrice'];
        $latestGrnPrice = $resolved['latestGrnPrice'];

        // 1. Fetch all stock batches (GRN Items) in chronological order (FIFO)
        $batchRecords = GrnItem::with(['grn.supplier', 'inventoryProduct'])
            ->where(function ($q) use ($productCode, $productName) {
                if ($productCode) {
                    $q->where('product_code', $productCode);
                } else {
                    $q->where('product_name', $productName);
                }
            })
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        if ($batchRecords->isNotEmpty() && empty($latestGrnPrice)) {
            $latestGrnPrice = (float) $batchRecords->last()->price_per_unit;
        }

        // 2. Fetch all paid orders for this product in chronological order
        $orderItems = OrderItem::with(['order', 'variant'])
            ->where(function ($q) use ($product, $productCode, $productName) {
                if ($product && $product->id) {
                    $q->where('product_id', $product->id);
                }
                if ($productCode) {
                    $q->orWhereHas('product', fn ($sq) => $sq->where('product_code', $productCode));
                }
                if ($productName) {
                    $q->orWhere('product_name', $productName);
                }
            })
            ->whereHas('order', fn ($q) => $q->where('payment_status', 'Paid'))
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // 3. Perform FIFO Allocation across batches
        $batches = $this->allocateFifoSales($batchRecords, $orderItems, $latestPrice, $product);

        // 4. Calculate Aggregate Totals
        $totalStock = 0;
        $totalSold = 0;
        $totalAvailable = 0;
        $totalGrnCost = 0.0;
        $totalSoldCost = 0.0;
        $totalUnsoldCost = 0.0;
        $totalAchievedRevenue = 0.0;
        $totalExpectedRevenue = 0.0;
        $endedBatchesCount = 0;
        $activeBatchCodes = [];

        foreach ($batches as $batch) {
            $totalStock += $batch['quantity'];
            $totalSold += $batch['sold_quantity'];
            $totalAvailable += $batch['available_quantity'];
            $totalGrnCost += $batch['total_grn_cost'];
            $totalSoldCost += $batch['realized_cogs'];
            $totalUnsoldCost += round($batch['available_quantity'] * $batch['grn_unit_price'], 2);
            $totalAchievedRevenue += $batch['achieved_from_sale'];
            $totalExpectedRevenue += $batch['revenue_price'];

            if ($batch['status'] === 'ENDED') {
                $endedBatchesCount++;
            }
            if ($batch['status'] === 'ACTIVE') {
                $label = ! empty($batch['color_name'])
                    ? "{$batch['stock_code']} ({$batch['color_name']})"
                    : $batch['stock_code'];
                $activeBatchCodes[] = $label;
            }
        }

        $totalRealizedProfit = round($totalAchievedRevenue - $totalSoldCost, 2);
        $realizedMarginPct = $totalAchievedRevenue > 0
            ? round(($totalRealizedProfit / $totalAchievedRevenue) * 100, 1)
            : 0.0;

        return [
            'product' => [
                'id' => $product?->id,
                'name' => $productName,
                'product_code' => $productCode,
                'supplier_name' => $product?->supplier?->name ?? $invProduct?->supplier?->name ?? 'Direct Stock',
                'image_url' => $this->resolveProductImage($product, $invProduct),
                'condition' => $product?->condition ?? 'Brand New',
                'latest_price' => $latestPrice,
                'latest_grn_price' => $latestGrnPrice,
                'retail_unit_margin' => round($latestPrice - $latestGrnPrice, 2),
                'has_variants' => $product ? $product->hasColorVariants() : false,
            ],
            'summary' => [
                'total_stock' => $totalStock,
                'total_sold' => $totalSold,
                'total_available' => $totalAvailable,
                'total_grn_cost' => round($totalGrnCost, 2),
                'total_sold_cost' => round($totalSoldCost, 2),
                'total_unsold_cost' => round($totalUnsoldCost, 2),
                'total_achieved_revenue' => round($totalAchievedRevenue, 2),
                'total_expected_revenue' => round($totalExpectedRevenue, 2),
                'total_profit_achieved' => $totalRealizedProfit,
                'total_realized_profit' => $totalRealizedProfit,
                'realized_margin_pct' => $realizedMarginPct,
                'total_batches_count' => count($batches),
                'ended_batches_count' => $endedBatchesCount,
                'active_batch_code' => ! empty($activeBatchCodes) ? implode(', ', $activeBatchCodes) : 'None (Out of Stock / No Batches)',
                'active_batch_codes' => $activeBatchCodes,
            ],
            'batches' => $batches,
        ];
    }

    /**
     * FIFO allocation of order sales to stock batches.
     */
    protected function allocateFifoSales(
        Collection $batchRecords,
        Collection $orderItems,
        float $productLatestPrice,
        ?Product $product = null
    ): array {
        if ($batchRecords->isEmpty()) {
            return [];
        }

        // Prepare batches state
        $batches = [];
        foreach ($batchRecords as $record) {
            // Determine effective latest price (variant specific if color variant)
            $batchLatestPrice = $productLatestPrice;
            if ($product && ! empty($record->color_name)) {
                $v = $product->variants->first(fn ($variant) => strcasecmp((string) $variant->color_name, (string) $record->color_name) === 0);
                if ($v) {
                    $batchLatestPrice = (float) ($v->sale_price ?: $v->price ?: $productLatestPrice);
                }
            }

            $batches[] = [
                'id' => $record->id,
                'stock_code' => $record->stock_code,
                'grn_number' => $record->grn?->grn_number ?? 'DIRECT-STOCK',
                'supplier_name' => $record->grn?->supplier?->name ?? $record->inventoryProduct?->supplier?->name ?? 'Direct Stock',
                'created_at' => $record->created_at,
                'added_date' => $record->created_at ? $record->created_at->format('M d, Y h:i A') : 'N/A',
                'condition' => $record->product_condition ?: 'Brandnew',
                'color_name' => $record->color_name,
                'quantity' => (int) $record->product_quantity,
                'remaining_capacity' => (int) $record->product_quantity,
                'grn_unit_price' => (float) $record->price_per_unit,
                'total_grn_cost' => round((float) $record->price_per_unit * (int) $record->product_quantity, 2),
                'latest_price' => $batchLatestPrice,
                'sold_quantity' => 0,
                'available_quantity' => (int) $record->product_quantity,
                'revenue_price' => round((float) $record->price_per_unit * (int) $record->product_quantity, 2),
                'achieved_from_sale' => 0.0,
                'realized_cogs' => 0.0,
                'realized_profit' => 0.0,
                'profit_achieved' => 0.0,
                'realized_margin_pct' => 0.0,
                'status' => 'QUEUED',
                'sales_breakdown' => [], // Keyed by price: [price => [qty, revenue, date_range]]
            ];
        }

        // Clone order items queue: each has [qty_remaining, unit_price, date, order_no, color]
        $orderQueue = [];
        foreach ($orderItems as $item) {
            $color = $item->color ?? $item->variant?->color_name;
            $orderQueue[] = [
                'remaining' => (int) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'created_at' => $item->created_at,
                'order_no' => $item->order?->order_number ?? ("#".$item->order_id),
                'color' => $color,
                'variant_id' => $item->variant_id,
            ];
        }

        // Run FIFO matching
        foreach ($batches as &$batch) {
            if ($batch['remaining_capacity'] <= 0) {
                continue;
            }

            foreach ($orderQueue as &$order) {
                if ($order['remaining'] <= 0) {
                    continue;
                }

                // If batch has a specific color, match only orders with matching color
                if (! empty($batch['color_name']) && ! empty($order['color'])) {
                    if (strcasecmp((string) $batch['color_name'], (string) $order['color']) !== 0) {
                        continue;
                    }
                }

                $take = min($batch['remaining_capacity'], $order['remaining']);
                if ($take <= 0) {
                    continue;
                }

                // Allocate $take units
                $batch['sold_quantity'] += $take;
                $batch['remaining_capacity'] -= $take;
                $order['remaining'] -= $take;

                $soldPrice = $order['unit_price'];
                $priceKey = number_format($soldPrice, 2, '.', '');

                if (! isset($batch['sales_breakdown'][$priceKey])) {
                    $batch['sales_breakdown'][$priceKey] = [
                        'selling_price' => $soldPrice,
                        'grn_price' => $batch['grn_unit_price'],
                        'quantity' => 0,
                        'total_revenue' => 0.0,
                        'achieved_profit' => 0.0,
                        'orders_count' => 0,
                        'first_order_date' => $order['created_at'],
                        'last_order_date' => $order['created_at'],
                    ];
                }

                $batch['sales_breakdown'][$priceKey]['quantity'] += $take;
                $batch['sales_breakdown'][$priceKey]['total_revenue'] += round($take * $soldPrice, 2);
                $batch['sales_breakdown'][$priceKey]['achieved_profit'] += round($take * ($soldPrice - $batch['grn_unit_price']), 2);
                $batch['sales_breakdown'][$priceKey]['orders_count'] += 1;
                $batch['sales_breakdown'][$priceKey]['last_order_date'] = $order['created_at'];

                $batch['achieved_from_sale'] += round($take * $soldPrice, 2);

                if ($batch['remaining_capacity'] <= 0) {
                    break;
                }
            }
        }
        unset($batch, $order);

        // Finalize status for each batch: track active FIFO batch separately for each color variant
        $activeAssignedPerColor = [];
        foreach ($batches as &$batch) {
            $batch['available_quantity'] = max(0, $batch['quantity'] - $batch['sold_quantity']);
            $batch['realized_cogs'] = round($batch['sold_quantity'] * $batch['grn_unit_price'], 2);
            $batch['realized_profit'] = round($batch['achieved_from_sale'] - $batch['realized_cogs'], 2);
            $batch['profit_achieved'] = $batch['realized_profit'];
            $batch['realized_margin_pct'] = $batch['achieved_from_sale'] > 0
                ? round(($batch['realized_profit'] / $batch['achieved_from_sale']) * 100, 1)
                : 0.0;
            $batch['sales_breakdown'] = array_values($batch['sales_breakdown']);

            // Color variant grouping key (or __default__ for non-variant products)
            $colorKey = ! empty($batch['color_name']) ? strtolower(trim((string) $batch['color_name'])) : '__default__';

            if ($batch['available_quantity'] <= 0) {
                $batch['status'] = 'ENDED'; // Stock ended / completely sold out
            } elseif (! isset($activeAssignedPerColor[$colorKey])) {
                $batch['status'] = 'ACTIVE'; // First available batch for THIS color variant in FIFO = Current Selling Stock!
                $activeAssignedPerColor[$colorKey] = $batch['stock_code'];
            } else {
                $batch['status'] = 'QUEUED'; // In queue / upcoming stock for this color variant
            }
        }
        unset($batch);

        return $batches;
    }

    /**
     * Helper to resolve product references from diverse inputs.
     */
    protected function resolveProductData(Product|InventoryProduct|string $productOrCode): array
    {
        $product = null;
        $invProduct = null;
        $productCode = null;
        $productName = null;
        $latestPrice = 0.0;
        $latestGrnPrice = 0.0;

        if ($productOrCode instanceof Product) {
            $product = $productOrCode;
            $productCode = $product->product_code;
            $productName = $product->name;
            $latestPrice = (float) ($product->sale_price ?: $product->price ?: 0);
            $latestGrnPrice = (float) ($product->grn_price ?: $product->cost_price ?: 0);
            $invProduct = $productCode ? InventoryProduct::where('product_code', $productCode)->first() : null;
        } elseif ($productOrCode instanceof InventoryProduct) {
            $invProduct = $productOrCode;
            $productCode = $invProduct->product_code;
            $productName = $invProduct->product_name;
            $product = Product::where('product_code', $productCode)->first();
            $latestPrice = $product ? (float) ($product->sale_price ?: $product->price ?: 0) : 0.0;
            $latestGrnPrice = (float) ($invProduct->latest_grn_price ?: 0);
        } else {
            $code = trim((string) $productOrCode);
            $product = Product::where('product_code', $code)->first();
            $invProduct = InventoryProduct::where('product_code', $code)->first();
            $productCode = $code;
            $productName = $product?->name ?? $invProduct?->product_name ?? $code;
            $latestPrice = $product ? (float) ($product->sale_price ?: $product->price ?: 0) : 0.0;
            $latestGrnPrice = (float) ($invProduct?->latest_grn_price ?: $product?->grn_price ?: 0);
        }

        return [
            'product' => $product,
            'invProduct' => $invProduct,
            'productCode' => $productCode,
            'productName' => $productName,
            'latestPrice' => $latestPrice,
            'latestGrnPrice' => $latestGrnPrice,
        ];
    }

    /**
     * Resolve product thumbnail URL.
     */
    protected function resolveProductImage(?Product $product, ?InventoryProduct $invProduct): string
    {
        if ($invProduct && $invProduct->product_image) {
            $img = $invProduct->product_image;
            return (str_starts_with($img, 'http') || str_starts_with($img, '/')) ? $img : asset('storage/' . $img);
        }

        if ($product && ! empty($product->images)) {
            $images = is_array($product->images) ? $product->images : [];
            $img = $images[0] ?? null;
            if ($img) {
                return (str_starts_with($img, 'http') || str_starts_with($img, '/')) ? $img : asset('storage/' . $img);
            }
        }

        return '/images/placeholder.png';
    }
}
