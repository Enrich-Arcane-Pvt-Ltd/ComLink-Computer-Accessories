<?php

namespace App\Filament\Resources\InventoryResource\Pages;

use App\Filament\Resources\InventoryResource\InventoryResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditInventory extends EditRecord
{
    protected static string $resource = InventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $product = $this->record->storefrontProduct;
        $grnPrice = (float) ($this->record->latest_grn_price ?? $product?->grn_price ?? 0);
        $origPrice = $product && (float) $product->price > 0 ? (float) $product->price : null;
        $salePrice = $product && $product->sale_price !== null && (float) $product->sale_price > 0
            ? (float) $product->sale_price
            : $origPrice;
        $discountPct = ($origPrice && $salePrice && $salePrice < $origPrice)
            ? (int) round((($origPrice - $salePrice) / $origPrice) * 100)
            : 0;

        $data['grn_price'] = round($grnPrice, 2);
        $data['price'] = $origPrice;
        $data['discount_percentage'] = $discountPct;
        $data['sale_price'] = $salePrice;
        $data['is_visible'] = (bool) ($product?->is_visible ?? false);
        $data['is_featured'] = (bool) ($product?->is_featured ?? false);

        // Populate color variations if product has color variants
        $variantsData = [];
        if ($product && $product->hasColorVariants()) {
            foreach ($product->variants as $v) {
                $vOrig = (float) ($v->price ?? 0);
                $vSale = $v->sale_price !== null && (float) $v->sale_price > 0 ? (float) $v->sale_price : $vOrig;
                $vDisc = ($vOrig > 0 && $vSale < $vOrig) ? (int) round((($vOrig - $vSale) / $vOrig) * 100) : (int) ($v->discount_percentage ?? 0);
                $vGrn = (float) ($v->grn_price ?: $product->grn_price ?: 0);

                $variantsData[] = [
                    'id'                  => $v->id,
                    'color_name'          => $v->color_name,
                    'variant_name'        => $v->variant_name ?: "{$product->name} - {$v->color_name}",
                    'image'               => $v->image,
                    'condition'           => $v->condition ?: 'Brand New',
                    'inventory_stock'     => (int) ($v->inventory_stock ?? $v->stock ?? 0),
                    'grn_price'           => round($vGrn, 2),
                    'price'               => $vOrig > 0 ? round($vOrig, 2) : null,
                    'discount_percentage' => $vDisc,
                    'sale_price'          => $vSale > 0 ? round($vSale, 2) : null,
                ];
            }
        }
        $data['has_color_variants'] = ! empty($variantsData);
        $data['color_variants'] = $variantsData;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return [
            'product_code'  => $this->record->product_code,
            'product_name'  => $this->record->product_name,
            'supplier_id'   => $this->record->supplier_id,
            'product_image' => $this->record->product_image,
        ];
    }

    protected function afterSave(): void
    {
        $raw = $this->data;
        $totalStock = (int) $this->record->grnItems()->sum('product_quantity');
        $latestGrnItem = $this->record->grnItems()->latest('id')->first();
        $supplierId = $this->record->supplier_id ?? $latestGrnItem?->grn?->supplier_id;

        $origPrice = (float) ($raw['price'] ?? 0);
        $salePrice = ! empty($raw['sale_price']) ? (float) $raw['sale_price'] : $origPrice;

        $product = $this->record->storefrontProduct;
        if (! $product) {
            $slugBase = Str::slug($this->record->product_name) ?: strtolower($this->record->product_code);
            $slug = $slugBase;
            $i = 1;
            while (Product::where('slug', $slug)->exists()) {
                $slug = "{$slugBase}-{$i}";
                $i++;
            }

            Product::create([
                'product_code'      => $this->record->product_code,
                'name'              => $this->record->product_name,
                'slug'              => $slug,
                'supplier_id'       => $supplierId,
                'type'              => 'simple',
                'condition'         => 'Brand New',
                'price'             => $origPrice,
                'sale_price'        => $salePrice,
                'grn_price'         => $latestGrnItem ? (float) $latestGrnItem->price_per_unit : 0.00,
                'inventory_stock'   => $totalStock,
                'show_quantity'     => $totalStock,
                'quantity'          => $totalStock,
                'stock_added_count' => $totalStock,
                'stock_added_date'  => now(),
                'is_visible'        => (bool) ($raw['is_visible'] ?? false),
                'is_featured'       => (bool) ($raw['is_featured'] ?? false),
                'images'            => $this->record->product_image ? [$this->record->product_image] : [],
            ]);
        } else {
            $sold = (int) $product->sold_count;
            $avail = max(0, $totalStock - $sold);

            $product->update([
                'name'            => $this->record->product_name,
                'supplier_id'     => $supplierId ?? $product->supplier_id,
                'price'           => $origPrice,
                'sale_price'      => $salePrice,
                'grn_price'       => $latestGrnItem ? (float) $latestGrnItem->price_per_unit : $product->grn_price,
                'inventory_stock' => $totalStock,
                'show_quantity'   => $totalStock,
                'quantity'        => $avail,
                'is_visible'      => (bool) ($raw['is_visible'] ?? false),
                'is_featured'     => (bool) ($raw['is_featured'] ?? false),
            ]);
        }

        // Persist color variants updates if present
        if (! empty($raw['color_variants']) && is_array($raw['color_variants'])) {
            foreach ($raw['color_variants'] as $vData) {
                if (! empty($vData['id'])) {
                    $variant = \App\Models\ProductVariant::find($vData['id']);
                    if ($variant) {
                        $vOrig = ! empty($vData['price']) ? (float) $vData['price'] : 0.0;
                        $vSale = ! empty($vData['sale_price']) ? (float) $vData['sale_price'] : $vOrig;
                        $vDisc = ($vOrig > 0 && $vSale < $vOrig) ? (int) round((($vOrig - $vSale) / $vOrig) * 100) : (int) ($vData['discount_percentage'] ?? 0);
                        $vGrn = isset($vData['grn_price']) && $vData['grn_price'] !== '' ? (float) $vData['grn_price'] : (float) $variant->grn_price;

                        $variant->update([
                            'grn_price'           => $vGrn,
                            'price'               => $vOrig,
                            'discount_percentage' => $vDisc,
                            'sale_price'          => $vSale,
                        ]);
                    }
                }
            }

            // Sync parent Product & InventoryProduct with the 1st color variation details
            if ($product) {
                $product->syncWithFirstVariant();
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
