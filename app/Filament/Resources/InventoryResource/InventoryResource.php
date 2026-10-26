<?php

declare(strict_types=1);

namespace App\Filament\Resources\InventoryResource;

use App\Filament\Resources\InventoryResource\Schemas\InventoryForm;
use App\Filament\Resources\InventoryResource\Tables\InventoryTable;
use App\Models\GrnItem;
use App\Models\InventoryProduct;
use App\Models\Supplier;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
            ->with(['supplier', 'grnItems.grn', 'storefrontProduct'])
            ->whereHas('grnItems');
    }

    /**
     * Ensures any product code recorded in GRN items is registered as an InventoryProduct.
     */
    protected static function syncGrnProductsToInventory(): void
    {
        try {
            $grnCodes = GrnItem::whereNotNull('product_code')
                ->where('product_code', '!=', '')
                ->pluck('product_code')
                ->unique();

            foreach ($grnCodes as $code) {
                $exists = InventoryProduct::where('product_code', $code)->exists();
                if (! $exists) {
                    $latest = GrnItem::where('product_code', $code)->latest('id')->first();
                    if ($latest) {
                        InventoryProduct::create([
                            'product_code' => $code,
                            'product_name' => $latest->product_name,
                            'supplier_id' => $latest->grn?->supplier_id ?? Supplier::first()?->id ?? 1,
                        ]);
                    }
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
            'view' => Pages\ViewInventory::route('/{record}'),
            'edit' => Pages\EditInventory::route('/{record}/edit'),
        ];
    }
}
