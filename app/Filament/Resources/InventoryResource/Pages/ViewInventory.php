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

        return $data;
    }
}
