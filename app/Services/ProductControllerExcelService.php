<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\InventoryProduct;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use OpenSpout\Writer\XLSX\Options as XlsxOptions;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;

class ProductControllerExcelService
{
    /**
     * Generate an .xlsx file for products (and their variants) matching the given filters.
     * Returns the absolute temporary file path.
     */
    public function exportToXlsx(array $filters = []): string
    {
        $query = Product::query()
            ->with(['categories.parent', 'supplier', 'variants'])
            ->orderBy('name');

        if (! empty($filters['subcategory_id'])) {
            $subId = (int) $filters['subcategory_id'];
            $query->whereHas('categories', fn ($q) => $q->where('categories.id', $subId));
        } elseif (! empty($filters['category_id'])) {
            $catId = (int) $filters['category_id'];
            $childIds = Category::where('parent_id', $catId)->pluck('id')->all();
            $query->whereHas('categories', fn ($q) => $q->whereIn('categories.id', array_merge([$catId], $childIds)));
        }

        if (! empty($filters['supplier_id'])) {
            $query->where('supplier_id', (int) $filters['supplier_id']);
        }

        if (($filters['visibility'] ?? 'visible') === 'visible') {
            $query->where('is_visible', true);
        } elseif (($filters['visibility'] ?? 'visible') === 'hidden') {
            $query->where('is_visible', false);
        }

        $products = $query->get();

        $tempPath = storage_path('app/temp_product_controller_' . uniqid() . '.xlsx');

        $options = new XlsxOptions();
        $options->setColumnWidth(12, 1);  // Product ID
        $options->setColumnWidth(12, 2);  // Variant ID
        $options->setColumnWidth(18, 3);  // Product Code
        $options->setColumnWidth(42, 4);  // Product Name
        $options->setColumnWidth(18, 5);  // Variant / Color
        $options->setColumnWidth(26, 6);  // Category
        $options->setColumnWidth(16, 7);  // Web Visible
        $options->setColumnWidth(16, 8);  // GRN Cost
        $options->setColumnWidth(18, 9);  // Original Price
        $options->setColumnWidth(18, 10); // Latest Price
        $options->setColumnWidth(15, 11); // Discount %

        $writer = new XlsxWriter($options);
        $writer->openToFile($tempPath);

        $headerStyle = (new Style())
            ->setFontBold()
            ->setFontColor(Color::WHITE)
            ->setBackgroundColor('0F172A');

        $writer->addRow(Row::fromValues([
            'Product ID',
            'Variant ID',
            'Product Code',
            'Product Name',
            'Variant / Color',
            'Category',
            'Web Visible (Yes/No)',
            'GRN Cost (Rs.)',
            'Original Price (Rs.)',
            'Latest Price (Rs.)',
            'Discount (%)',
        ], $headerStyle));

        $rowNum = 2;
        foreach ($products as $product) {
            $catNames = $product->categories
                ->map(fn ($c) => $c->parent ? "{$c->parent->name} > {$c->name}" : $c->name)
                ->join(', ') ?: 'Uncategorized';

            $visibleLabel = $product->is_visible ? 'Yes' : 'No';

            if ($product->variants && $product->variants->isNotEmpty()) {
                foreach ($product->variants as $variant) {
                    $orig = (float) ($variant->price ?: $product->price ?: 0);
                    $latest = ($variant->sale_price !== null && (float) $variant->sale_price > 0)
                        ? (float) $variant->sale_price
                        : $orig;
                    $cost = (float) ($variant->grn_price ?: $product->grn_price ?: $product->cost_price ?: 0);

                    $writer->addRow(Row::fromValues([
                        $product->id,
                        $variant->id,
                        $product->product_code ?: '',
                        $product->name,
                        $variant->color_name ?: 'Variant #' . $variant->id,
                        $catNames,
                        $visibleLabel,
                        round($cost, 2),
                        round($orig, 2),
                        round($latest, 2),
                        "=IF(I{$rowNum}>0,ROUND(((I{$rowNum}-J{$rowNum})/I{$rowNum})*100,0),0)",
                    ]));
                    $rowNum++;
                }
            } else {
                $orig = (float) ($product->price ?? 0);
                $latest = ($product->sale_price !== null && (float) $product->sale_price > 0)
                    ? (float) $product->sale_price
                    : $orig;
                $cost = (float) ($product->grn_price ?: $product->cost_price ?: 0);

                $writer->addRow(Row::fromValues([
                    $product->id,
                    '',
                    $product->product_code ?: '',
                    $product->name,
                    'Standard',
                    $catNames,
                    $visibleLabel,
                    round($cost, 2),
                    round($orig, 2),
                    round($latest, 2),
                    "=IF(I{$rowNum}>0,ROUND(((I{$rowNum}-J{$rowNum})/I{$rowNum})*100,0),0)",
                ]));
                $rowNum++;
            }
        }

        $writer->close();

        return $tempPath;
    }

