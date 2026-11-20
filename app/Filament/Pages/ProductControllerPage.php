<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\ProductControllerExcelService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\WithFileUploads;

class ProductControllerPage extends Page
{
    use WithFileUploads;

    protected static ?string $navigationGroup = 'Product Controller';

    protected static ?string $navigationLabel = 'Bulk Price & Excel Control';

    protected static ?string $title = 'Product Controller';

    protected static ?string $slug = 'product-controller';

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.product-controller';

    // Export Filters
    public ?int $categoryId = null;

    public ?int $subCategoryId = null;

    public ?int $supplierId = null;

    public string $visibility = 'visible'; // 'visible' | 'all' | 'hidden'

    // Upload & Preview State
    public $excelFile = null;

    public ?array $previewResult = null;

    public function updatedCategoryId(): void
    {
        $this->subCategoryId = null;
    }

    public function updatedExcelFile(): void
    {
        $this->previewResult = null;

        if (! $this->excelFile) {
            return;
        }

        $this->validate([
            'excelFile' => ['required', 'file', 'mimes:xlsx,csv', 'max:10240'],
        ], [
            'excelFile.mimes' => 'Please upload a valid .xlsx or .csv Excel file.',
            'excelFile.max' => 'File size must not exceed 10 MB.',
        ]);

        try {
            $ext = $this->excelFile->getClientOriginalExtension() ?: 'xlsx';
            $path = $this->excelFile->getRealPath();

            $this->previewResult = app(ProductControllerExcelService::class)->parseAndValidateUpload($path, $ext);

            if (! empty($this->previewResult['errors'])) {
                Notification::make()
                    ->title('Validation Issues Found')
                    ->body(count($this->previewResult['errors']) . ' row(s) had errors. Please review before applying.')
                    ->warning()
                    ->send();
            } elseif (empty($this->previewResult['changes'])) {
                Notification::make()
                    ->title('No Price Changes Detected')
                    ->body('All rows in the uploaded sheet match current database prices.')
                    ->info()
                    ->send();
            } else {
                Notification::make()
                    ->title('Excel Parsed Successfully')
                    ->body(count($this->previewResult['changes']) . ' product/variant price change(s) ready to apply.')
                    ->success()
                    ->send();
            }
        } catch (\Throwable $e) {
            report($e);
            $this->previewResult = null;
            $this->excelFile = null;

            Notification::make()
                ->title('Could Not Read Excel File')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function clearUpload(): void
    {
        $this->excelFile = null;
        $this->previewResult = null;
    }

    public function applyImportedChanges(): void
    {
        if (empty($this->previewResult['changes'])) {
            Notification::make()
                ->title('Nothing to Apply')
                ->warning()
                ->send();

            return;
        }

        if (! empty($this->previewResult['errors'])) {
            Notification::make()
                ->title('Cannot Apply While Errors Exist')
                ->body('Please fix the flagged rows in your Excel sheet and re-upload.')
                ->danger()
                ->send();

            return;
        }

        try {
            $count = app(ProductControllerExcelService::class)->applyChanges($this->previewResult['changes']);

            $this->excelFile = null;
            $this->previewResult = null;

            Notification::make()
                ->title('Prices & Website Synced!')
                ->body("Successfully updated {$count} product/variant record(s). Website & Admin prices are now live.")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            report($e);

            Notification::make()
                ->title('Failed to Apply Changes')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getViewData(): array
    {
        $categories = Category::whereNull('parent_id')->orderBy('name')->get(['id', 'name']);
        $subCategories = $this->categoryId
            ? Category::where('parent_id', $this->categoryId)->orderBy('name')->get(['id', 'name'])
            : Category::whereNotNull('parent_id')->orderBy('name')->get(['id', 'name']);
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);

        $countQuery = Product::query();
        if ($this->subCategoryId) {
            $countQuery->whereHas('categories', fn ($q) => $q->where('categories.id', $this->subCategoryId));
        } elseif ($this->categoryId) {
            $childIds = Category::where('parent_id', $this->categoryId)->pluck('id')->all();
            $countQuery->whereHas('categories', fn ($q) => $q->whereIn('categories.id', array_merge([$this->categoryId], $childIds)));
        }
        if ($this->supplierId) {
            $countQuery->where('supplier_id', $this->supplierId);
        }
        if ($this->visibility === 'visible') {
            $countQuery->where('is_visible', true);
        } elseif ($this->visibility === 'hidden') {
            $countQuery->where('is_visible', false);
        }
        $matchingCount = $countQuery->count();

        $exportUrl = route('admin.product-controller.export', array_filter([
            'category_id' => $this->categoryId,
            'subcategory_id' => $this->subCategoryId,
            'supplier_id' => $this->supplierId,
            'visibility' => $this->visibility,
        ]));

        return [
            'categories' => $categories,
            'subCategories' => $subCategories,
            'suppliers' => $suppliers,
            'matchingCount' => $matchingCount,
            'exportUrl' => $exportUrl,
        ];
    }
}
