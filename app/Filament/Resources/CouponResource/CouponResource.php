<?php

namespace App\Filament\Resources\CouponResource;

use App\Filament\Resources\CouponResource\Schemas\CouponForm;
use App\Filament\Resources\CouponResource\Schemas\CouponInfolist;
use App\Filament\Resources\CouponResource\Tables\CouponTable;
use App\Models\Coupon;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema(CouponForm::schema());
    }

    public static function table(Table $table): Table
    {
        return CouponTable::table($table);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return CouponInfolist::infolist($infolist);
    }

    public static function getRelations(): array
    {
        return [
            // Relations අවශ්‍ය නම් පසුව එක් කළ හැක
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
