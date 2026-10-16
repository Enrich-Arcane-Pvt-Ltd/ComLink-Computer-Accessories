<?php

namespace App\Filament\Resources\BrandResource;

use App\Filament\Resources\BrandResource\Schemas\BrandForm;
use App\Filament\Resources\BrandResource\Schemas\BrandInfolist;
use App\Filament\Resources\BrandResource\Tables\BrandTable;
use App\Models\Brand;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Product Catalog';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema(BrandForm::schema())->columns(1);
    }

    public static function table(Table $table): Table
    {
        return BrandTable::table($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return BrandInfolist::infolist($infolist);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'edit' => Pages\EditBrand::route('/{record}/edit'),
        ];
    }
}
