<?php

namespace App\Filament\Resources\SalesInfoResource\Widgets;

use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class SalesInfoOverviewWidget extends Widget
{
    protected static string $view = 'filament.widgets.sales-info-overview-widget';

    protected int | string | array $columnSpan = 'full';

    public string $period = 'monthly'; // 'daily', 'weekly', 'monthly', 'annually', 'custom'

    public ?string $startDate = null;

    public ?string $endDate = null;

    public bool $showCustomPicker = false;

    public function mount(): void
    {
        // Default to current month
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function setPeriod(string $period): void
    {
        $this->period = $period;

        if ($period === 'daily') {
            $this->startDate = now()->format('Y-m-d');
            $this->endDate = now()->format('Y-m-d');
            $this->showCustomPicker = false;
        } elseif ($period === 'weekly') {
            $this->startDate = now()->subDays(6)->format('Y-m-d');
            $this->endDate = now()->format('Y-m-d');
            $this->showCustomPicker = false;
        } elseif ($period === 'monthly') {
            $this->startDate = now()->startOfMonth()->format('Y-m-d');
            $this->endDate = now()->format('Y-m-d');
            $this->showCustomPicker = false;
        } elseif ($period === 'annually') {
            $this->startDate = now()->startOfYear()->format('Y-m-d');
            $this->endDate = now()->format('Y-m-d');
            $this->showCustomPicker = false;
        } elseif ($period === 'custom') {
            $this->showCustomPicker = true;
        }
    }

    public function toggleCustomPicker(): void
    {
        $this->showCustomPicker = ! $this->showCustomPicker;
        if ($this->showCustomPicker) {
            $this->period = 'custom';
        }
    }

    public function applyCustomRange(): void
    {
        $this->period = 'custom';
    }

    public function setQuickPreset(string $preset): void
    {
        $this->period = 'custom';
        $this->showCustomPicker = true;

        if ($preset === 'yesterday') {
            $this->startDate = now()->subDay()->format('Y-m-d');
            $this->endDate = now()->subDay()->format('Y-m-d');
        } elseif ($preset === 'last_7_days') {
            $this->startDate = now()->subDays(6)->format('Y-m-d');
            $this->endDate = now()->format('Y-m-d');
        } elseif ($preset === 'last_30_days') {
            $this->startDate = now()->subDays(29)->format('Y-m-d');
            $this->endDate = now()->format('Y-m-d');
        } elseif ($preset === 'last_month') {
            $this->startDate = now()->subMonth()->startOfMonth()->format('Y-m-d');
            $this->endDate = now()->subMonth()->endOfMonth()->format('Y-m-d');
        }
    }

    protected function getViewData(): array
    {
        $now = now();

        if ($this->period === 'daily') {
            $from = $now->copy()->startOfDay();
            $to = $now->copy()->endOfDay();
            $periodLabel = 'Today (' . $now->format('M d, Y') . ')';
            $daysCount = 1;
        } elseif ($this->period === 'weekly') {
            $from = $now->copy()->subDays(6)->startOfDay();
            $to = $now->copy()->endOfDay();
            $periodLabel = 'Past 7 Days (' . $from->format('M d') . ' – ' . $to->format('M d, Y') . ')';
            $daysCount = 7;
        } elseif ($this->period === 'monthly') {
            $from = $now->copy()->startOfMonth()->startOfDay();
            $to = $now->copy()->endOfMonth()->endOfDay();
            $periodLabel = 'This Month (' . $now->format('F Y') . ')';
            $daysCount = max(1, (int) round($from->diffInDays($to)) + 1);
        } elseif ($this->period === 'annually') {
            $from = $now->copy()->startOfYear()->startOfDay();
            $to = $now->copy()->endOfYear()->endOfDay();
            $periodLabel = 'This Year (' . $now->format('Y') . ')';
            $daysCount = max(1, (int) round($from->diffInDays($to)) + 1);
        } else {
            // Custom Range
            $from = $this->startDate ? Carbon::parse($this->startDate)->startOfDay() : $now->copy()->startOfMonth()->startOfDay();
            $to = $this->endDate ? Carbon::parse($this->endDate)->endOfDay() : $now->copy()->endOfDay();

            if ($from->gt($to)) {
                $temp = $from;
                $from = $to->copy()->startOfDay();
                $to = $temp->copy()->endOfDay();
            }

            $periodLabel = $from->format('M d, Y') . ' – ' . $to->format('M d, Y');
            $daysCount = max(1, (int) round($from->diffInDays($to)) + 1);
        }

        // Query paid orders in date range
        $ordersQuery = Order::query()
            ->with(['items'])
            ->where('payment_status', 'Paid')
            ->whereBetween('created_at', [$from, $to]);

        $orders = $ordersQuery->get();

        $totalRevenue = (float) $orders->sum('total_amount');
        $totalOrders = (int) $orders->count();
        $avgOrderValue = $totalOrders > 0 ? ($totalRevenue / $totalOrders) : 0;

        $orderIds = $orders->pluck('id');

        // Units and distinct products
        $totalUnits = 0;
        $distinctProductsCount = 0;
        if ($orderIds->isNotEmpty()) {
            $totalUnits = (int) OrderItem::whereIn('order_id', $orderIds)->sum('quantity');
            $distinctProductsCount = (int) OrderItem::whereIn('order_id', $orderIds)
                ->distinct('product_name')
                ->count('product_name');
        }

        // Fulfillment Breakdown
        $fulfillmentMap = [
            'Processing' => ['count' => 0, 'total' => 0.0, 'color' => '#38bdf8', 'bg' => 'rgba(56, 189, 248, 0.12)'],
            'Packaging'  => ['count' => 0, 'total' => 0.0, 'color' => '#fbbf24', 'bg' => 'rgba(251, 191, 36, 0.12)'],
            'Shipping'   => ['count' => 0, 'total' => 0.0, 'color' => '#818cf8', 'bg' => 'rgba(129, 140, 248, 0.12)'],
            'Delivered'  => ['count' => 0, 'total' => 0.0, 'color' => '#34d399', 'bg' => 'rgba(52, 211, 153, 0.12)'],
        ];

        foreach ($orders as $o) {
            $status = ucfirst(strtolower((string) $o->status));
            if (! isset($fulfillmentMap[$status])) {
                $status = 'Processing';
            }
            $fulfillmentMap[$status]['count']++;
            $fulfillmentMap[$status]['total'] += (float) $o->total_amount;
        }

        // Top Selling Products
        $topProducts = collect();
        if ($orderIds->isNotEmpty()) {
            $topProducts = OrderItem::whereIn('order_id', $orderIds)
                ->selectRaw('product_name, category_name, SUM(quantity) as total_qty, SUM(total_price) as total_revenue')
                ->groupBy('product_name', 'category_name')
                ->orderByDesc('total_qty')
                ->limit(5)
                ->get();
        }

        // Daily / Periodic Distribution Chart Bars
        $chartBars = [];
        if ($daysCount <= 31) {
            // Group by Day
            $groupedByDate = $orders->groupBy(fn ($o) => $o->created_at ? $o->created_at->format('Y-m-d') : '');

            $cursor = $from->copy();
            $maxRevenueInDay = 1;

            $tempBars = [];
            while ($cursor->lte($to)) {
                $dateKey = $cursor->format('Y-m-d');
                $dayOrders = $groupedByDate->get($dateKey, collect());
                $dayRev = (float) $dayOrders->sum('total_amount');
                if ($dayRev > $maxRevenueInDay) {
                    $maxRevenueInDay = $dayRev;
                }

                $tempBars[] = [
                    'label' => $cursor->format('d M'),
                    'full_date' => $cursor->format('M d, Y'),
                    'revenue' => $dayRev,
                    'orders_count' => $dayOrders->count(),
                ];
                $cursor->addDay();
            }

            foreach ($tempBars as $b) {
                $pct = $maxRevenueInDay > 0 ? round(($b['revenue'] / $maxRevenueInDay) * 100) : 0;
                $chartBars[] = array_merge($b, [
                    'height_pct' => max(10, $pct),
                    'is_active' => $b['revenue'] > 0,
                ]);
            }
        } else {
            // Group by Month
            $groupedByMonth = $orders->groupBy(fn ($o) => $o->created_at ? $o->created_at->format('Y-m') : '');

            $cursor = $from->copy()->startOfMonth();
            $maxRevenueInMonth = 1;

            $tempBars = [];
            while ($cursor->lte($to)) {
                $monthKey = $cursor->format('Y-m');
                $monthOrders = $groupedByMonth->get($monthKey, collect());
                $monthRev = (float) $monthOrders->sum('total_amount');
                if ($monthRev > $maxRevenueInMonth) {
                    $maxRevenueInMonth = $monthRev;
                }

                $tempBars[] = [
                    'label' => $cursor->format('M Y'),
                    'full_date' => $cursor->format('F Y'),
                    'revenue' => $monthRev,
                    'orders_count' => $monthOrders->count(),
                ];
                $cursor->addMonth();
            }

            foreach ($tempBars as $b) {
                $pct = $maxRevenueInMonth > 0 ? round(($b['revenue'] / $maxRevenueInMonth) * 100) : 0;
                $chartBars[] = array_merge($b, [
                    'height_pct' => max(10, $pct),
                    'is_active' => $b['revenue'] > 0,
                ]);
            }
        }

        return [
            'period' => $this->period,
            'periodLabel' => $periodLabel,
            'daysCount' => $daysCount,
            'from' => $from,
            'to' => $to,
            'totalRevenue' => $totalRevenue,
            'totalOrders' => $totalOrders,
            'avgOrderValue' => $avgOrderValue,
            'totalUnits' => $totalUnits,
            'distinctProductsCount' => $distinctProductsCount,
            'fulfillmentMap' => $fulfillmentMap,
            'topProducts' => $topProducts,
            'chartBars' => $chartBars,
        ];
    }
}
