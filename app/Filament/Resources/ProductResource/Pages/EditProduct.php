<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource\ProductResource;
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
                    'id' => $variant->id,
                    'color_name' => $variant->color_name,
                    'variant_name' => $variant->variant_name ?? "{$this->record->name} - {$variant->color_name}",
                    'image' => $variant->image,
                    'condition' => $variant->condition ?? 'Brand New',
                    'inventory_stock' => $variant->inventory_stock ?? 0,
                    'show_quantity' => $variant->show_quantity ?? $variant->stock ?? 0,
                    'grn_price' => $variant->grn_price,
                    'price' => $variant->price,
                    'discount_percentage' => $variant->discount_percentage ?? 0,
                    'sale_price' => $variant->sale_price,
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
            $totalShow = array_sum(array_map(fn ($v) => (int) ($v['show_quantity'] ?? 0), $variants));
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

            $oldShow = (int) ($this->record->show_quantity ?? $this->record->quantity ?? 0);
            $diff = $totalShow - $oldShow;
            $currentAvailable = (int) ($this->record->quantity ?? 0);
            $newAvailable = max(0, min($totalShow, $currentAvailable + $diff));

            $data['type'] = 'variable';
            $data['inventory_stock'] = $totalInv;
            $data['show_quantity'] = $totalShow;
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

            if (isset($data['show_quantity'])) {
                $newShow = (int) $data['show_quantity'];
                $oldShow = (int) ($this->record->show_quantity ?? $this->record->quantity ?? 0);
                $diff = $newShow - $oldShow;

                $currentAvailable = (int) ($this->record->quantity ?? 0);
                $data['quantity'] = max(0, min($newShow, $currentAvailable + $diff));
            }

            $code = $data['product_code'] ?? $this->record->product_code;
            if ($code) {
                $data['inventory_stock'] = (int) \App\Models\GrnItem::where('product_code', $code)->sum('product_quantity');
            }

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

                $variantData = [
                    'color_name' => $v['color_name'],
                    'variant_name' => $v['variant_name'] ?? "{$this->record->name} - {$v['color_name']}",
                    'attributes' => ['color' => $v['color_name']],
                    'image' => $image,
                    'condition' => $v['condition'] ?? 'Brand New',
                    'inventory_stock' => (int) ($v['inventory_stock'] ?? 0),
                    'show_quantity' => (int) ($v['show_quantity'] ?? 0),
                    'stock' => (int) ($v['show_quantity'] ?? 0),
                    'grn_price' => ! empty($v['grn_price']) ? (float) $v['grn_price'] : null,
                    'price' => (float) ($v['price'] ?? 0),
                    'discount_percentage' => (int) ($v['discount_percentage'] ?? 0),
                    'sale_price' => ! empty($v['sale_price']) ? (float) $v['sale_price'] : null,
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
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
