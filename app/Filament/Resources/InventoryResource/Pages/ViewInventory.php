<?php

declare(strict_types=1);

namespace App\Filament\Resources\InventoryResource\Pages;

use App\Filament\Resources\InventoryResource\InventoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\MaxWidth;

class ViewInventory extends ViewRecord
{
    protected static string $resource = InventoryResource::class;

    protected static string $view = 'filament.pages.view-inventory';

    public function getMaxContentWidth(): MaxWidth|string|null
    {
        return MaxWidth::Full;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
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
}
