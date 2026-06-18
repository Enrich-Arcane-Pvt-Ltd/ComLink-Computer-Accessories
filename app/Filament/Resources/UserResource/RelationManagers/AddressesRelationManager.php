<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->options(['shipping' => 'Shipping', 'billing' => 'Billing'])
                    ->required()
                    ->default('shipping'),

                Forms\Components\Toggle::make('is_default')
                    ->label('Set as Default Address')
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('first_name')->required(),
                Forms\Components\TextInput::make('last_name')->required(),
                Forms\Components\TextInput::make('address_line_1')->required()->columnSpanFull(),
                Forms\Components\TextInput::make('address_line_2')->columnSpanFull(),
                Forms\Components\TextInput::make('city')->required(),
                Forms\Components\TextInput::make('postal_code'),
                Forms\Components\TextInput::make('phone')->tel(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('is_default')
                    ->boolean()
                    ->label('Default')
                    ->trueIcon('heroicon-s-star') // Default නම් Star එකක්
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('warning'),
                Tables\Columns\TextColumn::make('type')->badge()->color('warning'),
                Tables\Columns\TextColumn::make('address_line_1'),
                Tables\Columns\TextColumn::make('city'),
                Tables\Columns\TextColumn::make('phone'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
