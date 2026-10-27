<?php

namespace App\Filament\Resources\InventoryProductResource\Pages;

use App\Filament\Resources\InventoryProductResource\InventoryProductResource;
use App\Filament\Resources\InventoryProductResource\Schemas\InventoryProductForm;
use App\Models\Product;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateInventoryProduct extends CreateRecord
{
    protected static string $resource = InventoryProductResource::class;

    protected static ?string $title = 'Add Product';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['product_code'])) {
            $data['product_code'] = InventoryProductForm::generateProductCode(
                $data['product_name'] ?? null,
                isset($data['category_id']) ? (int) $data['category_id'] : null,
                isset($data['sub_category_id']) ? (int) $data['sub_category_id'] : null,
            );
        }

        // Extract primary image for inventory_products.product_image
        if (! empty($data['has_color_variants']) && ! empty($data['color_variants'])) {
            $variants = array_values(array_filter($data['color_variants'], fn ($v) => ! empty($v['color_name'])));
            $firstImg = $variants[0]['image'] ?? null;
            if (is_array($firstImg)) {
                $firstImg = array_values(array_filter($firstImg))[0] ?? null;
            }
            $data['product_image'] = is_string($firstImg) && trim($firstImg) !== '' ? trim($firstImg) : null;
        } else {
            $images = isset($data['images']) && is_array($data['images'])
                ? array_values(array_filter($data['images']))
                : [];
            $data['product_image'] = $images[0] ?? null;
        }

        return [
            'product_code'  => $data['product_code'],
            'product_name'  => $data['product_name'],
            'product_image' => $data['product_image'] ?? null,
            'supplier_id'   => $data['supplier_id'] ?? null,
        ];
    }

    protected function afterCreate(): void
    {
        $raw = $this->data;
        $productCode = $this->record->product_code;
        $productName = $this->record->product_name;

        // Build unique slug
        $slugBase = Str::slug($productName) ?: strtolower($productCode);
        $slug = $slugBase;
        $counter = 1;
        while (Product::where('slug', $slug)->where('product_code', '!=', $productCode)->exists()) {
            $slug = "{$slugBase}-{$counter}";
            $counter++;
        }

        $hasVariants = ! empty($raw['has_color_variants']) && ! empty($raw['color_variants']);

        if ($hasVariants) {
            $variants = array_values(array_filter($raw['color_variants'], fn ($v) => ! empty($v['color_name'])));
            $images = array_values(array_filter(array_map(function ($v) {
                $img = $v['image'] ?? null;
                if (is_array($img)) {
                    $img = array_values(array_filter($img))[0] ?? null;
                }

                return is_string($img) && trim($img) !== '' ? trim($img) : null;
            }, $variants)));
            $condition = $variants[0]['condition'] ?? 'Brand New';
            $type = 'variable';
        } else {
            $images = isset($raw['images']) && is_array($raw['images'])
                ? array_values(array_filter($raw['images']))
                : [];
            $condition = $raw['condition'] ?? 'Brand New';
            $type = 'simple';
        }

        $product = Product::updateOrCreate(
            ['product_code' => $productCode],
            [
                'brand_id'                 => $raw['brand_id'] ?? null,
                'name'                     => $productName,
                'slug'                     => $slug,
                'description'              => $raw['description'] ?? null,
                'images'                   => $images,
                'price'                    => 0.00,
                'sale_price'               => null,
                'grn_price'                => 0.00,
                'quantity'                 => 0,
                'inventory_stock'          => 0,
                'show_quantity'            => 0,
                'is_visible'               => false, // Will be published directly from /admin/inventory after GRN
                'is_featured'              => false,
                'type'                     => $type,
                'condition'                => $condition,
                'warranty_duration'        => ! empty($raw['warranty_duration']) ? (int) $raw['warranty_duration'] : null,
                'warranty_unit'            => $raw['warranty_unit'] ?? 'Years',
                'main_specifications'      => $raw['main_specifications'] ?? [],
                'technical_specifications' => $raw['technical_specifications'] ?? [],
            ]
        );

        // Sync Category & Sub Category
        $categories = array_values(array_filter([
            $raw['category_id'] ?? null,
            $raw['sub_category_id'] ?? null,
        ]));
        if (! empty($categories)) {
            $product->categories()->sync($categories);
        }

        // Sync Color Variants if enabled
        if ($hasVariants) {
            $variants = array_values(array_filter($raw['color_variants'], fn ($v) => ! empty($v['color_name'])));
            foreach ($variants as $v) {
                $image = $v['image'] ?? null;
                if (is_array($image)) {
                    $image = array_values(array_filter($image))[0] ?? null;
                }
                $image = is_string($image) && trim($image) !== '' ? trim($image) : null;

                $product->variants()->create([
                    'color_name'          => $v['color_name'],
                    'variant_name'        => $v['variant_name'] ?? "{$productName} - {$v['color_name']}",
                    'attributes'          => ['color' => $v['color_name']],
                    'image'               => $image,
                    'condition'           => $v['condition'] ?? 'Brand New',
                    'inventory_stock'     => 0,
                    'show_quantity'       => 0,
                    'stock'               => 0,
                    'grn_price'           => null,
                    'price'               => 0.00,
                    'discount_percentage' => 0,
                    'sale_price'          => null,
                ]);
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
