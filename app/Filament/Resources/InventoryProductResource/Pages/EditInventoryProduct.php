<?php

namespace App\Filament\Resources\InventoryProductResource\Pages;

use App\Filament\Resources\InventoryProductResource\InventoryProductResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditInventoryProduct extends EditRecord
{
    protected static string $resource = InventoryProductResource::class;

    protected static ?string $title = 'Edit Product';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $product = $this->record->storefrontProduct()->with(['categories', 'variants'])->first();

        if ($product) {
            $mainCat = $product->categories->firstWhere('parent_id', null);
            $subCat = $product->categories->firstWhere('parent_id', '!=', null);

            $data['category_id'] = $mainCat?->id;
            $data['sub_category_id'] = $subCat?->id;
            $data['brand_id'] = $product->brand_id;
            $data['condition'] = $product->condition ?? 'Brand New';
            $data['description'] = $product->description;
            $data['images'] = is_array($product->images) ? $product->images : ($this->record->product_image ? [$this->record->product_image] : []);
            $data['warranty_duration'] = $product->warranty_duration;
            $data['warranty_unit'] = $product->warranty_unit ?? 'Years';
            $data['main_specifications'] = $product->main_specifications ?? [];
            $data['technical_specifications'] = $product->technical_specifications ?? [];

            if ($product->hasColorVariants()) {
                $data['has_color_variants'] = true;
                $data['color_variants'] = $product->variants->map(fn ($v) => [
                    'id'           => $v->id,
                    'color_name'   => $v->color_name,
                    'variant_name' => $v->variant_name ?? "{$product->name} - {$v->color_name}",
                    'condition'    => $v->condition ?? 'Brand New',
                    'image'        => $v->image,
                ])->toArray();
            } else {
                $data['has_color_variants'] = false;
            }
        } else {
            $data['has_color_variants'] = false;
            $data['condition'] = 'Brand New';
            $data['images'] = $this->record->product_image ? [$this->record->product_image] : [];
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! empty($data['has_color_variants']) && ! empty($data['color_variants'])) {
            $variants = array_values(array_filter($data['color_variants'], fn ($v) => ! empty($v['color_name'])));
            $firstImg = $variants[0]['image'] ?? null;
            if (is_array($firstImg)) {
                $firstImg = array_values(array_filter($firstImg))[0] ?? null;
            }
            $productImage = is_string($firstImg) && trim($firstImg) !== '' ? trim($firstImg) : $this->record->product_image;
        } else {
            $images = isset($data['images']) && is_array($data['images'])
                ? array_values(array_filter($data['images']))
                : [];
            $productImage = $images[0] ?? $this->record->product_image;
        }

        return [
            'product_code'  => $data['product_code'],
            'product_name'  => $data['product_name'],
            'product_image' => $productImage,
        ];
    }

    protected function afterSave(): void
    {
        $raw = $this->data;
        $productCode = $this->record->product_code;
        $productName = $this->record->product_name;

        $existingProduct = Product::where('product_code', $productCode)->first();

        $slugBase = Str::slug($productName) ?: strtolower($productCode);
        $slug = $existingProduct?->slug ?: $slugBase;
        $counter = 1;
        while (Product::where('slug', $slug)->where('id', '!=', $existingProduct?->id)->exists()) {
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
                'brand_id'                 => $raw['brand_id'] ?? $existingProduct?->brand_id,
                'name'                     => $productName,
                'slug'                     => $slug,
                'description'              => $raw['description'] ?? null,
                'images'                   => ! empty($images) ? $images : ($existingProduct?->images ?? []),
                'type'                     => $type,
                'condition'                => $condition,
                'warranty_duration'        => ! empty($raw['warranty_duration']) ? (int) $raw['warranty_duration'] : null,
                'warranty_unit'            => $raw['warranty_unit'] ?? 'Years',
                'main_specifications'      => $raw['main_specifications'] ?? [],
                'technical_specifications' => $raw['technical_specifications'] ?? [],
                'price'                    => $existingProduct ? $existingProduct->price : 0.00,
                'is_visible'               => $existingProduct ? $existingProduct->is_visible : false,
            ]
        );

        // Sync Categories
        $categories = array_values(array_filter([
            $raw['category_id'] ?? null,
            $raw['sub_category_id'] ?? null,
        ]));
        if (! empty($categories)) {
            $product->categories()->sync($categories);
        }

        // Sync Color Variants
        if ($hasVariants) {
            $existingIds = [];
            $variants = array_values(array_filter($raw['color_variants'], fn ($v) => ! empty($v['color_name'])));

            foreach ($variants as $v) {
                $image = $v['image'] ?? null;
                if (is_array($image)) {
                    $image = array_values(array_filter($image))[0] ?? null;
                }
                $image = is_string($image) && trim($image) !== '' ? trim($image) : null;

                if (! empty($v['id'])) {
                    $variant = $product->variants()->find($v['id']);
                    if ($variant) {
                        $variant->update([
                            'color_name'   => $v['color_name'],
                            'variant_name' => $v['variant_name'] ?? "{$productName} - {$v['color_name']}",
                            'attributes'   => ['color' => $v['color_name']],
                            'image'        => $image ?: $variant->image,
                            'condition'    => $v['condition'] ?? 'Brand New',
                        ]);
                        $existingIds[] = $variant->id;
                        continue;
                    }
                }

                $newVariant = $product->variants()->create([
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
                $existingIds[] = $newVariant->id;
            }

            $product->variants()->whereNotIn('id', $existingIds)->delete();
        } else {
            $product->variants()->delete();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
