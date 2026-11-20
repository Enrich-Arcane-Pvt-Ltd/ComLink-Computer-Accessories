<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reports;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\ReportCenterService;
use Filament\Pages\Page;

abstract class BaseReportPage extends Page
{
    protected static ?string $navigationGroup = 'Reports';

    protected static string $view = 'filament.pages.report-center';

    public ?int $categoryId = null;

    public ?int $subCategoryId = null;

    public ?int $productId = null;

    public ?int $supplierId = null;

    public string $productType = 'all';

    public string $period = 'all';

    public ?string $startDate = null;

    public ?string $endDate = null;

    abstract public function getReportType(): string;

    public function updatedCategoryId(): void
    {
        $this->subCategoryId = null;
        $this->productId = null;
    }

    public function updatedSubCategoryId(): void
    {
        $this->productId = null;
    }

    public function updatedPeriod(): void
    {
        if ($this->period !== 'custom') {
            $this->startDate = null;
            $this->endDate = null;
        } else {
            $this->startDate ??= now()->subDays(30)->format('Y-m-d');
            $this->endDate ??= now()->format('Y-m-d');
        }
    }

    public function resetFilters(): void
    {
        $this->categoryId = null;
        $this->subCategoryId = null;
        $this->productId = null;
        $this->supplierId = null;
        $this->productType = 'all';
        $this->period = 'all';
        $this->startDate = null;
        $this->endDate = null;
    }

    protected function getViewData(): array
    {
        $filters = [
            'category_id' => $this->categoryId,
            'subcategory_id' => $this->subCategoryId,
            'product_id' => $this->productId,
            'supplier_id' => $this->supplierId,
            'product_type' => $this->productType,
            'period' => $this->period,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ];

        $report = app(ReportCenterService::class)->generate($this->getReportType(), $filters);

        $categories = Category::whereNull('parent_id')->orderBy('name')->get(['id', 'name']);
        $subCategories = $this->categoryId
            ? Category::where('parent_id', $this->categoryId)->orderBy('name')->get(['id', 'name'])
            : Category::whereNotNull('parent_id')->orderBy('name')->get(['id', 'name']);

        $productsQuery = Product::query()->orderBy('name');
        if ($this->subCategoryId) {
            $productsQuery->whereHas('categories', fn ($q) => $q->where('categories.id', $this->subCategoryId));
        } elseif ($this->categoryId) {
            $childIds = Category::where('parent_id', $this->categoryId)->pluck('id')->all();
            $productsQuery->whereHas('categories', fn ($q) => $q->whereIn('categories.id', array_merge([$this->categoryId], $childIds)));
        }
        if ($this->supplierId) {
            $productsQuery->where('supplier_id', $this->supplierId);
        }
        $products = $productsQuery->get(['id', 'name', 'product_code']);

        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);

        $exportQuery = array_filter([
            'category_id' => $this->categoryId,
            'subcategory_id' => $this->subCategoryId,
            'product_id' => $this->productId,
            'supplier_id' => $this->supplierId,
            'product_type' => $this->productType !== 'all' ? $this->productType : null,
            'period' => $this->period !== 'all' ? $this->period : null,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ]);

        return [
            'report' => $report,
            'categories' => $categories,
            'subCategories' => $subCategories,
            'products' => $products,
            'suppliers' => $suppliers,
            'pdfUrl' => route('admin.reports.pdf', array_merge(['type' => $this->getReportType()], $exportQuery)),
            'printUrl' => route('admin.reports.print', array_merge(['type' => $this->getReportType()], $exportQuery)),
        ];
    }
}
