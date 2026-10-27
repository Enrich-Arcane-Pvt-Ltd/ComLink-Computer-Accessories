<?php

declare(strict_types=1);

namespace App\Filament\Resources\InventoryResource\Schemas;

use App\Models\InventoryProduct;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;

class InventoryForm
{
    public static function schema(): array
    {
        $currency = config('app.currency_symbol', 'Rs. ');

        return [
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

                    Forms\Components\Placeholder::make('latest_grn_price')
                        ->label('Latest GRN Unit Cost')
                        ->content(fn (?InventoryProduct $record): string => 'Rs. ' . number_format((float) ($record?->latest_grn_price ?? 0), 2)),

                    Forms\Components\Placeholder::make('latest_selling_price')
                        ->label('Storefront Latest Price')
                        ->content(fn (?InventoryProduct $record): string => 'Rs. ' . number_format((float) ($record?->latest_price ?? 0), 2)),
                ])->columns(5),

            Forms\Components\Section::make('Website Pricing, Promotion & Visibility')
                ->description('Set the selling price, discount promotion, and toggle whether this product is displayed on the website.')
                ->schema([
                    Forms\Components\TextInput::make('grn_price')
                        ->label('GRN Unit Cost')
                        ->numeric()
                        ->prefix($currency)
                        ->readOnly()
                        ->dehydrated(false),

                    Forms\Components\TextInput::make('price')
                        ->label('Original Price')
                        ->numeric()
                        ->prefix($currency)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                            $orig = (float) $state;
                            $latest = (float) $get('sale_price');
                            $pct = (float) $get('discount_percentage');

                            if ($orig > 0 && $latest > 0) {
                                $discount = max(0, min(100, round((($orig - $latest) / $orig) * 100)));
                                $set('discount_percentage', $discount);
                            } elseif ($orig > 0 && $pct > 0) {
                                $set('sale_price', round($orig * (1 - ($pct / 100)), 2));
                            } elseif ($orig > 0 && $latest <= 0) {
                                $set('sale_price', $orig);
                            }
                        }),

                    Forms\Components\TextInput::make('discount_percentage')
                        ->label('Discount Percentage')
                        ->numeric()
                        ->suffix('%')
                        ->dehydrated(false)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                            $orig = (float) $get('price');
                            $pct = (float) $state;
                            if ($orig > 0) {
                                $latest = round($orig * (1 - ($pct / 100)), 2);
                                $set('sale_price', $latest);
                            }
                        }),

                    Forms\Components\TextInput::make('sale_price')
                        ->label('Latest Selling Price')
                        ->numeric()
                        ->prefix($currency)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                            $orig = (float) $get('price');
                            $latest = (float) $state;
                            if ($orig > 0 && $latest > 0) {
                                $discount = max(0, min(100, round((($orig - $latest) / $orig) * 100)));
                                $set('discount_percentage', $discount);
                            }
                        }),

                    Forms\Components\Toggle::make('is_visible')
                        ->label('Show on Website (Visible to Customers)')
                        ->default(true),

                    Forms\Components\Toggle::make('is_featured')
                        ->label('Best Seller (Featured)')
                        ->default(false),
                ])->columns(3),

            Forms\Components\Section::make('Inventory Product Information')
                ->description('Product code, name, and warehouse catalog info.')
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
                        ->preload(),

                    Forms\Components\FileUpload::make('product_image')
                        ->label('Product Image')
                        ->image()
                        ->disk('public')
                        ->directory('inventory-products')
                        ->visibility('public')
                        ->imageEditor(),
                ])->columns(2),
        ];
    }
}
