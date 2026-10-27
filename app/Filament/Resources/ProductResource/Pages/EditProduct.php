<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource\ProductResource;
use App\Models\GrnItem;
use App\Models\InventoryProduct;
use App\Models\Supplier;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($this->record->hasColorVariants()) {
            $data['has_color_variants'] = true;
            $data['color_variants'] = $this->record->variants->map(function ($variant) {
                return [
                    'id'                  => $variant->id,
                    'color_name'          => $variant->color_name,
                    'variant_name'        => $variant->variant_name ?? "{$this->record->name} - {$variant->color_name}",
                    'image'               => $variant->image,
                    'condition'           => $variant->condition ?? 'Brand New',
                    'inventory_stock'     => $variant->inventory_stock ?? 0,
                    'show_quantity'       => $variant->show_quantity ?? $variant->stock ?? 0,
                    'grn_price'           => $variant->grn_price,
                    'price'               => $variant->price,
                    'discount_percentage' => $variant->discount_percentage ?? 0,
                    'sale_price'          => $variant->sale_price,
                ];
            })->toArray();
        } else {
            $data['has_color_variants'] = false;
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! empty($data['has_color_variants']) && ! empty($data['color_variants'])) {
            $variants = array_values(array_filter($data['color_variants'], fn ($v) => ! empty($v['color_name'])));
            $data['color_variants'] = $variants;

            $totalInv = array_sum(array_map(fn ($v) => (int) ($v['inventory_stock'] ?? 0), $variants));
            $prices = array_values(array_filter(array_map(fn ($v) => (float) ($v['price'] ?? 0), $variants)));
            $salePrices = array_values(array_filter(array_map(fn ($v) => (float) ($v['sale_price'] ?? 0), $variants)));
            $grnPrices = array_values(array_filter(array_map(fn ($v) => (float) ($v['grn_price'] ?? 0), $variants)));
            $images = array_values(array_filter(array_map(function ($v) {
                $img = $v['image'] ?? null;
                if (is_array($img)) {
                    $img = array_values(array_filter($img))[0] ?? null;
                }

                return is_string($img) && trim($img) !== '' ? trim($img) : null;
            }, $variants)));

            $minPrice = ! empty($prices) ? min($prices) : (float) ($data['price'] ?? 0);
            $minSale = ! empty($salePrices) ? min($salePrices) : $minPrice;

            $totalSold = (int) $this->record->sold_count;
            $newAvailable = max(0, $totalInv - $totalSold);

            $data['type'] = 'variable';
            $data['inventory_stock'] = $totalInv;
            $data['show_quantity'] = $totalInv;
            $data['quantity'] = $newAvailable;
            $data['price'] = $minPrice;
            $data['sale_price'] = $minSale;
            if (! empty($grnPrices)) {
                $data['grn_price'] = min($grnPrices);
            }
            if (! empty($images)) {
                $data['images'] = $images;
            }
            $data['condition'] = $variants[0]['condition'] ?? 'Brand New';
        } else {
            if (isset($data['images']) && is_array($data['images'])) {
                $data['images'] = array_values(array_filter($data['images']));
            }

            $code = $data['product_code'] ?? $this->record->product_code;
            $invStock = $code
                ? (int) GrnItem::where('product_code', $code)->sum('product_quantity')
                : (int) ($data['quantity'] ?? $this->record->quantity ?? 0);

            $sold = (int) $this->record->sold_count;
            $available = max(0, $invStock - $sold);

            $data['inventory_stock'] = $invStock;
            $data['show_quantity'] = $invStock;
            $data['quantity'] = $available;
            $data['type'] = 'simple';
        }

        if (empty($this->record->stock_added_count)) {
            $data['stock_added_count'] = (int) ($data['show_quantity'] ?? $data['quantity'] ?? 0);
            $data['stock_added_date'] = $this->record->created_at ?? now();
        }

        unset($data['image_1'], $data['image_2'], $data['image_3'], $data['image_4'], $data['discount_percentage']);

        return $data;
    }

    protected function afterSave(): void
    {
        $categories = array_values(array_filter([
            $this->data['category_id'] ?? null,
            $this->data['sub_category_id'] ?? null,
        ]));

        if (! empty($categories)) {
            $this->record->categories()->sync($categories);
        }

        if (! empty($this->data['has_color_variants']) && ! empty($this->data['color_variants'])) {
            $existingIds = [];
            foreach ($this->data['color_variants'] as $v) {
                if (empty($v['color_name'])) {
                    continue;
                }

                $image = $v['image'] ?? null;
                if (is_array($image)) {
                    $image = array_values(array_filter($image))[0] ?? null;
                }
                $image = is_string($image) && trim($image) !== '' ? trim($image) : null;

                $vInv = (int) ($v['inventory_stock'] ?? 0);
                $vSold = ! empty($v['id'])
                    ? (int) \App\Models\OrderItem::where('variant_id', $v['id'])->whereHas('order', fn ($q) => $q->where('payment_status', 'Paid'))->sum('quantity')
                    : 0;
                $vAvail = max(0, $vInv - $vSold);

                $variantData = [
                    'color_name'          => $v['color_name'],
                    'variant_name'        => $v['variant_name'] ?? "{$this->record->name} - {$v['color_name']}",
                    'attributes'          => ['color' => $v['color_name']],
                    'image'               => $image,
                    'condition'           => $v['condition'] ?? 'Brand New',
                    'inventory_stock'     => $vInv,
                    'show_quantity'       => $vInv,
                    'stock'               => $vAvail,
                    'grn_price'           => ! empty($v['grn_price']) ? (float) $v['grn_price'] : null,
                    'price'               => (float) ($v['price'] ?? 0),
                    'discount_percentage' => (int) ($v['discount_percentage'] ?? 0),
                    'sale_price'          => ! empty($v['sale_price']) ? (float) $v['sale_price'] : null,
                ];

                if (! empty($v['id'])) {
                    $variant = $this->record->variants()->find($v['id']);
                    if ($variant) {
                        $variant->update($variantData);
                        $existingIds[] = $variant->id;

                        continue;
                    }
                }

                $newVariant = $this->record->variants()->create($variantData);
                $existingIds[] = $newVariant->id;
            }

            // Remove any variants that were deleted from the repeater
            $this->record->variants()->whereNotIn('id', $existingIds)->delete();
        } elseif (empty($this->data['has_color_variants'])) {
            // Switched back to standard product: remove variants
            $this->record->variants()->delete();
        }

        // Keep InventoryProduct in sync with storefront product
        $this->syncToInventoryProduct();
    }

    /**
     * Ensures the product's code is registered in inventory_products table.
     */
    protected function syncToInventoryProduct(): void
    {
        $productCode = $this->record->product_code;
        if (! $productCode) {
            return;
        }

        try {
            $images = $this->record->images ?? [];
            $productImage = ! empty($images) ? (is_array($images) ? $images[0] : $images) : null;

            $supplierId = InventoryProduct::where('product_code', $productCode)->value('supplier_id')
                ?? Supplier::first()?->id;

            InventoryProduct::firstOrCreate(
                ['product_code' => $productCode],
                [
                    'product_name'  => $this->record->name,
                    'supplier_id'   => $supplierId,
                    'product_image' => $productImage,
                ]
            );
        } catch (\Throwable $e) {
            // Non-critical
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
