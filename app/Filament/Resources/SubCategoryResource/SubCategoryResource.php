<?php

namespace App\Filament\Resources\SubCategoryResource;

use App\Filament\Resources\SubCategoryResource\Schemas\SubCategoryForm;
use App\Filament\Resources\SubCategoryResource\Tables\SubCategoryTable;
use App\Models\Category;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class SubCategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder-minus';

    protected static ?string $navigationGroup = 'Product Catalog';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Sub Categories';

    protected static ?string $modelLabel = 'Sub Category';

    protected static ?string $pluralModelLabel = 'Sub Categories';

    protected static ?string $slug = 'sub-categories';

    public static function form(Form $form): Form
    {
        return $form->schema(SubCategoryForm::schema())->columns(1);
    }

    public static function table(Table $table): Table
    {
        return SubCategoryTable::table($table)
            ->modifyQueryUsing(fn ($query) => $query->whereNotNull('parent_id')->with('parent'));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubCategories::route('/'),
            'create' => Pages\CreateSubCategory::route('/create'),
            'edit' => Pages\EditSubCategory::route('/{record}/edit'),
        ];
    }
}
