<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\GrnItem;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProfitLossAnalysisService
{
    /**
     * Calculate comprehensive Profit & Loss metrics for filtered or all products.
     *
     * @param int|null $categoryId Parent Category ID
     * @param int|null $subCategoryId Subcategory ID
     * @param int|null $productId Specific Product ID
     * @return array
     */
    public function calculate(?int $categoryId = null, ?int $subCategoryId = null, ?int $productId = null): array
    {
        // 1. Resolve Filter Meta & Labels
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
        $filterDescription = empty($filterLabels) ? 'All Products (Store-Wide)' : implode(' > ', $filterLabels);

        // 2. Build Product Query
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

        // 3. Pre-aggregate GRN data (Quantity and Total Procurement Value per product_code)
        $grnAggregates = GrnItem::select(
            'product_code',
            DB::raw('SUM(product_quantity) as total_qty'),
            DB::raw('SUM(product_quantity * price_per_unit) as total_val')
        )
            ->whereNotNull('product_code')
            ->groupBy('product_code')
            ->get()
            ->keyBy('product_code');

        // 4. Pre-aggregate Paid Order Items (Quantity sold and Total Revenue per product)
        $soldItems = OrderItem::whereHas('order', fn ($q) => $q->where('payment_status', 'Paid'))
            ->select(
                'product_id',
                'product_name',
                DB::raw('SUM(quantity) as total_sold_qty'),
                DB::raw('SUM(total_price) as total_revenue')
            )
            ->groupBy('product_id', 'product_name')
            ->get();

        $soldByProductId = [];
        $soldByProductName = [];
        $revenueByProductId = [];
        $revenueByProductName = [];

        foreach ($soldItems as $sold) {
            if ($sold->product_id) {
                $soldByProductId[$sold->product_id] = ($soldByProductId[$sold->product_id] ?? 0) + (int) $sold->total_sold_qty;
                $revenueByProductId[$sold->product_id] = ($revenueByProductId[$sold->product_id] ?? 0.0) + (float) $sold->total_revenue;
            }
            if ($sold->product_name) {
                $soldByProductName[$sold->product_name] = ($soldByProductName[$sold->product_name] ?? 0) + (int) $sold->total_sold_qty;
                $revenueByProductName[$sold->product_name] = ($revenueByProductName[$sold->product_name] ?? 0.0) + (float) $sold->total_revenue;
            }
        }

        // 5. Compute metrics per product and accumulate totals
        $productRows = [];
        $totalGrnValue = 0.0;
        $totalStockCount = 0;
        $availableStockCount = 0;
        $soldStockCount = 0;
        $gainedIncome = 0.0;
        $totalCogs = 0.0;

        foreach ($products as $p) {
            // Stock counts
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

            // Sold count & revenue
            $soldQty = 0;
            $income = 0.0;

            if (isset($soldByProductId[$p->id])) {
                $soldQty = (int) $soldByProductId[$p->id];
                $income = (float) $revenueByProductId[$p->id];
            } elseif ($p->name && isset($soldByProductName[$p->name])) {
                $soldQty = (int) $soldByProductName[$p->name];
                $income = (float) $revenueByProductName[$p->name];
            }

            $availableQty = max(0, $fullCount - $soldQty);
            $sellingPrice = (float) ($p->sale_price ?: $p->price ?: 0);
            $unitCost = (float) ($p->grn_price ?: $p->cost_price ?: 0);
            if ($unitCost <= 0 && $p->product_code) {
                $unitCost = (float) (GrnItem::where('product_code', $p->product_code)->latest()->value('price_per_unit') ?? 0);
            }
            if ($unitCost <= 0 && $sellingPrice > 0) {
                $unitCost = round($sellingPrice * 0.75, 2);
            }

            // COGS & Net Profit for this product
            $productCogs = round($soldQty * $unitCost, 2);
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
                'unit_cost' => $unitCost,
                'selling_price' => $sellingPrice,
                'total_stock' => $fullCount,
                'available_stock' => $availableQty,
                'sold_stock' => $soldQty,
                'grn_value' => $grnValue,
                'gained_income' => $income,
                'cogs' => $productCogs,
                'net_profit' => $netProfit,
                'margin' => $margin,
                'is_profitable' => $netProfit >= 0,
            ];

            // Summary accumulators
            $totalGrnValue += $grnValue;
            $totalStockCount += $fullCount;
            $availableStockCount += $availableQty;
            $soldStockCount += $soldQty;
            $gainedIncome += $income;
            $totalCogs += $productCogs;
        }

        $overallNetProfit = round($gainedIncome - $totalCogs, 2);
        $overallMargin = $gainedIncome > 0 ? round(($overallNetProfit / $gainedIncome) * 100, 1) : 0.0;
        $cashFlowNet = round($gainedIncome - $totalGrnValue, 2);

        return [
            'filter_meta' => [
                'category_id' => $categoryId,
                'subcategory_id' => $subCategoryId,
                'product_id' => $productId,
                'category_name' => $category?->name,
                'subcategory_name' => $subcategory?->name,
                'product_name' => $filteredProduct?->name,
                'description' => $filterDescription,
            ],
            'summary' => [
                'total_grn_value' => round($totalGrnValue, 2),
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

