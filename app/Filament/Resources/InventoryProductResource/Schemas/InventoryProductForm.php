<?php

namespace App\Filament\Resources\InventoryProductResource\Schemas;

use App\Models\Supplier;
use Filament\Forms;

class InventoryProductForm
{
    public static function schema(): array
    {
        return [
            Forms\Components\Section::make('Product Information')
                ->description('Add inventory product with supplier-provided code and specifications.')
                ->schema([
                    Forms\Components\FileUpload::make('product_image')
                        ->label('Product Image')
                        ->image()
                        ->disk('public')
                        ->directory('inventory-products')
                        ->imageEditor()
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('product_code')
                        ->label('Product Code')
                        ->placeholder('e.g. SUP-PRD-001 or Model code')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)
                        ->helperText('Unique product code provided by the supplier.'),

                    Forms\Components\TextInput::make('product_name')
                        ->label('Product Name')
                        ->placeholder('e.g. Logitech G304 Wireless Gaming Mouse')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Select::make('supplier_id')
                        ->label('Supplier Name')
                        ->options(fn () => Supplier::pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpanFull()
                        ->helperText('Select the supplier who provides this product code.'),
                ])->columns(2),
        ];
    }
}
