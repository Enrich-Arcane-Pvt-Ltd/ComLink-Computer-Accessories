<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
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

            $minPrice = ! empty($prices) ? min($prices) : 0.0;
            $minSale = ! empty($salePrices) ? min($salePrices) : $minPrice;

            $data['type'] = 'variable';
            $data['inventory_stock'] = $totalInv;
            $data['show_quantity'] = $totalInv;
            $data['quantity'] = $totalInv;
            $data['stock_added_count'] = $totalInv;
            $data['stock_added_date'] = now();
            $data['price'] = $minPrice;
            $data['sale_price'] = $minSale;
            if (! empty($grnPrices)) {
                $data['grn_price'] = min($grnPrices);
            }
            $data['images'] = $images;
            $data['condition'] = $variants[0]['condition'] ?? 'Brand New';
        } else {
            if (isset($data['images']) && is_array($data['images'])) {
                $data['images'] = array_values(array_filter($data['images']));
            }

            $fullStock = (int) ($data['quantity'] ?? $data['inventory_stock'] ?? 0);
            $data['inventory_stock'] = $fullStock;
            $data['show_quantity'] = $fullStock;
            $data['quantity'] = $fullStock;
            $data['stock_added_count'] = $fullStock;
            $data['stock_added_date'] = now();
            $data['type'] = 'simple';
        }

        unset($data['image_1'], $data['image_2'], $data['image_3'], $data['image_4'], $data['discount_percentage']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $categories = array_values(array_filter([
            $this->data['category_id'] ?? null,
            $this->data['sub_category_id'] ?? null,
        ]));

        if (! empty($categories)) {
            $this->record->categories()->sync($categories);
        }

        if (! empty($this->data['has_color_variants']) && ! empty($this->data['color_variants'])) {
            foreach ($this->data['color_variants'] as $v) {
                if (empty($v['color_name'])) {
                    continue;
                }

                $image = $v['image'] ?? null;
                if (is_array($image)) {
                    $image = array_values(array_filter($image))[0] ?? null;
                }
                $image = is_string($image) && trim($image) !== '' ? trim($image) : null;

                $this->record->variants()->create([
                    'color_name' => $v['color_name'],
                    'variant_name' => $v['variant_name'] ?? "{$this->record->name} - {$v['color_name']}",
                    'attributes' => ['color' => $v['color_name']],
                    'image' => $image,
                    'condition' => $v['condition'] ?? 'Brand New',
                    'inventory_stock' => (int) ($v['inventory_stock'] ?? 0),
                    'show_quantity' => (int) ($v['inventory_stock'] ?? 0),
                    'stock' => (int) ($v['inventory_stock'] ?? 0),
                    'grn_price' => ! empty($v['grn_price']) ? (float) $v['grn_price'] : null,
                    'price' => (float) ($v['price'] ?? 0),
                    'discount_percentage' => (int) ($v['discount_percentage'] ?? 0),
                    'sale_price' => ! empty($v['sale_price']) ? (float) $v['sale_price'] : null,
                ]);
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
