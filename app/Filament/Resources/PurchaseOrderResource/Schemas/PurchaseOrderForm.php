<?php

namespace App\Filament\Resources\PurchaseOrderResource\Schemas;

use App\Models\InventoryProduct;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;

class PurchaseOrderForm
{
    public static function schema(): array
    {
        return [
            // 1. Order Header Info
            Forms\Components\Section::make('Purchase Order Information')
                ->description('Manage purchase order number, supplier selection, approval status, and creation date.')
                ->schema([
                    Forms\Components\TextInput::make('po_number')
                        ->label('Purchase Order No')
                        ->default(fn () => PurchaseOrder::generatePoNumber())
                        ->disabled(fn (string $operation) => $operation === 'edit')
                        ->dehydrated()
                        ->required()
                        ->helperText('Auto-generated sequence: COM-P-{Year}-000001')
                        ->columnSpan(1),

                    Forms\Components\Select::make('supplier_id')
                        ->label('Select Supplier')
                        ->options(fn () => Supplier::pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->reactive()
                        ->required()
                        ->columnSpan(1),

                    Forms\Components\Select::make('status')
                        ->label('Purchase Order Status')
                        ->options([
                            'Planning' => 'Planning',
                            'Approved' => 'Approved',
                            'Rejected' => 'Rejected',
                        ])
                        ->default('Planning')
                        ->native(false)
                        ->required()
                        ->columnSpan(1),

                    Forms\Components\DateTimePicker::make('created_at')
                        ->label('Order Created Date & Time')
                        ->default(now())
                        ->native(false)
                        ->seconds(false)
                        ->dehydrated()
                        ->helperText('Local date and time when the purchase order is issued.')
                        ->columnSpan(1),

                    Forms\Components\Textarea::make('notes')
                        ->label('Order Notes / Remarks')
                        ->placeholder('Optional delivery instructions or notes for supplier...')
                        ->columnSpanFull(),
                ])->columns(2),

            // 2. Order Items
            Forms\Components\Section::make('Order Items')
                ->description('Add order items by product code. Product names are automatically loaded from Inventory Products.')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->relationship('items')
                        ->label('Products List')
                        ->addActionLabel('+ Add Product Section')
                        ->schema([
                            Forms\Components\Hidden::make('inventory_product_id'),
                            Forms\Components\Hidden::make('product_variant_id'),

                            Forms\Components\Select::make('product_code')
                                ->label('Product Code')
                                ->options(function () {
                                    return InventoryProduct::orderBy('product_name')
                                        ->get()
                                        ->mapWithKeys(fn ($p) => [
                                            $p->product_code => "{$p->product_code} — {$p->product_name}",
                                        ]);
                                })
                                ->searchable()
                                ->reactive()
                                ->afterStateUpdated(function ($state, Set $set) {
                                    if ($state) {
                                        $invProduct = InventoryProduct::where('product_code', $state)->first();
                                        if ($invProduct) {
                                            $set('product_name', $invProduct->product_name);
                                            $set('inventory_product_id', $invProduct->id);
                                        }

                                        // Auto-link store product variant if applicable
                                        $storeProduct = \App\Models\Product::with('variants')->where('product_code', $state)->first();
                                        if ($storeProduct && $storeProduct->variants->isNotEmpty()) {
                                            // Keep color_name open for selection
                                        } else {
                                            $set('color_name', null);
                                            $set('product_variant_id', null);
                                        }
                                    }
                                })
                                ->required()
                                ->columnSpan(['sm' => 2, 'md' => 4]),

                            Forms\Components\TextInput::make('product_name')
                                ->label('Product Name')
                                ->required()
                                ->readOnly()
                                ->helperText('Auto-filled from Inventory Products')
                                ->columnSpan(['sm' => 2, 'md' => 3]),

                            Forms\Components\TextInput::make('color_name')
                                ->label('Color Variant')
                                ->placeholder('Type color (e.g. Red, Black)')
                                ->datalist(function (Get $get) {
                                    $code = $get('product_code');
                                    $defaultColors = ['Red', 'White', 'Black', 'Blue', 'Silver', 'Grey', 'Gold', 'Green', 'Yellow', 'RGB', 'Pink', 'Purple', 'Orange'];
                                    if ($code) {
                                        $product = \App\Models\Product::with('variants')->where('product_code', $code)->first();
                                        if ($product && $product->variants->isNotEmpty()) {
                                            $variantColors = $product->variants->pluck('color_name')->filter()->toArray();

                                            return array_values(array_unique(array_merge($variantColors, $defaultColors)));
                                        }
                                    }

                                    return $defaultColors;
                                })
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                    $code = $get('product_code');
                                    if ($code && $state) {
                                        $product = \App\Models\Product::with('variants')->where('product_code', $code)->first();
                                        $variant = $product?->variants->first(fn ($v) => strcasecmp((string) $v->color_name, trim((string) $state)) === 0);
                                        if ($variant) {
                                            $set('product_variant_id', $variant->id);
                                        } else {
                                            $set('product_variant_id', null);
                                        }
                                    } else {
                                        $set('product_variant_id', null);
                                    }
                                })
                                ->helperText('Type any color or pick from suggestions')
                                ->columnSpan(['sm' => 2, 'md' => 2]),

                            Forms\Components\TextInput::make('product_quantity')
                                ->label('Quantity')
                                ->numeric()
                                ->minValue(1)
                                ->default(1)
                                ->required()
                                ->columnSpan(['sm' => 1, 'md' => 1]),

                            Forms\Components\Select::make('product_condition')
                                ->label('Condition')
                                ->options([
                                    'Brand New' => 'Brand New',
                                    'Used' => 'Used',
                                ])
                                ->default('Brand New')
                                ->formatStateUsing(fn ($state) => $state === 'Brandnew' ? 'Brand New' : ($state ?? 'Brand New'))
                                ->native(false)
                                ->required()
                                ->columnSpan(['sm' => 1, 'md' => 2]),
                        ])
                        ->columns(['sm' => 2, 'md' => 12])
                        ->defaultItems(1)
                        ->collapsible()
                        ->itemLabel(function (array $state): ?string {
                            if (! isset($state['product_name']) || empty($state['product_name'])) {
                                return 'New Item';
                            }
                            $cond = ($state['product_condition'] ?? '') === 'Brandnew' ? 'Brand New' : ($state['product_condition'] ?? 'Brand New');
                            $color = ! empty($state['color_name']) ? " (🎨 {$state['color_name']})" : '';
                            $qty = $state['product_quantity'] ?? 1;

                            return "{$state['product_name']}{$color} [{$cond}] × {$qty}";
                        })
                        ->columnSpanFull(),
                ]),
        ];
    }
}
