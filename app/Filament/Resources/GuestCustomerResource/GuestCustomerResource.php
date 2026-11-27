<?php

namespace App\Filament\Resources\GuestCustomerResource;

use App\Filament\Resources\UserResource\RelationManagers\AddressesRelationManager;
use App\Filament\Resources\UserResource\Schemas\UserForm;
use App\Filament\Resources\UserResource\Schemas\UserInfolist;
use App\Filament\Resources\UserResource\Tables\UserTable;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class GuestCustomerResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?string $navigationLabel = 'Guest Customers';

    protected static ?string $modelLabel = 'Guest Customer';

    protected static ?string $pluralModelLabel = 'Guest Customers';

    protected static ?string $slug = 'user-management/guest-customers';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return UserForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return UserTable::configure($table)
            ->recordUrl(fn (Model $record) => Pages\ViewGuestCustomer::getUrl([$record->id]));
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return UserInfolist::configure($infolist);
    }

    public static function getRelations(): array
    {
        return [
            AddressesRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('is_guest', true);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGuestCustomers::route('/'),
            'view' => Pages\ViewGuestCustomer::route('/{record}'),
            'edit' => Pages\EditGuestCustomer::route('/{record}/edit'),
        ];
    }
}
