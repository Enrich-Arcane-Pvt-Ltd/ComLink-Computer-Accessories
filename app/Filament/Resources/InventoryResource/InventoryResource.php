<?php

namespace App\Filament\Resources\InventoryResource;

use App\Filament\Resources\InventoryResource\Pages;
use App\Filament\Resources\InventoryResource\Schemas\InventoryForm;
use App\Filament\Resources\InventoryResource\Tables\InventoryTable;
use App\Models\GrnItem;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class InventoryResource extends Resource
{
    protected static ?string $model = GrnItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'Inventory Management';

    protected static ?string $navigationLabel = 'Inventory';

    protected static ?string $modelLabel = 'Inventory Item';

    protected static ?string $pluralModelLabel = 'Inventory';

    protected static ?string $slug = 'inventory';

    protected static ?int $navigationSort = 4;

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
