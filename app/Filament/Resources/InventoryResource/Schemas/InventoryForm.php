<?php

namespace App\Filament\Resources\InventoryResource\Schemas;

use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;

class InventoryForm
{
    public static function schema(): array
    {
        return [
            Forms\Components\Section::make('Inventory Product Details')
                ->description('Received item details linked to GRN invoice.')
                ->schema([
                    Forms\Components\TextInput::make('product_code')
                        ->label('Product Code')
                        ->readOnly()
                        ->dehydrated(),

                    Forms\Components\TextInput::make('product_name')
                        ->label('Product Name')
                        ->readOnly()
                        ->dehydrated(),

                    Forms\Components\Select::make('product_condition')
                        ->label('Product Condition')
                        ->options([
                            'Brandnew' => 'Brandnew',
                            'Used' => 'Used',
                        ])
                        ->native(false)
                        ->required(),

                    Forms\Components\TextInput::make('price_per_unit')
                        ->label('Price Per Unit (Rs.)')
                        ->numeric()
                        ->prefix('Rs. ')
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            $price = (float) ($get('price_per_unit') ?: 0);
                            $qty = (int) ($get('product_quantity') ?: 1);
                            $set('total', round($price * $qty, 2));
                        }),

                    Forms\Components\TextInput::make('product_quantity')
                        ->label('Product Quantity')
                        ->numeric()
                        ->minValue(1)
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            $price = (float) ($get('price_per_unit') ?: 0);
                            $qty = (int) ($get('product_quantity') ?: 1);
                            $set('total', round($price * $qty, 2));
                        }),

                    Forms\Components\TextInput::make('total')
                        ->label('Total (Rs.)')
                        ->prefix('Rs. ')
                        ->numeric()
                        ->readOnly()
                        ->dehydrated(),
                ])->columns(2),
        ];
    }
}
