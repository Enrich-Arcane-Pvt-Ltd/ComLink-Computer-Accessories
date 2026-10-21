<?php

namespace App\Filament\Resources\TestimonialResource;

use App\Filament\Resources\TestimonialResource\Pages\CreateTestimonial;
use App\Filament\Resources\TestimonialResource\Pages\EditTestimonial;
use App\Filament\Resources\TestimonialResource\Pages\ListTestimonials;
use App\Filament\Resources\TestimonialResource\Schemas\TestimonialForm;
use App\Filament\Resources\TestimonialResource\Schemas\TestimonialInfolist;
use App\Filament\Resources\TestimonialResource\Tables\TestimonialTable;
use App\Models\Testimonial;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static ?string $navigationIcon = 'heroicon-o-pencil';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema(TestimonialForm::schema())->columns(3);
    }

    public static function table(Table $table): Table
    {
        return TestimonialTable::table($table);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return TestimonialInfolist::infolist($infolist);
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
            'index' => ListTestimonials::route('/'),
            'create' => CreateTestimonial::route('/create'),
            'edit' => EditTestimonial::route('/{record}/edit'),
        ];
    }
}
