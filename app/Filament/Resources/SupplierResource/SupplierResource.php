<?php

namespace App\Filament\Resources\SupplierResource;

use App\Filament\Resources\SupplierResource\Schemas\SupplierForm;
use App\Filament\Resources\SupplierResource\Schemas\SupplierInfolist;
use App\Filament\Resources\SupplierResource\Tables\SupplierTable;
use App\Models\Supplier;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Supplier Management';

    protected static ?string $navigationLabel = 'Supplier Details';

    protected static ?string $modelLabel = 'Supplier';

    protected static ?string $pluralModelLabel = 'Supplier Details';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema(SupplierForm::schema())->columns(1);
    }

    public static function table(Table $table): Table
    {
        return SupplierTable::table($table);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return SupplierInfolist::infolist($infolist);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