    /**
     * Parse an uploaded .xlsx or .csv file, validate values, and return a diff preview.
     */
    public function parseAndValidateUpload(string $filePath, string $extension = 'xlsx'): array
    {
        $reader = strtolower($extension) === 'csv' ? new CsvReader() : new XlsxReader();
        $reader->open($filePath);

        $changes = [];
        $errors = [];
        $warnings = [];
        $unchangedCount = 0;
        $totalRows = 0;

        $productsById = Product::with('variants')->get()->keyBy('id');
        $variantsById = ProductVariant::all()->keyBy('id');

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                if ($rowIndex === 1) {
                    continue; // Skip header row
                }

                $cells = $row->toArray();
                if (empty(array_filter($cells, fn ($v) => $v !== null && $v !== ''))) {
                    continue; // Skip blank trailing rows
                }

                $totalRows++;
                $productId = isset($cells[0]) && is_numeric($cells[0]) ? (int) $cells[0] : null;
                $variantId = isset($cells[1]) && $cells[1] !== '' && is_numeric($cells[1]) ? (int) $cells[1] : null;
                $productCode = trim((string) ($cells[2] ?? ''));
                $visibleRaw = strtolower(trim((string) ($cells[6] ?? '')));
                $origRaw = $cells[8] ?? null;
                $latestRaw = $cells[9] ?? null;

                /** @var Product|null $product */
                $product = $productId ? $productsById->get($productId) : null;
                if (! $product && $productCode !== '') {
                    $product = $productsById->firstWhere('product_code', $productCode);
                }

                if (! $product) {
                    $errors[] = "Row {$rowIndex}: Product not found (ID: {$productId}, Code: {$productCode}).";
                    continue;
                }

                $variant = $variantId ? $variantsById->get($variantId) : null;
                if ($variantId && (! $variant || (int) $variant->product_id !== (int) $product->id)) {
                    $errors[] = "Row {$rowIndex}: Variant ID {$variantId} does not belong to '{$product->name}'.";
                    continue;
                }

                if (! is_numeric($origRaw) || ! is_numeric($latestRaw)) {
                    $errors[] = "Row {$rowIndex} ({$product->name}): Original Price and Latest Price must be valid numbers.";
                    continue;
                }

                $newOrig = round((float) $origRaw, 2);
                $newLatest = round((float) $latestRaw, 2);

                $newVisible = $product->is_visible;
                if (in_array($visibleRaw, ['yes', '1', 'true', 'active'])) {
                    $newVisible = true;
                } elseif (in_array($visibleRaw, ['no', '0', 'false', 'hidden'])) {
                    $newVisible = false;
                }

                $oldOrig = $variant
                    ? round((float) ($variant->price ?: $product->price ?: 0), 2)
                    : round((float) ($product->price ?? 0), 2);
                $oldLatest = $variant
                    ? round((float) (($variant->sale_price !== null && (float) $variant->sale_price > 0) ? $variant->sale_price : $oldOrig), 2)
                    : round((float) (($product->sale_price !== null && (float) $product->sale_price > 0) ? $product->sale_price : $oldOrig), 2);
                $oldVisible = (bool) $product->is_visible;

                $priceChanged = (abs($newOrig - $oldOrig) >= 0.01) || (abs($newLatest - $oldLatest) >= 0.01);
                $hasChanged = $priceChanged || ($newVisible !== $oldVisible);

                if (! $hasChanged) {
                    $unchangedCount++;
                    continue;
                }

                if ($newOrig < 0 || $newLatest < 0) {
                    $errors[] = "Row {$rowIndex} ({$product->name}): Prices cannot be negative.";
                    continue;
                }

                if ($priceChanged && ($newOrig <= 0 || $newLatest <= 0)) {
                    $errors[] = "Row {$rowIndex} ({$product->name}): Edited prices must be greater than 0.";
                    continue;
                }

                if ($newLatest > $newOrig) {
                    $errors[] = "Row {$rowIndex} ({$product->name}): Latest Price (Rs. {$newLatest}) cannot exceed Original Price (Rs. {$newOrig}).";
                    continue;
                }

                $oldDiscount = ($oldOrig > 0 && $oldLatest < $oldOrig)
                    ? (int) round((($oldOrig - $oldLatest) / $oldOrig) * 100)
                    : 0;
                $newDiscount = ($newOrig > 0 && $newLatest < $newOrig)
                    ? (int) round((($newOrig - $newLatest) / $newOrig) * 100)
                    : 0;

                $cost = $variant
                    ? (float) ($variant->grn_price ?: $product->grn_price ?: $product->cost_price ?: 0)
                    : (float) ($product->grn_price ?: $product->cost_price ?: 0);

                $isBelowCost = $cost > 0 && $newLatest < $cost;
                if ($isBelowCost) {
                    $warnings[] = "Row {$rowIndex} ({$product->name}): Latest Price (Rs. " . number_format($newLatest, 2) . ") is below GRN Cost (Rs. " . number_format($cost, 2) . ').';
                }

                $changes[] = [
                    'row' => $rowIndex,
                    'product_id' => $product->id,
                    'variant_id' => $variant?->id,
                    'product_code' => $product->product_code ?: '—',
                    'product_name' => $product->name,
                    'variant_name' => $variant?->color_name ?? 'Standard',
                    'grn_cost' => $cost,
                    'old_orig' => $oldOrig,
                    'new_orig' => $newOrig,
                    'old_latest' => $oldLatest,
                    'new_latest' => $newLatest,
                    'old_discount' => $oldDiscount,
                    'new_discount' => $newDiscount,
                    'old_visible' => $oldVisible,
                    'new_visible' => $newVisible,
                    'is_below_cost' => $isBelowCost,
                ];
            }
            break; // Only process first sheet
        }

        $reader->close();

        return [
            'total_rows' => $totalRows,
            'changes' => $changes,
            'warnings' => $warnings,
            'errors' => $errors,
            'unchanged_count' => $unchangedCount,
        ];
    }

    /**
     * Apply validated price & visibility changes to Products, ProductVariants, and InventoryProducts.
     */
    public function applyChanges(array $changes): int
    {
        if (empty($changes)) {
            return 0;
        }

        return DB::transaction(function () use ($changes) {
            $updatedCount = 0;
            $touchedProductIds = [];

            foreach ($changes as $change) {
                $product = Product::find($change['product_id']);
                if (! $product) {
                    continue;
                }

                $newOrig = (float) $change['new_orig'];
                $newLatest = (float) $change['new_latest'];
                $newDisc = (int) $change['new_discount'];
                $newVisible = (bool) $change['new_visible'];

                if (! empty($change['variant_id'])) {
                    $variant = ProductVariant::find($change['variant_id']);
                    if ($variant && (int) $variant->product_id === (int) $product->id) {
                        $variant->update([
                            'price' => $newOrig,
                            'sale_price' => $newLatest,
                            'discount_percentage' => $newDisc,
                        ]);
                        $updatedCount++;
                    }
                    if ($product->is_visible !== $newVisible) {
                        $product->update(['is_visible' => $newVisible]);
                    }
                    $touchedProductIds[$product->id] = true;
                } else {
                    $product->update([
                        'price' => $newOrig,
                        'sale_price' => $newLatest,
                        'is_visible' => $newVisible,
                    ]);
                    $updatedCount++;
                }
            }

            // Re-sync parent Product price / sale_price for any updated variant products
            foreach (array_keys($touchedProductIds) as $pid) {
                $prod = Product::with('variants')->find($pid);
                if (! $prod || $prod->variants->isEmpty()) {
                    continue;
                }

                $prod->syncFromFirstVariant();
            }

            return $updatedCount;
        });
    }
}
