<?php

namespace App\Filament\Resources\InventoryProductResource;

use App\Filament\Resources\InventoryProductResource\Pages;
use App\Filament\Resources\InventoryProductResource\Schemas\InventoryProductForm;
use App\Filament\Resources\InventoryProductResource\Tables\InventoryProductTable;
use App\Models\InventoryProduct;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class InventoryProductResource extends Resource
{
    protected static ?string $model = InventoryProduct::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Inventory Management';

    protected static ?string $navigationLabel = 'Inventory Products';

    protected static ?string $modelLabel = 'Inventory Product';

    protected static ?string $pluralModelLabel = 'Inventory Products';

    protected static ?string $slug = 'inventory-products';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema(InventoryProductForm::schema())->columns(1);
    }

    public static function table(Table $table): Table
    {
        return InventoryProductTable::table($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventoryProducts::route('/'),
            'create' => Pages\CreateInventoryProduct::route('/create'),
            'edit' => Pages\EditInventoryProduct::route('/{record}/edit'),
        ];
    }
}
