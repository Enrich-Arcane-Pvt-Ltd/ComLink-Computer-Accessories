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
                    Forms\Components\Placeholder::make('color_variants_pricing_notice')
                        ->label('Color Variations Pricing Active')
                        ->content('Pricing and GRN costs for this product are managed individually per color variation in the "Color Variations — Inventory Product Information" section below.')
                        ->columnSpanFull()
                        ->visible(fn (Get $get, ?InventoryProduct $record) => (bool) ($get('has_color_variants') || ($record && ($record->storefrontProduct?->hasColorVariants() || $record->storefrontProduct?->variants()->exists())))),

                    Forms\Components\TextInput::make('grn_price')
                        ->label('GRN Unit Cost')
                        ->numeric()
                        ->prefix($currency)
                        ->formatStateUsing(fn ($state, ?InventoryProduct $record) => $state ?? ($record ? round((float) ($record->latest_grn_price ?? $record->storefrontProduct?->grn_price ?? 0), 2) : null))
                        ->readOnly()
                        ->dehydrated(false)
                        ->hidden(fn (Get $get, ?InventoryProduct $record) => (bool) ($get('has_color_variants') || ($record && ($record->storefrontProduct?->hasColorVariants() || $record->storefrontProduct?->variants()->exists())))),

                    Forms\Components\TextInput::make('price')
                        ->label('Original Price')
                        ->numeric()
                        ->prefix($currency)
                        ->formatStateUsing(fn ($state, ?InventoryProduct $record) => $state ?? ($record && (float) ($record->storefrontProduct?->price ?? 0) > 0 ? (float) $record->storefrontProduct->price : null))
                        ->live(onBlur: true)
                        ->hidden(fn (Get $get, ?InventoryProduct $record) => (bool) ($get('has_color_variants') || ($record && ($record->storefrontProduct?->hasColorVariants() || $record->storefrontProduct?->variants()->exists()))))
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
                        ->formatStateUsing(function ($state, ?InventoryProduct $record) {
                            if ($state !== null) return $state;
                            $product = $record?->storefrontProduct;
                            $orig = (float) ($product?->price ?? 0);
                            $sale = (float) ($product?->sale_price ?? $orig);
                            return ($orig > 0 && $sale > 0 && $sale < $orig) ? (int) round((($orig - $sale) / $orig) * 100) : 0;
                        })
                        ->dehydrated(false)
                        ->live(onBlur: true)
                        ->hidden(fn (Get $get, ?InventoryProduct $record) => (bool) ($get('has_color_variants') || ($record && ($record->storefrontProduct?->hasColorVariants() || $record->storefrontProduct?->variants()->exists()))))
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
                        ->formatStateUsing(fn ($state, ?InventoryProduct $record) => $state ?? ($record && (float) ($record->storefrontProduct?->sale_price ?? 0) > 0 ? (float) $record->storefrontProduct->sale_price : ($record && (float) ($record->storefrontProduct?->price ?? 0) > 0 ? (float) $record->storefrontProduct->price : null)))
                        ->live(onBlur: true)
                        ->hidden(fn (Get $get, ?InventoryProduct $record) => (bool) ($get('has_color_variants') || ($record && ($record->storefrontProduct?->hasColorVariants() || $record->storefrontProduct?->variants()->exists()))))
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
                        ->formatStateUsing(fn ($state, ?InventoryProduct $record) => $state ?? (bool) ($record?->storefrontProduct?->is_visible ?? false))
                        ->default(true),

                    Forms\Components\Toggle::make('is_featured')
                        ->label('Best Seller (Featured)')
                        ->formatStateUsing(fn ($state, ?InventoryProduct $record) => $state ?? (bool) ($record?->storefrontProduct?->is_featured ?? false))
                        ->default(false),
                ])->columns(3),

            Forms\Components\Section::make('Color Variations — Inventory Product Information')
                ->description('Individual inventory product information, procurement cost, and storefront pricing for each color variation.')
                ->visible(function (?InventoryProduct $record, Get $get) {
                    $prod = $record?->storefrontProduct;
                    return (bool) ($prod?->hasColorVariants() || ! empty($get('color_variants')));
                })
                ->schema([
                    Forms\Components\Repeater::make('color_variants')
                        ->label('Color Variations Breakdown')
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false)
                        ->itemLabel(fn (array $state): ?string => 
                            ! empty($state['color_name']) 
                                ? "Color Variant: {$state['color_name']} — " . ($state['variant_name'] ?? '') . " [Stock: " . ($state['inventory_stock'] ?? 0) . " units]"
                                : 'Color Variant'
                        )
                        ->schema([
                            Forms\Components\Grid::make(4)
                                ->schema([
                                    Forms\Components\FileUpload::make('image')
                                        ->label('Color Image')
                                        ->image()
                                        ->disk('public')
                                        ->directory('products/variants')
                                        ->visibility('public')
                                        ->disabled()
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('color_name')
                                        ->label('Color Name')
                                        ->disabled()
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('variant_name')
                                        ->label('Product Variant Name')
                                        ->disabled()
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('inventory_stock')
                                        ->label('Available Stock (Units)')
                                        ->numeric()
                                        ->disabled()
                                        ->columnSpan(1),
                                ]),

                            Forms\Components\Grid::make(4)
                                ->schema([
                                    Forms\Components\TextInput::make('grn_price')
                                        ->label('GRN Unit Cost')
                                        ->numeric()
                                        ->prefix($currency)
                                        ->live(onBlur: true),

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
                                ]),
                        ])
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Inventory Product Information')
                ->description('Master catalog information (Read-only. Managed under Inventory Products).')
                ->schema([
                    Forms\Components\TextInput::make('product_code')
                        ->label('Product Code')
                        ->disabled(),

                    Forms\Components\TextInput::make('product_name')
                        ->label('Product Name')
                        ->disabled(),

                    Forms\Components\Select::make('supplier_id')
                        ->label('Supplier')
                        ->relationship('supplier', 'name')
                        ->disabled(),

                    Forms\Components\FileUpload::make('product_image')
                        ->label('Product Image')
                        ->image()
                        ->disk('public')
                        ->directory('inventory-products')
                        ->visibility('public')
                        ->disabled()
                        ->dehydrated(false),
                ])
                ->disabled()
                ->columns(2),
        ];
    }
}
