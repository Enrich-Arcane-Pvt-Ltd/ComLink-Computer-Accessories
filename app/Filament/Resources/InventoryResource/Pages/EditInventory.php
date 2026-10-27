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

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return [
            'product_code'  => $data['product_code'],
            'product_name'  => $data['product_name'],
            'supplier_id'   => $data['supplier_id'] ?? null,
            'product_image' => $data['product_image'] ?? $this->record->product_image,
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
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
