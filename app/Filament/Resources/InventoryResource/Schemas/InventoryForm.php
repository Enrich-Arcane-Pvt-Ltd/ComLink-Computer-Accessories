<?php

declare(strict_types=1);

namespace App\Filament\Resources\InventoryResource\Schemas;

use App\Models\InventoryProduct;
use Filament\Forms;

class InventoryForm
{
    public static function schema(): array
    {
        return [
            Forms\Components\Section::make('Inventory Product Information')
                ->description('Supplier product code, name, and warehouse catalog info.')
                ->schema([
                    Forms\Components\TextInput::make('product_code')
                        ->label('Product Code')
                        ->required()
                        ->disabled(fn (string $operation) => $operation === 'edit')
                        ->dehydrated()
                        ->unique(InventoryProduct::class, 'product_code', ignoreRecord: true),

                    Forms\Components\TextInput::make('product_name')
                        ->label('Product Name')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Select::make('supplier_id')
                        ->label('Supplier')
                        ->relationship('supplier', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\FileUpload::make('product_image')
                        ->label('Product Image')
                        ->image()
                        ->disk('public')
                        ->directory('inventory-products')
                        ->visibility('public')
                        ->imageEditor(),
                ])->columns(2),

            Forms\Components\Section::make('Current Warehouse Stock & Valuation')
                ->schema([
                    Forms\Components\Placeholder::make('total_stock')
                        ->label('Total Received Stock (GRN)')
                        ->content(fn (?InventoryProduct $record): string => ($record?->total_stock ?? 0) . ' units'),

                    Forms\Components\Placeholder::make('sold_count')
                        ->label('Total Quantity Sold')
                        ->content(fn (?InventoryProduct $record): string => ($record?->sold_count ?? 0) . ' units'),

                    Forms\Components\Placeholder::make('available_stock')
                        ->label('Available Warehouse Stock')
                        ->content(fn (?InventoryProduct $record): string => ($record?->available_stock ?? 0) . ' units'),

                    Forms\Components\Placeholder::make('latest_selling_price')
                        ->label('Storefront Latest Price')
                        ->content(fn (?InventoryProduct $record): string => 'Rs. ' . number_format((float) ($record?->latest_price ?? 0), 2)),

                    Forms\Components\Placeholder::make('latest_grn_price')
                        ->label('Latest GRN Unit Cost')
                        ->content(fn (?InventoryProduct $record): string => 'Rs. ' . number_format((float) ($record?->latest_grn_price ?? 0), 2)),
                ])->columns(3),
        ];
    }
}
