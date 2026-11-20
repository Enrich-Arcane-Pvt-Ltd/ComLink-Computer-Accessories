<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\CustomerReturnItem;
use App\Models\GrnItem;
use App\Models\InventoryProduct;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\SupplierReturnItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportCenterService
{
    /**
     * Resolve time period start/end boundaries and human-readable label.
     */
    public function resolvePeriod(string $period, ?string $startDate = null, ?string $endDate = null): array
    {
        return match ($period) {
            'daily' => [
                'period' => 'daily',
                'start' => Carbon::today()->startOfDay(),
                'end' => Carbon::today()->endOfDay(),
                'label' => 'Daily (' . Carbon::today()->format('M d, Y') . ')',
            ],
            'weekly' => [
                'period' => 'weekly',
                'start' => Carbon::now()->subDays(6)->startOfDay(),
                'end' => Carbon::now()->endOfDay(),
                'label' => 'Weekly (' . Carbon::now()->subDays(6)->format('M d') . ' - ' . Carbon::now()->format('M d, Y') . ')',
            ],
            'monthly' => [
                'period' => 'monthly',
                'start' => Carbon::now()->startOfMonth(),
                'end' => Carbon::now()->endOfMonth(),
                'label' => 'Monthly (' . Carbon::now()->format('F Y') . ')',
            ],
            'annually' => [
                'period' => 'annually',
                'start' => Carbon::now()->startOfYear(),
                'end' => Carbon::now()->endOfYear(),
                'label' => 'Annually (' . Carbon::now()->format('Y') . ')',
            ],
            'custom' => ($startDate && $endDate) ? [
                'period' => 'custom',
                'start' => Carbon::parse($startDate)->startOfDay(),
                'end' => Carbon::parse($endDate)->endOfDay(),
                'label' => 'Custom (' . Carbon::parse($startDate)->format('M d, Y') . ' - ' . Carbon::parse($endDate)->format('M d, Y') . ')',
            ] : [
                'period' => 'all',
                'start' => null,
                'end' => null,
                'label' => 'All Time',
            ],
            default => [
                'period' => 'all',
                'start' => null,
                'end' => null,
                'label' => 'All Time',
            ],
        };
    }

    /**
     * Generate structured report data for any supported report type.
     */
    public function generate(string $reportType, array $filters = []): array
    {
        $categoryId = ! empty($filters['category_id']) ? (int) $filters['category_id'] : null;
        $subCategoryId = ! empty($filters['subcategory_id']) ? (int) $filters['subcategory_id'] : null;
        $productId = ! empty($filters['product_id']) ? (int) $filters['product_id'] : null;
        $supplierId = ! empty($filters['supplier_id']) ? (int) $filters['supplier_id'] : null;
        $productType = in_array($filters['product_type'] ?? 'all', ['all', 'sold', 'unsold']) ? $filters['product_type'] : 'all';
        $periodInfo = $this->resolvePeriod(
            (string) ($filters['period'] ?? 'all'),
            $filters['start_date'] ?? null,
            $filters['end_date'] ?? null
        );

        $filterDescription = $this->buildFilterDescription(
            $categoryId,
            $subCategoryId,
            $productId,
            $supplierId,
            $productType,
            $periodInfo['period'] !== 'all' ? $periodInfo['label'] : null
        );

        $ctx = [
            'category_id' => $categoryId,
            'subcategory_id' => $subCategoryId,
            'product_id' => $productId,
            'supplier_id' => $supplierId,
            'product_type' => $productType,
            'start' => $periodInfo['start'],
            'end' => $periodInfo['end'],
            'period' => $periodInfo['period'],
            'period_label' => $periodInfo['label'],
            'filter_description' => $filterDescription,
        ];

        return match ($reportType) {
            'inventory_products' => $this->buildInventoryProductsReport($ctx),
            'grn_analyzer' => $this->buildGrnAnalyzerReport($ctx),
            'inventory' => $this->buildInventoryReport($ctx),
            'stock_analyzer' => $this->buildStockAnalyzerReport($ctx),
            'orders' => $this->buildOrdersReport($ctx),
            'sales_info' => $this->buildSalesInfoReport($ctx),
            'profit_loss' => $this->buildProfitLossReport($ctx),
            'refunds_returns' => $this->buildRefundsReturnsReport($ctx),
            default => $this->buildInventoryReport($ctx),
        };
    }

    protected function buildFilterDescription(
        ?int $categoryId,
        ?int $subCategoryId,
        ?int $productId,
        ?int $supplierId,
        string $productType,
        ?string $periodLabel
    ): string {
        $parts = [];
        if ($categoryId && ($cat = Category::find($categoryId))) {
            $parts[] = 'Category: ' . $cat->name;
        }
        if ($subCategoryId && ($sub = Category::find($subCategoryId))) {
            $parts[] = 'Subcategory: ' . $sub->name;
        }
        if ($productId && ($prod = Product::find($productId))) {
            $parts[] = 'Product: ' . $prod->name;
        }
        if ($supplierId && ($sup = Supplier::find($supplierId))) {
            $parts[] = 'Supplier: ' . $sup->name;
        }
        if ($productType === 'sold') {
            $parts[] = 'Sold Products Only';
        } elseif ($productType === 'unsold') {
            $parts[] = 'Unsold Products Only';
        }
        if ($periodLabel) {
            $parts[] = 'Period: ' . $periodLabel;
        }

        return empty($parts) ? 'All Records (Store-Wide)' : implode(' > ', $parts);
    }

    /**
     * Resolve matching Product IDs & Product Codes based on Category, Subcategory, Product, Supplier, and Sold/Unsold filters.
     */
    protected function getFilteredProducts(array $ctx): Collection
    {
        $query = Product::query()->with(['supplier', 'brand', 'categories.parent', 'variants'])->orderBy('name');

        if ($ctx['product_id']) {
            $query->where('id', $ctx['product_id']);
        } elseif ($ctx['subcategory_id']) {
            $query->whereHas('categories', fn ($q) => $q->where('categories.id', $ctx['subcategory_id']));
        } elseif ($ctx['category_id']) {
            $childIds = Category::where('parent_id', $ctx['category_id'])->pluck('id')->all();
            $query->whereHas('categories', fn ($q) => $q->whereIn('categories.id', array_merge([$ctx['category_id']], $childIds)));
        }

        if ($ctx['supplier_id']) {
            $query->where('supplier_id', $ctx['supplier_id']);
        }

        $products = $query->get();

        if ($ctx['product_type'] !== 'all') {
            $soldQuery = OrderItem::whereHas('order', function ($q) use ($ctx) {
                $q->where('payment_status', 'Paid');
                if ($ctx['start'] && $ctx['end']) {
                    $q->whereBetween('orders.created_at', [$ctx['start'], $ctx['end']]);
                }
            });
            $soldProductIds = (clone $soldQuery)->whereNotNull('product_id')->pluck('product_id')->unique()->all();
            $soldProductNames = (clone $soldQuery)->whereNull('product_id')->pluck('product_name')->unique()->all();

            $products = $products->filter(function (Product $p) use ($ctx, $soldProductIds, $soldProductNames) {
                $isSold = in_array($p->id, $soldProductIds) || in_array($p->name, $soldProductNames);

                return $ctx['product_type'] === 'sold' ? $isSold : ! $isSold;
            })->values();
        }

        return $products;
    }

    /**
     * 1. Inventory Products Report (/admin/inventory-products)
     */
    protected function buildInventoryProductsReport(array $ctx): array
    {
        $filteredProducts = $this->getFilteredProducts($ctx);
        $allowedCodes = $filteredProducts->pluck('product_code')->filter()->unique()->all();

        $query = InventoryProduct::query()->with(['supplier'])->orderBy('product_name');

        if ($ctx['supplier_id']) {
            $query->where('supplier_id', $ctx['supplier_id']);
        }
        if ($ctx['category_id'] || $ctx['subcategory_id'] || $ctx['product_id'] || $ctx['product_type'] !== 'all') {
            $query->whereIn('product_code', $allowedCodes);
        }
        if ($ctx['start'] && $ctx['end']) {
            $query->whereBetween('created_at', [$ctx['start'], $ctx['end']]);
        }

        $items = $query->get();
        $supplierIds = [];
        $rows = [];

        foreach ($items as $item) {
            if ($item->supplier_id) {
                $supplierIds[$item->supplier_id] = true;
            }

            $rows[] = [
                'code' => $item->product_code ?: '—',
                'name' => $item->product_name,
                'supplier' => $item->supplier?->name ?? 'Unassigned',
                'condition' => $item->condition ?: 'Brand New',
            ];
        }

        return [
            'report_type' => 'inventory_products',
            'title' => 'Inventory Products Report',
            'subtitle' => 'Procurement catalog, supplier mapping & product conditions',
            'filter_description' => $ctx['filter_description'],
            'summary_cards' => [
                ['label' => 'Catalog Items', 'value' => number_format(count($rows)), 'tone' => 'slate'],
                ['label' => 'Active Suppliers', 'value' => number_format(count($supplierIds)), 'tone' => 'cyan'],
            ],
            'columns' => ['Code', 'Product Name', 'Supplier', 'Condition'],
            'rows' => $rows,
        ];
    }

    /**
     * 2. GRN Analyzer Report (/admin/grn-analyzer)
     */
    protected function buildGrnAnalyzerReport(array $ctx): array
    {
        $filteredProducts = $this->getFilteredProducts($ctx);
        $allowedCodes = $filteredProducts->pluck('product_code')->filter()->unique()->all();

        $query = GrnItem::query()->with(['grn.supplier'])->orderByDesc('created_at');

        if ($ctx['supplier_id']) {
            $query->whereHas('grn', fn ($q) => $q->where('supplier_id', $ctx['supplier_id']));
        }
        if ($ctx['category_id'] || $ctx['subcategory_id'] || $ctx['product_id'] || $ctx['product_type'] !== 'all') {
            $query->whereIn('product_code', $allowedCodes);
        }
        if ($ctx['start'] && $ctx['end']) {
            $query->whereBetween('created_at', [$ctx['start'], $ctx['end']]);
        }

        $items = $query->get();
        $totalQty = 0;
        $totalVal = 0.0;
        $grnIds = [];
        $rows = [];

        foreach ($items as $item) {
            $qty = (int) $item->product_quantity;
            $lineTotal = (float) $item->total;
            $totalQty += $qty;
            $totalVal += $lineTotal;
            if ($item->grn_id) {
                $grnIds[$item->grn_id] = true;
            }

            $rows[] = [
                'grn_no' => $item->grn?->grn_number ?? '—',
                'date' => $item->created_at ? $item->created_at->format('M d, Y') : '—',
                'supplier' => $item->grn?->supplier?->name ?? 'Unknown',
                'product' => $item->product_name . ($item->product_code ? " ({$item->product_code})" : ''),
                'condition' => $item->product_condition ?: 'Brand New',
                'qty' => number_format($qty),
                'unit_price' => 'Rs. ' . number_format((float) $item->price_per_unit, 2),
                'total' => 'Rs. ' . number_format($lineTotal, 2),
            ];
        }

        return [
            'report_type' => 'grn_analyzer',
            'title' => 'GRN Analyzer Report',
            'subtitle' => 'Goods Received Notes intake, supplier batches & procurement expenditure',
            'filter_description' => $ctx['filter_description'],
            'summary_cards' => [
                ['label' => 'GRN Batches', 'value' => number_format(count($grnIds)), 'tone' => 'slate'],
                ['label' => 'Line Items', 'value' => number_format(count($rows)), 'tone' => 'blue'],
                ['label' => 'Received Units', 'value' => number_format($totalQty) . ' units', 'tone' => 'cyan'],
                ['label' => 'Total GRN Spend', 'value' => 'Rs. ' . number_format($totalVal, 2), 'tone' => 'violet'],
            ],
            'columns' => ['GRN No', 'Date', 'Supplier', 'Product', 'Condition', 'Qty', 'Unit Price', 'Line Total'],
            'rows' => $rows,
        ];
    }

    /**
     * 3. Inventory Master Report (/admin/inventory)
     */
    protected function buildInventoryReport(array $ctx): array
    {
        $products = $this->getFilteredProducts($ctx);
        $totalFull = 0;
        $totalAvail = 0;
        $totalSold = 0;
        $totalDamaged = 0;
        $totalValue = 0.0;
        $rows = [];

        foreach ($products as $p) {
            if ($ctx['start'] && $ctx['end'] && ($p->created_at < $ctx['start'] || $p->created_at > $ctx['end'])) {
                continue;
            }
            $full = (int) $p->inventory_full_count;
            $avail = (int) $p->current_inventory_stock;
            $sold = (int) $p->net_sold_count;
            $dmg = (int) $p->damaged_count;
            $cost = (float) ($p->grn_price ?: $p->cost_price ?: 0);
            $val = round($avail * $cost, 2);

            $totalFull += $full;
            $totalAvail += $avail;
            $totalSold += $sold;
            $totalDamaged += $dmg;
            $totalValue += $val;

            $catNames = $p->categories->map(fn ($c) => $c->parent ? "{$c->parent->name} > {$c->name}" : $c->name)->join(', ');

            $rows[] = [
                'code' => $p->product_code ?: '—',
                'name' => $p->name,
                'category' => $catNames ?: 'Uncategorized',
                'supplier' => $p->supplier?->name ?? '—',
                'full' => number_format($full),
                'avail' => number_format($avail),
                'sold' => number_format($sold),
                'damaged' => number_format($dmg),
                'retail_price' => 'Rs. ' . number_format((float) ($p->sale_price ?: $p->price ?: 0), 2),
                'avail_value' => 'Rs. ' . number_format($val, 2),
            ];
        }

        return [
            'report_type' => 'inventory',
            'title' => 'Inventory Master Report',
            'subtitle' => 'Complete store-wide product inventory, availability & damaged quarantine',
            'filter_description' => $ctx['filter_description'],
            'summary_cards' => [
                ['label' => 'Products', 'value' => number_format(count($rows)), 'tone' => 'slate'],
                ['label' => 'Available Stock', 'value' => number_format($totalAvail) . ' units', 'tone' => 'green'],
                ['label' => 'Net Sold', 'value' => number_format($totalSold) . ' units', 'tone' => 'blue'],
                ['label' => 'Damaged Stock', 'value' => number_format($totalDamaged) . ' units', 'tone' => 'red'],
                ['label' => 'Available Valuation', 'value' => 'Rs. ' . number_format($totalValue, 2), 'tone' => 'violet'],
            ],
            'columns' => ['Code', 'Product', 'Category', 'Supplier', 'Full', 'Available', 'Sold', 'Damaged', 'Selling Price', 'Avail. Cost Value'],
            'rows' => $rows,
        ];
    }

    /**
     * 4. Stock Analyzer Report (/admin/stock-analyzer)
     */
    protected function buildStockAnalyzerReport(array $ctx): array
    {
        $pl = app(ProfitLossAnalysisService::class)->calculate(
            $ctx['category_id'],
            $ctx['subcategory_id'],
            $ctx['product_id'],
            $ctx['period'],
            $ctx['start']?->toDateString(),
            $ctx['end']?->toDateString(),
            $ctx['product_type']
        );

        $allowedIds = $this->getFilteredProducts($ctx)->pluck('id')->all();
        $rows = [];
        $totStock = 0;
        $totAvail = 0;
        $totSold = 0;
        $totDmg = 0;
        $totGrn = 0.0;
        $totRev = 0.0;

        foreach ($pl['products'] as $item) {
            if ($ctx['supplier_id'] && ! in_array($item['id'], $allowedIds)) {
                continue;
            }
            $totStock += $item['total_stock'];
            $totAvail += $item['available_stock'];
            $totSold += $item['sold_stock'];
            $totDmg += $item['damaged_stock'] ?? 0;
            $totGrn += $item['grn_value'];
            $totRev += $item['gained_income'];

            $rows[] = [
                'product' => $item['name'] . ($item['product_code'] ? " ({$item['product_code']})" : ''),
                'category' => $item['categories'],
                'total' => number_format($item['total_stock']),
                'avail' => number_format($item['available_stock']),
                'sold' => number_format($item['sold_stock']),
                'damaged' => number_format($item['damaged_stock'] ?? 0),
                'unit_cost' => 'Rs. ' . number_format($item['unit_cost'], 2),
                'grn_val' => 'Rs. ' . number_format($item['grn_value'], 2),
                'revenue' => 'Rs. ' . number_format($item['gained_income'], 2),
            ];
        }

        return [
            'report_type' => 'stock_analyzer',
            'title' => 'Stock Analyzer Report',
            'subtitle' => 'Stock movement breakdown, sell-through & cost allocation',
            'filter_description' => $ctx['filter_description'],
            'summary_cards' => [
                ['label' => 'Products Analyzed', 'value' => number_format(count($rows)), 'tone' => 'slate'],
                ['label' => 'Total Stock', 'value' => number_format($totStock) . ' units', 'tone' => 'cyan'],
                ['label' => 'Available / Sold / Dmg', 'value' => "{$totAvail} / {$totSold} / {$totDmg}", 'tone' => 'green'],
                ['label' => 'Net GRN Valuation', 'value' => 'Rs. ' . number_format($totGrn, 2), 'tone' => 'violet'],
                ['label' => 'Realized Revenue', 'value' => 'Rs. ' . number_format($totRev, 2), 'tone' => 'blue'],
            ],
            'columns' => ['Product', 'Category', 'Total', 'Avail', 'Sold', 'Damaged', 'Unit Cost', 'GRN Value', 'Realized Revenue'],
            'rows' => $rows,
        ];
    }

    /**
     * 5. Orders Report (/admin/order-resource/orders)
     */
    protected function buildOrdersReport(array $ctx, bool $paidOnly = false): array
    {
        $filteredProducts = $this->getFilteredProducts($ctx);
        $allowedIds = $filteredProducts->pluck('id')->all();
        $allowedNames = $filteredProducts->pluck('name')->all();
        $hasProductFilter = (bool) ($ctx['category_id'] || $ctx['subcategory_id'] || $ctx['product_id'] || $ctx['supplier_id'] || $ctx['product_type'] !== 'all');

        $query = Order::query()->with(['items', 'user'])->orderByDesc('id');

        if ($paidOnly) {
            $query->where('payment_status', 'Paid');
        }
        if ($ctx['start'] && $ctx['end']) {
            $query->whereBetween('created_at', [$ctx['start'], $ctx['end']]);
        }
        if ($hasProductFilter) {
            $query->whereHas('items', function ($q) use ($allowedIds, $allowedNames) {
                $q->whereIn('product_id', $allowedIds)->orWhereIn('product_name', $allowedNames);
            });
        }

        $orders = $query->get();
        $grossTotal = 0.0;
        $refundedTotal = 0.0;
        $netTotal = 0.0;
        $totalUnits = 0;
        $rows = [];

        foreach ($orders as $o) {
            $units = (int) $o->items->sum('quantity');
            $gross = (float) $o->total_amount;
            $ref = (float) ($o->refunded_amount ?? 0);
            $net = max(0.0, $gross - $ref);

            $grossTotal += $gross;
            $refundedTotal += $ref;
            $netTotal += $net;
            $totalUnits += $units;

            $rows[] = [
                'order_no' => $o->order_number,
                'date' => $o->created_at ? $o->created_at->format('M d, Y h:i A') : '—',
                'customer' => $o->customer_name ?: ($o->user?->name ?? 'Guest'),
                'units' => number_format($units),
                'payment' => ($o->payment_status ?: 'Pending') . ' (' . ($o->payment_method ?: 'Card') . ')',
                'status' => $o->status ?: 'Processing',
                'gross' => 'Rs. ' . number_format($gross, 2),
                'refunded' => 'Rs. ' . number_format($ref, 2),
                'net' => 'Rs. ' . number_format($net, 2),
            ];
        }

        return [
            'report_type' => $paidOnly ? 'sales_info' : 'orders',
            'title' => $paidOnly ? 'Sales Info Report' : 'Orders Report',
            'subtitle' => $paidOnly ? 'Verified paid sales orders, refunds & net revenue' : 'All customer orders, fulfillment statuses & net totals',
            'filter_description' => $ctx['filter_description'],
            'summary_cards' => [
                ['label' => $paidOnly ? 'Paid Orders' : 'Total Orders', 'value' => number_format(count($rows)), 'tone' => 'slate'],
                ['label' => 'Total Units', 'value' => number_format($totalUnits) . ' units', 'tone' => 'cyan'],
                ['label' => 'Gross Amount', 'value' => 'Rs. ' . number_format($grossTotal, 2), 'tone' => 'blue'],
                ['label' => 'Refunded', 'value' => 'Rs. ' . number_format($refundedTotal, 2), 'tone' => 'red'],
                ['label' => 'Net Revenue', 'value' => 'Rs. ' . number_format($netTotal, 2), 'tone' => 'green'],
            ],
            'columns' => ['Order No', 'Placed At', 'Customer', 'Units', 'Payment', 'Order Status', 'Gross Total', 'Refunded', 'Net Total'],
            'rows' => $rows,
        ];
    }

    /**
     * 6. Sales Info Report (/admin/sales-section/sales-info)
     */
    protected function buildSalesInfoReport(array $ctx): array
    {
        return $this->buildOrdersReport($ctx, true);
    }

    /**
     * 7. Profit / Loss Analysis Report
     */
    protected function buildProfitLossReport(array $ctx): array
    {
        $pl = app(ProfitLossAnalysisService::class)->calculate(
            $ctx['category_id'],
            $ctx['subcategory_id'],
            $ctx['product_id'],
            $ctx['period'],
            $ctx['start']?->toDateString(),
            $ctx['end']?->toDateString(),
            $ctx['product_type']
        );

        $allowedIds = $this->getFilteredProducts($ctx)->pluck('id')->all();
        $rows = [];
        $totGrn = 0.0;
        $totIncome = 0.0;
        $totCogs = 0.0;
        $totDmgLoss = 0.0;
        $totProfit = 0.0;

        foreach ($pl['products'] as $item) {
            if ($ctx['supplier_id'] && ! in_array($item['id'], $allowedIds)) {
                continue;
            }
            $totGrn += $item['grn_value'];
            $totIncome += $item['gained_income'];
            $totCogs += $item['cogs'];
            $totDmgLoss += $item['damaged_loss'] ?? 0;
            $totProfit += $item['net_profit'];

            $rows[] = [
                'product' => $item['name'] . ($item['product_code'] ? " ({$item['product_code']})" : ''),
                'category' => $item['categories'],
                'stock' => "{$item['total_stock']}T / {$item['available_stock']}A / {$item['sold_stock']}S" . (($item['damaged_stock'] ?? 0) > 0 ? " / {$item['damaged_stock']}D" : ''),
                'unit_cost' => 'Rs. ' . number_format($item['unit_cost'], 2),
                'grn' => 'Rs. ' . number_format($item['grn_value'], 2),
                'income' => 'Rs. ' . number_format($item['gained_income'], 2),
                'cogs_loss' => 'Rs. ' . number_format($item['cogs'] + ($item['damaged_loss'] ?? 0), 2),
                'net_profit' => ($item['net_profit'] >= 0 ? '+' : '') . 'Rs. ' . number_format($item['net_profit'], 2) . " ({$item['margin']}%)",
            ];
        }

        return [
            'report_type' => 'profit_loss',
            'title' => 'Profit / Loss Analysis Report',
            'subtitle' => 'Comprehensive GRN procurement, net sales, COGS, damaged write-offs & net profit',
            'filter_description' => $ctx['filter_description'],
            'summary_cards' => [
                ['label' => 'Products', 'value' => number_format(count($rows)), 'tone' => 'slate'],
                ['label' => 'Net GRN Value', 'value' => 'Rs. ' . number_format($totGrn, 2), 'tone' => 'violet'],
                ['label' => 'Net Gained Income', 'value' => 'Rs. ' . number_format($totIncome, 2), 'tone' => 'blue'],
                ['label' => 'COGS + Dmg Loss', 'value' => 'Rs. ' . number_format($totCogs + $totDmgLoss, 2), 'tone' => 'amber'],
                ['label' => 'Net Profit / Loss', 'value' => ($totProfit >= 0 ? '+' : '') . 'Rs. ' . number_format($totProfit, 2), 'tone' => $totProfit >= 0 ? 'green' : 'red'],
            ],
            'columns' => ['Product', 'Category', 'Stock (T/A/S/D)', 'Unit Cost', 'Net GRN', 'Gained Income', 'COGS & Loss', 'Net Profit / Loss'],
            'rows' => $rows,
        ];
    }

    /**
     * 8. Refunds & Returns Report (Customer Refunds + Damaged Stock + Supplier RMA)
     */
    protected function buildRefundsReturnsReport(array $ctx): array
    {
        $filteredProducts = $this->getFilteredProducts($ctx);
        $allowedIds = $filteredProducts->pluck('id')->all();
        $allowedNames = $filteredProducts->pluck('name')->all();
        $hasProductFilter = (bool) ($ctx['category_id'] || $ctx['subcategory_id'] || $ctx['product_id'] || $ctx['supplier_id'] || $ctx['product_type'] !== 'all');

        $custQuery = CustomerReturnItem::query()->with(['customerReturn.order', 'product.supplier'])->orderByDesc('id');
        if ($hasProductFilter) {
            $custQuery->where(fn ($q) => $q->whereIn('product_id', $allowedIds)->orWhereIn('product_name', $allowedNames));
        }
        if ($ctx['start'] && $ctx['end']) {
            $custQuery->whereBetween('created_at', [$ctx['start'], $ctx['end']]);
        }
        $custItems = $custQuery->get();

        $supQuery = SupplierReturnItem::query()->with(['supplierReturn.supplier', 'supplierReturn.grn'])->orderByDesc('id');
        if ($ctx['supplier_id']) {
            $supQuery->whereHas('supplierReturn', fn ($q) => $q->where('supplier_id', $ctx['supplier_id']));
        }
        if ($hasProductFilter) {
            $supQuery->where(fn ($q) => $q->whereIn('product_id', $allowedIds)->orWhereIn('product_name', $allowedNames));
        }
        if ($ctx['start'] && $ctx['end']) {
            $supQuery->whereBetween('created_at', [$ctx['start'], $ctx['end']]);
        }
        $supItems = $supQuery->get();

        $totalRefunded = 0.0;
        $totalDamagedUnits = 0;
        $totalSupplierCredit = 0.0;
        $rows = [];

        foreach ($custItems as $ci) {
            $cr = $ci->customerReturn;
            if (! $cr) {
                continue;
            }
            if ($cr->status === 'approved') {
                $totalRefunded += (float) $ci->refund_subtotal;
                if ($ci->condition === 'damaged' && in_array($ci->resolution_status, ['in_damaged_stock', 'sent_to_supplier'])) {
                    $totalDamagedUnits += (int) $ci->quantity;
                }
            }

            $rows[] = [
                'ref_no' => $cr->return_number,
                'type' => 'Customer Refund',
                'date' => $cr->created_at ? $cr->created_at->format('M d, Y') : '—',
                'partner' => ($cr->order?->customer_name ?? 'Customer') . ' (' . ($cr->order?->order_number ?? '—') . ')',
                'product' => $ci->product_name,
                'qty' => number_format((int) $ci->quantity),
                'status' => ucfirst($cr->status) . ' / ' . str($ci->resolution_status)->replace('_', ' ')->title(),
                'amount' => 'Rs. ' . number_format((float) $ci->refund_subtotal, 2),
            ];
        }

        foreach ($supItems as $si) {
            $sr = $si->supplierReturn;
            if (! $sr) {
                continue;
            }
            if ($sr->status === 'credited') {
                $totalSupplierCredit += (float) $si->total_cost;
            }

            $rows[] = [
                'ref_no' => $sr->rma_number,
                'type' => 'Supplier RMA',
                'date' => $sr->created_at ? $sr->created_at->format('M d, Y') : '—',
                'partner' => ($sr->supplier?->name ?? 'Supplier') . ($sr->grn ? " ({$sr->grn->grn_number})" : ''),
                'product' => $si->product_name,
                'qty' => number_format((int) $si->quantity),
                'status' => ucfirst($sr->status),
                'amount' => 'Rs. ' . number_format((float) $si->total_cost, 2),
            ];
        }

        return [
            'report_type' => 'refunds_returns',
            'title' => 'Refunds & Returns Report',
            'subtitle' => 'Customer refund requests, damaged inventory quarantine & supplier RMA resolutions',
            'filter_description' => $ctx['filter_description'],
            'summary_cards' => [
                ['label' => 'Total Records', 'value' => number_format(count($rows)), 'tone' => 'slate'],
                ['label' => 'Approved Refunds', 'value' => 'Rs. ' . number_format($totalRefunded, 2), 'tone' => 'red'],
                ['label' => 'Active Damaged Units', 'value' => number_format($totalDamagedUnits) . ' units', 'tone' => 'amber'],
                ['label' => 'Supplier Credits Recovered', 'value' => 'Rs. ' . number_format($totalSupplierCredit, 2), 'tone' => 'green'],
            ],
            'columns' => ['Ref No', 'Flow Type', 'Date', 'Customer / Supplier', 'Product', 'Qty', 'Status', 'Value'],
            'rows' => $rows,
        ];
    }
}
