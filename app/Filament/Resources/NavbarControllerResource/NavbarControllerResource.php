<?php

namespace App\Filament\Resources\NavbarControllerResource;

use App\Filament\Resources\NavbarControllerResource\Tables\NavbarControllerTable;
use App\Models\Category;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class NavbarControllerResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-bars-3-bottom-left';

    protected static ?string $navigationGroup = 'Product Catalog';

    protected static ?string $navigationLabel = 'Navbar Controller';

    protected static ?string $modelLabel = 'Navbar Category';

    protected static ?string $pluralModelLabel = 'Navbar Controller';

    protected static ?string $slug = 'navbar-controller';

    protected static ?int $navigationSort = 3;

    public static function table(Table $table): Table
    {
        return NavbarControllerTable::table($table)
            ->modifyQueryUsing(fn ($query) => $query->whereNull('parent_id')->with('children'));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNavbarControllers::route('/'),
        ];
    }
}
