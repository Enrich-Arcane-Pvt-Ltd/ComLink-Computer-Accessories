<?php

namespace App\Filament\Resources\GrnResource;

use App\Filament\Resources\GrnResource\Pages;
use App\Filament\Resources\GrnResource\Schemas\GrnForm;
use App\Filament\Resources\GrnResource\Tables\GrnTable;
use App\Models\Grn;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class GrnResource extends Resource
{
    protected static ?string $model = Grn::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $navigationGroup = 'Inventory Management';

    protected static ?string $navigationLabel = 'GRN';

    protected static ?string $modelLabel = 'GRN';

    protected static ?string $pluralModelLabel = 'Goods Received Notes (GRN)';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema(GrnForm::schema())->columns(1);
    }

    public static function table(Table $table): Table
    {
        return GrnTable::table($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGrns::route('/'),
            'create' => Pages\CreateGrn::route('/create'),
            'view' => Pages\ViewGrn::route('/{record}'),
            'edit' => Pages\EditGrn::route('/{record}/edit'),
        ];
    }
}
