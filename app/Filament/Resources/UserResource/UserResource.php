<?php

namespace App\Filament\Resources\UserResource;

use App\Filament\Resources\UserResource\Schemas\UserForm;
use App\Filament\Resources\UserResource\Schemas\UserInfolist; // අලුත් Infolist එක
use App\Filament\Resources\UserResource\Tables\UserTable;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'User Management';

    // 1. Form Configuration
    public static function form(Form $form): Form
    {
        return UserForm::configure($form);
    }

    // 2. Table Configuration
    public static function table(Table $table): Table
    {
        return UserTable::configure($table)
            // Table Row එක Click කළාම වෙන දේ:
            ->recordUrl(function (Model $record) {
                // Super Admin (ID 1) නම් Click කරන්න බෑ (null)
                if ($record->id === 1) {
                    return null;
                }

                // අනිත් අයට View Page එකට යන්න
                return Pages\ViewUser::getUrl([$record->id]);
            });
    }

    // 3. View Page Infolist Configuration
    public static function infolist(Infolist $infolist): Infolist
    {
        return UserInfolist::configure($infolist);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AddressesRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (Auth::id() !== 1) {
            $query->where('id', '!=', 1);
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'), // View Page Route
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
