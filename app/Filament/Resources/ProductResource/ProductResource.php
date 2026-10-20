<?php

namespace App\Filament\Resources\ProductResource;

use App\Filament\Resources\ProductResource\Schemas\ProductForm;
use App\Filament\Resources\ProductResource\Schemas\ProductInfolist;
use App\Filament\Resources\ProductResource\Tables\ProductTable;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationGroup = 'Product Catalog';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema(ProductForm::schema())->columns(1);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return ProductInfolist::infolist($infolist);
    }

    public static function table(Table $table): Table
    {
        // අර හදපු Table Class එක මෙතනට දානවා
        return ProductTable::table($table);
    }

    public static function getRelations(): array
    {
        return [
            // Relations පස්සේ දාමු
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
