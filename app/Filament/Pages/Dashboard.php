<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\GrnItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'Dashboard';

    protected static string $view = 'filament.pages.dashboard';

    /**
     * Disable default Filament widgets so only our custom dashboard is displayed.
     */
    public function getWidgets(): array
    {
        return [];
    }

    /**
     * Pass computed metrics, chart series, action links, and recent records to the view.
     */
    protected function getViewData(): array
    {
        // 1. Metric Card 1: Our Store
        $categoriesCount = Category::whereNull('parent_id')->count();
        $subCategoriesCount = Category::whereNotNull('parent_id')->count();
        $productsCount = Product::count();

        // 2. Metric Card 2: Total Sales (Paid Orders Total)
        $totalSales = (float) Order::where('payment_status', 'Paid')->sum('total_amount');

        // 3. Metric Card 3: Orders Count
        $ordersCount = Order::count();
        $paidOrdersCount = Order::where('payment_status', 'Paid')->count();
        $pendingOrdersCount = Order::where('payment_status', '!=', 'Paid')->count();

        // 4. Metric Card 4: Expenses (All GRN Stock Items Total Value)
        $totalExpenses = (float) GrnItem::sum(DB::raw('product_quantity * price_per_unit'));

        // 5. Metric Card 5: Revenue (All-Time Sales Income)
        $totalRevenue = $totalSales;

        // 6. Metric Card 6: Net Profit / Loss
        // Calculate Cost of Goods Sold (COGS) for sold items
        $paidOrderItems = OrderItem::with(['product.categories.parent', 'order'])
            ->whereHas('order', fn ($q) => $q->where('payment_status', 'Paid'))
            ->get();

        $cogs = 0.0;
        foreach ($paidOrderItems as $item) {
            $unitCost = (float) ($item->product?->grn_price ?: $item->product?->cost_price ?: 0);
            $cogs += round($item->quantity * $unitCost, 2);
        }

        $netProfit = round($totalRevenue - $cogs, 2);
        $profitMargin = $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 1) : 0.0;

        // 7. Monthly Data for Charts (Last 6 Months)
        $monthlyLabels = [];
        $monthlyExpenses = [];
        $monthlyIncomes = [];
        $monthlySales = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthDate = Carbon::now()->subMonths($i);
            $start = $monthDate->copy()->startOfMonth();
            $end = $monthDate->copy()->endOfMonth();
            $label = $monthDate->format('M Y');

            $monthlyLabels[] = $label;

            // Monthly stock expense from GRNs
            $expense = (float) GrnItem::whereBetween('created_at', [$start, $end])
                ->sum(DB::raw('product_quantity * price_per_unit'));
            $monthlyExpenses[] = round($expense, 2);

            // Monthly sales income from paid orders
            $income = (float) Order::where('payment_status', 'Paid')
                ->whereBetween('created_at', [$start, $end])
                ->sum('total_amount');
            $monthlyIncomes[] = round($income, 2);
            $monthlySales[] = round($income, 2);
        }

        // 8. Category-Wise Selling Percentages for Donut / Pie Chart (Matching CategoryResource)
        $categorySales = [];
        foreach ($paidOrderItems as $item) {
            $catName = null;

            // 1. Check loaded product relations for Main Category (parent_id IS NULL)
            if ($item->product) {
                $main = $item->product->categories->firstWhere('parent_id', null);
                if ($main) {
                    $catName = $main->name;
                } else {
                    $first = $item->product->categories->first();
                    $catName = $first?->parent?->name ?: $first?->name;
                }
            }

            // 2. Lookup product by product_id if relation not resolved
            if (empty($catName) && ! empty($item->product_id)) {
                $p = Product::with('categories.parent')->find($item->product_id);
                $main = $p?->categories->firstWhere('parent_id', null);
                $catName = $main?->name ?: ($p?->categories->first()?->parent?->name ?: $p?->categories->first()?->name);
            }

            // 3. Lookup product by product_name
            if (empty($catName) && ! empty($item->product_name)) {
                $p = Product::with('categories.parent')->where('name', $item->product_name)->first();
                $main = $p?->categories->firstWhere('parent_id', null);
                $catName = $main?->name ?: ($p?->categories->first()?->parent?->name ?: $p?->categories->first()?->name);
            }

            // 4. Use category_name stored if valid and not legacy generic 'Hardware'
            if (empty($catName) && ! empty($item->category_name) && strcasecmp(trim($item->category_name), 'Hardware') !== 0) {
                $catName = trim($item->category_name);
            }

            // 5. Intelligent keyword fallback matching main categories in CategoryResource
            if (empty($catName)) {
                $pName = strtolower((string) $item->product_name);
                if (str_contains($pName, 'laptop') || str_contains($pName, 'bravo') || str_contains($pName, 'thinkpad') || str_contains($pName, 'al15')) {
                    $catName = 'Laptops';
                } elseif (str_contains($pName, 'camera') || str_contains($pName, 'cctv') || str_contains($pName, 'gncc')) {
                    $catName = 'Cameras';
                } elseif (str_contains($pName, 'mouse') || str_contains($pName, 'keyboard') || str_contains($pName, 'headset') || str_contains($pName, 'speaker')) {
                    $catName = 'Peripherals';
                } elseif (str_contains($pName, 'ryzen') || str_contains($pName, 'intel') || str_contains($pName, 'processor') || str_contains($pName, 'core')) {
                    $catName = 'PC Components';
                } else {
                    $catName = 'Accessories';
                }
            }

            if (! isset($categorySales[$catName])) {
                $categorySales[$catName] = 0.0;
            }
            $categorySales[$catName] += (float) ($item->total_price ?: ($item->quantity * $item->unit_price));
        }

        // If no paid order item category sales yet, fallback to product counts per category
        if (empty($categorySales)) {
            $topCats = Category::whereNull('parent_id')->withCount('products')->get();
            foreach ($topCats as $cat) {
                if ($cat->products_count > 0) {
                    $categorySales[$cat->name] = $cat->products_count;
                }
            }
        }

        // Prepare chart slices and percentages
        $catLabels = [];
        $catValues = [];
        $catPercentages = [];
        $totalCatAmount = array_sum($categorySales);

        arsort($categorySales);
        $topCategorySales = array_slice($categorySales, 0, 6, true);
        if (count($categorySales) > 6) {
            $otherSum = array_sum(array_slice($categorySales, 6));
            $topCategorySales['Other'] = $otherSum;
        }

        foreach ($topCategorySales as $name => $val) {
            $catLabels[] = $name;
            $catValues[] = round($val, 2);
            $pct = $totalCatAmount > 0 ? round(($val / $totalCatAmount) * 100, 1) : 0;
            $catPercentages[] = $pct;
        }

        // 9. Latest 6 Products with Category & Subcategory relationships
        $latestProducts = Product::with(['categories.parent', 'supplier'])
            ->latest('id')
            ->take(6)
            ->get();

        // 10. Latest 6 Orders
        $latestOrders = Order::with(['items', 'user'])
            ->latest('id')
            ->take(6)
            ->get();

        return [
            // Store Metrics
            'categoriesCount' => $categoriesCount,
            'subCategoriesCount' => $subCategoriesCount,
            'productsCount' => $productsCount,
            'totalSales' => $totalSales,
            'ordersCount' => $ordersCount,
            'paidOrdersCount' => $paidOrdersCount,
            'pendingOrdersCount' => $pendingOrdersCount,
            'totalExpenses' => $totalExpenses,
            'totalRevenue' => $totalRevenue,
            'cogs' => $cogs,
            'netProfit' => $netProfit,
            'profitMargin' => $profitMargin,
            'isProfitable' => $netProfit >= 0,

            // Chart Data
            'chartOne' => [
                'labels' => $monthlyLabels,
                'expenses' => $monthlyExpenses,
                'incomes' => $monthlyIncomes,
            ],
            'chartTwo' => [
                'labels' => $monthlyLabels,
                'sales' => $monthlySales,
            ],
            'chartThree' => [
                'labels' => $catLabels,
                'values' => $catValues,
                'percentages' => $catPercentages,
            ],

            // Action Buttons
            'inventoryProductsUrl' => url('/admin/inventory-products'),
            'createGrnUrl' => url('/admin/grns/create'),
            'createProductUrl' => url('/admin/product-resource/products/create'),

            // Section Records
            'latestProducts' => $latestProducts,
            'latestOrders' => $latestOrders,
            'allProductsUrl' => url('/admin/product-resource/products'),
            'allOrdersUrl' => url('/admin/order-resource/orders'),
        ];
    }
}
