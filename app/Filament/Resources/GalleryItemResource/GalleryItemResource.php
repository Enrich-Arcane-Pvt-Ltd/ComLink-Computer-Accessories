<?php

namespace App\Filament\Resources\GalleryItemResource;

use App\Filament\Resources\GalleryItemResource\Schemas\GalleryItemForm;
use App\Filament\Resources\GalleryItemResource\Schemas\GalleryItemInfolist;
use App\Filament\Resources\GalleryItemResource\Tables\GalleryItemTable;
use App\Models\GalleryItem;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class GalleryItemResource extends Resource
{
    protected static ?string $model = GalleryItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-camera';

    protected static ?string $navigationGroup = 'Content Management';

    protected static ?string $pluralLabel = 'Media Gallery';

    public static function form(Form $form): Form
    {
        return $form->schema(GalleryItemForm::schema())->columns(3);
    }

    public static function table(Table $table): Table
    {
        return GalleryItemTable::table($table);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return GalleryItemInfolist::infolist($infolist);
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
            'index' => Pages\ListGalleryItems::route('/'),
            'create' => Pages\CreateGalleryItem::route('/create'),
            'edit' => Pages\EditGalleryItem::route('/{record}/edit'),
        ];
    }
}
