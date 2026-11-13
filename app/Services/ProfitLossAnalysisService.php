<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\GrnItem;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ProfitLossAnalysisService
{
    /**
     * Calculate comprehensive Profit & Loss metrics for filtered or all products and time period.
     *
     * @param int|null $categoryId Parent Category ID
     * @param int|null $subCategoryId Subcategory ID
     * @param int|null $productId Specific Product ID
     * @param string $period 'all', 'daily', 'weekly', 'monthly', 'annually', 'custom'
     * @param string|null $startDate 'YYYY-MM-DD'
     * @param string|null $endDate 'YYYY-MM-DD'
     * @return array
     */
    public function calculate(
        ?int $categoryId = null,
        ?int $subCategoryId = null,
        ?int $productId = null,
        string $period = 'all',
        ?string $startDate = null,
        ?string $endDate = null
    ): array
    {
        // 1. Resolve Time Period Dates
        $startDateTime = null;
        $endDateTime = null;
        $periodLabel = 'All Time';

        switch ($period) {
            case 'daily':
                $startDateTime = Carbon::today()->startOfDay();
                $endDateTime = Carbon::today()->endOfDay();
                $periodLabel = 'Daily (' . Carbon::today()->format('M d, Y') . ')';
                break;
            case 'weekly':
                $startDateTime = Carbon::now()->subDays(6)->startOfDay();
                $endDateTime = Carbon::now()->endOfDay();
                $periodLabel = 'Weekly (' . $startDateTime->format('M d') . ' - ' . $endDateTime->format('M d, Y') . ')';
                break;
            case 'monthly':
                $startDateTime = Carbon::now()->startOfMonth();
                $endDateTime = Carbon::now()->endOfMonth();
                $periodLabel = 'Monthly (' . Carbon::now()->format('F Y') . ')';
                break;
            case 'annually':
                $startDateTime = Carbon::now()->startOfYear();
                $endDateTime = Carbon::now()->endOfYear();
                $periodLabel = 'Annually (' . Carbon::now()->format('Y') . ')';
                break;
            case 'custom':
                if ($startDate && $endDate) {
                    $startDateTime = Carbon::parse($startDate)->startOfDay();
                    $endDateTime = Carbon::parse($endDate)->endOfDay();
                    $periodLabel = 'Custom (' . Carbon::parse($startDate)->format('M d, Y') . ' - ' . Carbon::parse($endDate)->format('M d, Y') . ')';
                } else {
                    $period = 'all';
                    $periodLabel = 'All Time';
                }
                break;
            default:
                $period = 'all';
                $periodLabel = 'All Time';
                break;
        }

        // 2. Resolve Filter Meta & Labels
        $category = $categoryId ? Category::find($categoryId) : null;
        $subcategory = $subCategoryId ? Category::find($subCategoryId) : null;
        $filteredProduct = $productId ? Product::find($productId) : null;

        $filterLabels = [];
        if ($category) {
            $filterLabels[] = 'Category: ' . $category->name;
        }
        if ($subcategory) {
            $filterLabels[] = 'Subcategory: ' . $subcategory->name;
        }
        if ($filteredProduct) {
            $filterLabels[] = 'Product: ' . $filteredProduct->name;
        }
        if ($period !== 'all') {
            $filterLabels[] = 'Period: ' . $periodLabel;
        }
        $filterDescription = empty($filterLabels) ? 'All Products (Store-Wide)' : implode(' > ', $filterLabels);

        // 3. Build Product Query
        $productQuery = Product::query()
            ->with(['supplier', 'brand', 'categories.parent', 'variants'])
            ->orderBy('name');

        if ($productId) {
            $productQuery->where('id', $productId);
        } elseif ($subCategoryId) {
            $productQuery->whereHas('categories', fn ($q) => $q->where('categories.id', $subCategoryId));
        } elseif ($categoryId) {
            $childCatIds = Category::where('parent_id', $categoryId)->pluck('id')->toArray();
            $allowedCatIds = array_merge([$categoryId], $childCatIds);
            $productQuery->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $allowedCatIds));
        }

        $products = $productQuery->get();

        // 4. Pre-aggregate GRN data (Quantity and Total Procurement Value per product_code)
        $grnAggregates = GrnItem::select(
            'product_code',
            DB::raw('SUM(product_quantity) as total_qty'),
            DB::raw('SUM(product_quantity * price_per_unit) as total_val')
        )
            ->whereNotNull('product_code')
            ->groupBy('product_code')
            ->get()
            ->keyBy('product_code');

        // Period-specific GRN aggregates if period filter is applied
        $periodGrnAggregates = null;
        if ($startDateTime && $endDateTime) {
            $periodGrnAggregates = GrnItem::select(
                'product_code',
                DB::raw('SUM(product_quantity) as total_qty'),
                DB::raw('SUM(product_quantity * price_per_unit) as total_val')
            )
                ->whereNotNull('product_code')
                ->whereBetween('created_at', [$startDateTime, $endDateTime])
                ->groupBy('product_code')
                ->get()
                ->keyBy('product_code');
        }

        // 5. Query Paid Order Items for the selected period with variant relations
        $paidOrderItemsQuery = OrderItem::with(['variant', 'product'])
            ->whereHas('order', function ($q) use ($startDateTime, $endDateTime) {
                $q->where('payment_status', 'Paid');
                if ($startDateTime && $endDateTime) {
                    $q->whereBetween('orders.created_at', [$startDateTime, $endDateTime]);
                }
            });

        $allPaidOrderItems = $paidOrderItemsQuery->get();

        // 6. Compute metrics per product and accumulate totals
        $productRows = [];
        $totalGrnValue = 0.0;
        $totalStockCount = 0;
        $availableStockCount = 0;
        $soldStockCount = 0;
        $gainedIncome = 0.0;
        $totalCogs = 0.0;
        $periodTotalGrn = 0.0;

        foreach ($products as $p) {
            // Stock counts & initial GRN valuation
            $fullCount = 0;
            $grnValue = 0.0;

            if ($p->product_code && isset($grnAggregates[$p->product_code])) {
                $agg = $grnAggregates[$p->product_code];
                $fullCount = (int) $agg->total_qty;
                $grnValue = (float) $agg->total_val;
            } else {
                $fullCount = (int) ($p->inventory_stock ?? $p->stock_added_count ?? $p->quantity ?? 0);
                $unitCost = (float) ($p->grn_price ?: $p->cost_price ?: 0);
                $grnValue = round($fullCount * $unitCost, 2);
            }

            // If product has color variations and parent stock/value wasn't set, aggregate from variants
            if ($p->relationLoaded('variants') && $p->variants->isNotEmpty()) {
                $variantStockSum = $p->variants->sum(fn ($v) => (int) ($v->inventory_stock ?? $v->stock ?? 0));
                $variantGrnSum = $p->variants->sum(fn ($v) => ((int) ($v->inventory_stock ?? $v->stock ?? 0)) * (float) ($v->grn_price ?: 0));
                if ($fullCount <= 0 && $variantStockSum > 0) {
                    $fullCount = $variantStockSum;
                }
                if ($grnValue <= 0 && $variantGrnSum > 0) {
                    $grnValue = round($variantGrnSum, 2);
                }
            }

            // Period GRN value if period filtered
            $itemPeriodGrn = 0.0;
            if ($periodGrnAggregates && $p->product_code && isset($periodGrnAggregates[$p->product_code])) {
                $itemPeriodGrn = (float) $periodGrnAggregates[$p->product_code]->total_val;
            }

            // Filter sold order items specifically for this product
            $productOrderItems = $allPaidOrderItems->filter(function ($item) use ($p) {
                return ($item->product_id && $item->product_id == $p->id)
                    || (!$item->product_id && $item->product_name && $item->product_name === $p->name);
            });

            $soldQty = (int) $productOrderItems->sum('quantity');
            $income = (float) $productOrderItems->sum('total_price');

            $availableQty = max(0, $fullCount - $soldQty);
            $sellingPrice = (float) ($p->sale_price ?: $p->price ?: 0);
            $defaultUnitCost = (float) ($p->grn_price ?: $p->cost_price ?: 0);
            if ($defaultUnitCost <= 0 && $p->product_code) {
                $defaultUnitCost = (float) (GrnItem::where('product_code', $p->product_code)->latest()->value('price_per_unit') ?? 0);
            }
            if ($defaultUnitCost <= 0 && $sellingPrice > 0) {
                $defaultUnitCost = round($sellingPrice * 0.75, 2);
            }

            // Calculate exact COGS item by item using variant GRN cost if present
            $productCogs = 0.0;
            foreach ($productOrderItems as $item) {
                $itemQty = (int) $item->quantity;
                $itemUnitCost = 0.0;

                // 1. Check if order item has variant relation with grn_price
                if ($item->variant_id && $item->variant && (float) $item->variant->grn_price > 0) {
                    $itemUnitCost = (float) $item->variant->grn_price;
                } elseif ($item->color && $p->relationLoaded('variants') && $p->variants->isNotEmpty()) {
                    $matchedVar = $p->variants->firstWhere('color_name', $item->color);
                    if ($matchedVar && (float) $matchedVar->grn_price > 0) {
                        $itemUnitCost = (float) $matchedVar->grn_price;
                    }
                }

                // 2. Fallback to default product cost
                if ($itemUnitCost <= 0) {
                    $itemUnitCost = $defaultUnitCost;
                }

                $productCogs += round($itemQty * $itemUnitCost, 2);
            }

            // If no items sold yet, fallback display unit cost
            $displayUnitCost = $defaultUnitCost;
            if ($displayUnitCost <= 0 && $p->relationLoaded('variants') && $p->variants->isNotEmpty()) {
                $displayUnitCost = (float) ($p->variants->firstWhere('grn_price', '>', 0)?->grn_price ?? 0);
            }

            $netProfit = round($income - $productCogs, 2);
            $margin = $income > 0 ? round(($netProfit / $income) * 100, 1) : 0.0;

            // Product image resolution
            $imageSrc = null;
            if (is_array($p->images) && !empty($p->images)) {
                $imageSrc = $p->images[0];
            }

            // Categories string
            $categoryNames = $p->categories->map(function ($cat) {
                return $cat->parent ? "{$cat->parent->name} > {$cat->name}" : $cat->name;
            })->join(', ');

            $productRows[] = [
                'id' => $p->id,
                'name' => $p->name,
                'product_code' => $p->product_code,
                'image' => $imageSrc,
                'categories' => $categoryNames ?: 'Uncategorized',
                'brand' => $p->brand?->name,
                'unit_cost' => $displayUnitCost ?: $defaultUnitCost,
                'selling_price' => $sellingPrice,
                'total_stock' => $fullCount,
                'available_stock' => $availableQty,
                'sold_stock' => $soldQty,
                'grn_value' => $grnValue,
                'period_grn_value' => $itemPeriodGrn,
                'gained_income' => $income,
                'cogs' => $productCogs,
                'net_profit' => $netProfit,
                'margin' => $margin,
                'is_profitable' => $netProfit >= 0,
            ];

            // Summary accumulators
            $totalGrnValue += $grnValue;
            $periodTotalGrn += $itemPeriodGrn;
            $totalStockCount += $fullCount;
            $availableStockCount += $availableQty;
            $soldStockCount += $soldQty;
            $gainedIncome += $income;
            $totalCogs += $productCogs;
        }

        $overallNetProfit = round($gainedIncome - $totalCogs, 2);
        $overallMargin = $gainedIncome > 0 ? round(($overallNetProfit / $gainedIncome) * 100, 1) : 0.0;
        $effectiveGrnVal = ($period !== 'all' && $periodGrnAggregates !== null) ? round($periodTotalGrn, 2) : round($totalGrnValue, 2);
        $cashFlowNet = round($gainedIncome - $effectiveGrnVal, 2);

        return [
            'filter_meta' => [
                'category_id' => $categoryId,
                'subcategory_id' => $subCategoryId,
                'product_id' => $productId,
                'period' => $period,
                'period_label' => $periodLabel,
                'start_date' => $startDateTime?->toDateString(),
                'end_date' => $endDateTime?->toDateString(),
                'category_name' => $category?->name,
                'subcategory_name' => $subcategory?->name,
                'product_name' => $filteredProduct?->name,
                'description' => $filterDescription,
            ],
            'summary' => [
                'total_grn_value' => round($totalGrnValue, 2),
                'period_grn_value' => round($periodTotalGrn, 2),
                'display_grn_value' => $effectiveGrnVal,
                'is_period_filtered' => $period !== 'all',
                'products_count' => count($productRows),
                'total_stock_count' => $totalStockCount,
                'available_stock_count' => $availableStockCount,
                'sold_stock_count' => $soldStockCount,
                'gained_income' => round($gainedIncome, 2),
                'cogs' => round($totalCogs, 2),
                'net_profit' => $overallNetProfit,
                'profit_margin' => $overallMargin,
                'is_profitable' => $overallNetProfit >= 0,
                'cash_flow_net' => $cashFlowNet,
            ],
            'products' => $productRows,
        ];
    }
}
