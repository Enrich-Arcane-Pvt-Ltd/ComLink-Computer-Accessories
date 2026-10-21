<?php

namespace App\Filament\Resources\ProductSpecificationResource;

use App\Filament\Resources\ProductSpecificationResource\Schemas\ProductSpecificationForm;
use App\Filament\Resources\ProductSpecificationResource\Tables\ProductSpecificationTable;
use App\Models\ProductSpecificationGroup;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class ProductSpecificationResource extends Resource
{
    protected static ?string $model = ProductSpecificationGroup::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationGroup = 'Product Catalog';

    protected static ?string $navigationLabel = 'Product Specification Attributes';

    protected static ?string $modelLabel = 'Product Specification Attribute';

    protected static ?string $pluralModelLabel = 'Product Specification Attributes';

    protected static ?string $slug = 'product-specification-attributes';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema(ProductSpecificationForm::schema())->columns(1);
    }

    public static function table(Table $table): Table
    {
        return ProductSpecificationTable::table($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductSpecifications::route('/'),
            'create' => Pages\CreateProductSpecification::route('/create'),
            'edit' => Pages\EditProductSpecification::route('/{record}/edit'),
        ];
    }
}
