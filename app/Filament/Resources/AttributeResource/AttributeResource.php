<?php

namespace App\Filament\Resources\AttributeResource;

use App\Filament\Resources\AttributeResource\Schemas\AttributeForm;
use App\Filament\Resources\AttributeResource\Schemas\AttributeInfolist;
use App\Filament\Resources\AttributeResource\Tables\AttributeTable;
use App\Models\Attribute;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class AttributeResource extends Resource
{
    protected static ?string $model = Attribute::class;

    protected static ?string $navigationIcon = 'heroicon-o-swatch';

    protected static ?string $navigationGroup = 'Catalog Management';

    protected static ?int $navigationSort = 4;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form->schema(AttributeForm::schema());
    }

    public static function table(Table $table): Table
    {
        return AttributeTable::table($table);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return AttributeInfolist::infolist($infolist);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttributes::route('/'),
            'create' => Pages\CreateAttribute::route('/create'),
            'edit' => Pages\EditAttribute::route('/{record}/edit'),
        ];
    }
}
