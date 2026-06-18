<?php

namespace App\Filament\Resources\CategoryResource;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\Schemas\CategoryForm;
use App\Filament\Resources\CategoryResource\Schemas\CategoryInfolist;
use App\Filament\Resources\CategoryResource\Tables\CategoryTable;
use App\Models\Category;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack'; // Stack icon
    protected static ?string $navigationGroup = 'Catalog Management';
    protected static ?int $navigationSort = 2; // Brand එකට පස්සේ

    public static function form(Form $form): Form
    {
        return $form->schema(CategoryForm::schema())->columns(3);
    }

    public static function table(Table $table): Table
    {
        return CategoryTable::table($table)
            // Performance: Parent category එක N+1 නැතුව Load කරගන්නවා
            ->modifyQueryUsing(fn($query) => $query->with('parent'));
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return CategoryInfolist::infolist($infolist);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
