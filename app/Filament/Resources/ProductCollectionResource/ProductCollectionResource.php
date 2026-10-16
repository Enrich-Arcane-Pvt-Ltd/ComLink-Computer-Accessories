<?php

namespace App\Filament\Resources\ProductCollectionResource;

use App\Filament\Resources\ProductCollectionResource\Pages;
use App\Filament\Resources\ProductCollectionResource\Schemas\CollectionForm;
use App\Filament\Resources\ProductCollectionResource\Schemas\CollectionInfolist;
use App\Filament\Resources\ProductCollectionResource\Tables\CollectionTable;
use App\Models\ProductCollection;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class ProductCollectionResource extends Resource
{
    protected static ?string $model = ProductCollection::class;
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationGroup = 'Catalog Management';
    protected static ?int $navigationSort = 3;
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form->schema(CollectionForm::schema())->columns(3);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return CollectionInfolist::infolist($infolist);
    }

    public static function table(Table $table): Table
    {
        return CollectionTable::table($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductCollections::route('/'),
            'create' => Pages\CreateProductCollection::route('/create'),
            'edit' => Pages\EditProductCollection::route('/{record}/edit'),
        ];
    }
}
