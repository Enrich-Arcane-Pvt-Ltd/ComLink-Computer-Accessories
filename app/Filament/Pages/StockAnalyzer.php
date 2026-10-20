<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\Supplier;
use App\Services\StockAnalysisService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class StockAnalyzer extends Page
{
    protected static ?string $navigationGroup = 'Stock Management';

    protected static ?string $navigationLabel = 'Stock Analyzer';

    protected static ?string $title = 'Stock Analyzer';

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.stock-analyzer';

    // Interactive filters
    public string $period = 'all';

    public ?string $customStart = null;

    public ?string $customEnd = null;

    public ?int $supplierId = null;

    public ?int $categoryId = null;

    public string $search = '';

    // Breakdown Modal state
    public bool $showBreakdownModal = false;

    public ?array $activeBreakdown = null;

    public function mount(): void
    {
        $this->period = 'all';
    }

    public function applyPeriod(string $newPeriod): void
    {
        $this->period = $newPeriod;
        if ($newPeriod !== 'custom') {
            $this->customStart = null;
            $this->customEnd = null;
        }
    }

    public function resetFilters(): void
    {
        $this->period = 'all';
        $this->customStart = null;
        $this->customEnd = null;
        $this->supplierId = null;
        $this->categoryId = null;
        $this->search = '';

        Notification::make()
            ->title('Filters Reset')
            ->info()
            ->send();
    }

    public function openBreakdown(int $productId): void
    {
        $service = app(StockAnalysisService::class);
        $this->activeBreakdown = $service->getProductBreakdown($productId);
        $this->showBreakdownModal = true;
    }

    public function closeBreakdown(): void
    {
        $this->showBreakdownModal = false;
        $this->activeBreakdown = null;
    }

    public function getAnalysisDataProperty(): array
    {
        $service = app(StockAnalysisService::class);

        return $service->analyze(
            $this->period,
            $this->customStart,
            $this->customEnd,
            $this->supplierId,
            $this->categoryId,
            $this->search
        );
    }

    public function getSuppliersProperty()
    {
        return Supplier::orderBy('name')->pluck('name', 'id');
    }

    public function getCategoriesProperty()
    {
        return Category::orderBy('name')->pluck('name', 'id');
    }

    public function getPdfUrlProperty(): string
    {
        return route('admin.stock-analyzer.pdf', [
            'period' => $this->period,
            'start_date' => $this->customStart,
            'end_date' => $this->customEnd,
            'supplier_id' => $this->supplierId,
            'category_id' => $this->categoryId,
            'search' => $this->search,
        ]);
    }

    public function getPrintUrlProperty(): string
    {
        return route('admin.stock-analyzer.print', [
            'period' => $this->period,
            'start_date' => $this->customStart,
            'end_date' => $this->customEnd,
            'supplier_id' => $this->supplierId,
            'category_id' => $this->categoryId,
            'search' => $this->search,
        ]);
    }
}
