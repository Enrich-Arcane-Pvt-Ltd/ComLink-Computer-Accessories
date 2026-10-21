<?php

namespace App\Filament\Resources\SlideResource;

use App\Filament\Resources\SlideResource\Schemas\SlideForm;
use App\Filament\Resources\SlideResource\Schemas\SlideInfolist;
use App\Filament\Resources\SlideResource\Tables\SlideTable;
use App\Models\Slide;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class SlideResource extends Resource
{
    protected static ?string $model = Slide::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Content Management';

    protected static ?string $pluralLabel = 'Hero & CTA Content';

    public static function form(Form $form): Form
    {
        return $form->schema(SlideForm::schema())->columns(3);
    }

    public static function table(Table $table): Table
    {
        return SlideTable::table($table);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return SlideInfolist::infolist($infolist);
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
            'index' => Pages\ListSlides::route('/'),
            'create' => Pages\CreateSlide::route('/create'),
            'edit' => Pages\EditSlide::route('/{record}/edit'),
        ];
    }
}
