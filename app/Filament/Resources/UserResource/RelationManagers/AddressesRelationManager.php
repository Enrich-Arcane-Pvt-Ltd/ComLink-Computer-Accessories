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
                    ->label('Address Type')
                    ->options(['shipping' => 'Shipping', 'billing' => 'Billing'])
                    ->required()
                    ->default('shipping'),

                Forms\Components\Toggle::make('is_default')
                    ->label('Set as Default Address')
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('first_name')
                    ->label('First Name')
                    ->required()
                    ->maxLength(100)
                    ->placeholder('e.g. Kasun'),

                Forms\Components\TextInput::make('last_name')
                    ->label('Last Name')
                    ->required()
                    ->maxLength(100)
                    ->placeholder('e.g. Perera'),

                Forms\Components\TextInput::make('address_line_1')
                    ->label('Address Line 1')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g. No. 123, Galle Road')
                    ->helperText('Street address, P.O. box, or building details (Max 255 characters)')
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('address_line_2')
                    ->label('Address Line 2')
                    ->maxLength(255)
                    ->placeholder('e.g. Apt 4B, 2nd Floor (Optional)')
                    ->helperText('Apartment, suite, unit, floor, etc. (Max 255 characters)')
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('city')
                    ->label('City')
                    ->required()
                    ->maxLength(100)
                    ->placeholder('e.g. Colombo, Kandy, Gampaha'),

                Forms\Components\TextInput::make('postal_code')
                    ->label('Postal Code')
                    ->maxLength(20)
                    ->placeholder('e.g. 00100')
                    ->rules(['nullable', 'string', 'max:20', 'regex:/^[a-zA-Z0-9\s\-]+$/'])
                    ->validationMessages([
                        'regex' => 'The postal code may only contain letters, numbers, spaces, and hyphens.',
                    ]),

                Forms\Components\TextInput::make('phone')
                    ->label('Phone Number')
                    ->tel()
                    ->maxLength(25)
                    ->placeholder('e.g. +94 77 123 4567 or 0771234567')
                    ->rules(['nullable', 'string', 'max:25', 'regex:/^[0-9+\s\-()]+$/'])
                    ->validationMessages([
                        'regex' => 'Please enter a valid phone number (digits, +, -, () only).',
                    ])
                    ->helperText('Contact number for order & delivery updates (Max 25 characters)'),
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
