<?php

declare(strict_types=1);

namespace App\Filament\Resources\InventoryResource;

use App\Filament\Resources\InventoryResource\Schemas\InventoryForm;
use App\Filament\Resources\InventoryResource\Tables\InventoryTable;
use App\Models\GrnItem;
use App\Models\InventoryProduct;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class InventoryResource extends Resource
{
    protected static ?string $model = InventoryProduct::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'Inventory Management';

    protected static ?string $navigationLabel = 'Inventory';

    protected static ?string $modelLabel = 'Inventory Product';

    protected static ?string $pluralModelLabel = 'Inventory';

    protected static ?string $slug = 'inventory';

    protected static ?int $navigationSort = 5;

    public static function getEloquentQuery(): Builder
    {
        static::syncGrnProductsToInventory();

        return parent::getEloquentQuery()
            ->with([
                'supplier',
                'grnItems.grn.supplier',
                'storefrontProduct.categories',
                'storefrontProduct.brand',
                'storefrontProduct.variants',
            ])
            ->whereHas('grnItems');
    }

    /**
     * Ensures any product code recorded in GRN items is registered as an InventoryProduct
     * and has its storefront Product stock and GRN cost synced.
     */
    protected static function syncGrnProductsToInventory(): void
    {
        try {
            $grnCodes = GrnItem::whereNotNull('product_code')
                ->where('product_code', '!=', '')
                ->pluck('product_code')
                ->unique();

            foreach ($grnCodes as $code) {
                $latest = GrnItem::with('grn')->where('product_code', $code)->latest('id')->first();
                $totalStock = (int) GrnItem::where('product_code', $code)->sum('product_quantity');
                $supplierId = $latest?->grn?->supplier_id;

                $invProduct = InventoryProduct::where('product_code', $code)->first();
                if (! $invProduct && $latest) {
                    $invProduct = InventoryProduct::create([
                        'product_code' => $code,
                        'product_name' => $latest->product_name,
                        'supplier_id'  => $supplierId,
                    ]);
                } elseif ($invProduct && $supplierId && ! $invProduct->supplier_id) {
                    $invProduct->update(['supplier_id' => $supplierId]);
                }

                // Ensure corresponding Product record exists and has synced stock/GRN price
                $product = Product::where('product_code', $code)->first();
                if (! $product && $invProduct) {
                    $slugBase = Str::slug($invProduct->product_name) ?: strtolower($code);
                    $slug = $slugBase;
                    $i = 1;
                    while (Product::where('slug', $slug)->exists()) {
                        $slug = "{$slugBase}-{$i}";
                        $i++;
                    }

                    Product::create([
                        'product_code'      => $code,
                        'name'              => $invProduct->product_name,
                        'slug'              => $slug,
                        'supplier_id'       => $supplierId ?? $invProduct->supplier_id,
                        'type'              => 'simple',
                        'condition'         => 'Brand New',
                        'price'             => 0.00,
                        'grn_price'         => $latest ? (float) $latest->price_per_unit : 0.00,
                        'inventory_stock'   => $totalStock,
                        'show_quantity'     => $totalStock,
                        'quantity'          => $totalStock,
                        'stock_added_count' => $totalStock,
                        'stock_added_date'  => now(),
                        'is_visible'        => false,
                        'is_featured'       => false,
                        'images'            => $invProduct->product_image ? [$invProduct->product_image] : [],
                    ]);
                } elseif ($product) {
                    $sold = (int) $product->sold_count;
                    $avail = max(0, $totalStock - $sold);
                    $updates = [
                        'inventory_stock' => $totalStock,
                        'show_quantity'   => $totalStock,
                        'quantity'        => $avail,
                    ];
                    if ($latest) {
                        $updates['grn_price'] = (float) $latest->price_per_unit;
                    }
                    if ($supplierId && ! $product->supplier_id) {
                        $updates['supplier_id'] = $supplierId;
                    }
                    $product->updateQuietly($updates);
                }
            }
        } catch (\Throwable $e) {
            // Graceful fallback
        }
    }

    public static function form(Form $form): Form
    {
        return $form->schema(InventoryForm::schema())->columns(1);
    }

    public static function table(Table $table): Table
    {
        return InventoryTable::table($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventories::route('/'),
            'view'  => Pages\ViewInventory::route('/{record}'),
            'edit'  => Pages\EditInventory::route('/{record}/edit'),
        ];
    }
}
