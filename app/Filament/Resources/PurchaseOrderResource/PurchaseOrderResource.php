<?php

namespace App\Filament\Resources\PurchaseOrderResource;

use App\Filament\Resources\PurchaseOrderResource\Schemas\PurchaseOrderForm;
use App\Filament\Resources\PurchaseOrderResource\Tables\PurchaseOrderTable;
use App\Models\PurchaseOrder;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Inventory Management';

    protected static ?string $navigationLabel = 'Purchase Orders';

    protected static ?string $modelLabel = 'Purchase Order';

    protected static ?string $pluralModelLabel = 'Purchase Orders';

    protected static ?string $slug = 'purchase-orders';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema(PurchaseOrderForm::schema())->columns(1);
    }

    public static function table(Table $table): Table
    {
        return PurchaseOrderTable::table($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'view' => Pages\ViewPurchaseOrder::route('/{record}'),
            'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
