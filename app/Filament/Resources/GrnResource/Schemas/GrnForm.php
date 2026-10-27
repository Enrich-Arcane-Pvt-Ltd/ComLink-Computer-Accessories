<?php

namespace App\Filament\Resources\GrnResource\Schemas;

use App\Models\Grn;
use App\Models\InventoryProduct;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Str;

class GrnForm
{
    public static function schema(): array
    {
        return [
            // 1. GRN Header
            Forms\Components\Section::make('GRN Invoice Details')
                ->description('Choose whether this GRN is linked to a system Purchase Order or is a Direct GRN (personal/direct supplier purchase).')
                ->schema([
                    Forms\Components\ToggleButtons::make('grn_type')
                        ->label('GRN Mode / Purchase Order Type')
                        ->options([
                            'po'     => 'From Purchase Order',
                            'direct' => 'Direct GRN (Without PO)',
                        ])
                        ->icons([
                            'po'     => 'heroicon-m-document-check',
                            'direct' => 'heroicon-m-bolt',
                        ])
                        ->colors([
                            'po'     => 'primary',
                            'direct' => 'warning',
                        ])
                        ->default('po')
                        ->inline()
                        ->live()
                        ->afterStateUpdated(function ($state, Set $set, Get $get) {
                            if ($state === 'direct') {
                                $set('purchase_order_id', null);
                                if (! $get('direct_po_number')) {
                                    $set('direct_po_number', Grn::generateDirectPoNumber());
                                }
                            } else {
                                $set('direct_po_number', null);
                            }
                        })
                        ->afterStateHydrated(function ($component, ?Grn $record) {
                            if ($record) {
                                $component->state($record->purchase_order_id ? 'po' : ($record->grn_type ?: 'direct'));
                            }
                        })
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('grn_number')
                        ->label('Purchase GRN Invoice No')
                        ->default(fn () => Grn::generateGrnNumber())
                        ->disabled(fn (string $operation) => $operation === 'edit')
                        ->dehydrated()
                        ->required()
                        ->helperText('Auto-generated: COM-GRN-{Year}INV-000001'),

                    Forms\Components\Select::make('supplier_id')
                        ->label('Select Supplier')
                        ->options(fn () => Supplier::pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->reactive()
                        ->required(),

                    // Shown when GRN Mode = 'From Purchase Order'
                    Forms\Components\Select::make('purchase_order_id')
                        ->label('Purchase Order No')
                        ->placeholder('Select Purchase Order')
                        ->visible(fn (Get $get) => ($get('grn_type') ?? 'po') === 'po')
                        ->required(fn (Get $get) => ($get('grn_type') ?? 'po') === 'po')
                        ->options(function (Get $get) {
                            $supplierId = $get('supplier_id');
                            $query = PurchaseOrder::query();
                            if ($supplierId) {
                                $query->where('supplier_id', $supplierId);
                            }

                            return $query->with('grns')->get()->mapWithKeys(function ($po) {
                                $tag = $po->grns->isNotEmpty() ? ' (GRN Done - '.$po->grns->first()->grn_number.')' : '';

                                return [$po->id => "{$po->po_number}{$tag}"];
                            });
                        })
                        ->searchable()
                        ->reactive()
                        ->afterStateUpdated(function ($state, Set $set) {
                            if ($state) {
                                $po = PurchaseOrder::with('items')->find($state);
                                if ($po) {
                                    $set('supplier_id', $po->supplier_id);
                                    $itemsData = [];
                                    foreach ($po->items as $item) {
                                        $itemsData[(string) Str::uuid()] = [
                                            'inventory_product_id' => $item->inventory_product_id,
                                            'product_variant_id'   => $item->product_variant_id,
                                            'color_name'           => $item->color_name,
                                            'product_code'         => $item->product_code,
                                            'product_name'         => $item->product_name,
                                            'product_condition'    => $item->product_condition ?: 'Brandnew',
                                            'product_quantity'     => (int) ($item->product_quantity ?: 1),
                                            'price_per_unit'       => 0.00,
                                            'total'                => 0.00,
                                        ];
                                    }
                                    $set('items', $itemsData);
                                    $set('sub_total', 0.00);
                                }
                            }
                        })
                        ->helperText('Selecting a PO automatically loads its products into the table below.'),

                    // Shown when GRN Mode = 'Direct GRN (Without PO)'
                    Forms\Components\TextInput::make('direct_po_number')
                        ->label('Direct Reference / PO No')
                        ->default(fn () => Grn::generateDirectPoNumber())
                        ->visible(fn (Get $get) => ($get('grn_type') ?? 'po') === 'direct')
                        ->required(fn (Get $get) => ($get('grn_type') ?? 'po') === 'direct')
                        ->readOnly()
                        ->dehydrated()
                        ->helperText('Auto-generated Direct Reference No (no system Purchase Order required).'),

                    Forms\Components\DateTimePicker::make('created_at')
                        ->label('Created Date & Time')
                        ->default(now())
                        ->disabled(fn (string $operation) => $operation === 'edit')
                        ->dehydrated(),
                ])->columns(2),

            // 2. Received Items
            Forms\Components\Section::make('Received Products')
                ->description('List of goods received with price per unit and auto-calculated totals.')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->relationship('items')
                        ->label('GRN Products')
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
                                        ])
                                        ->toArray();
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

                                        $storeProduct = \App\Models\Product::with('variants')->where('product_code', $state)->first();
                                        if (! $storeProduct || $storeProduct->variants->isEmpty()) {
                                            $set('color_name', null);
                                            $set('product_variant_id', null);
                                        }
                                    }
                                })
                                ->required()
                                ->columnSpan(['sm' => 2, 'md' => 3]),

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
                                            if ((float) $variant->grn_price > 0) {
                                                $set('price_per_unit', (float) $variant->grn_price);
                                            }
                                        } else {
                                            $set('product_variant_id', null);
                                        }
                                    } else {
                                        $set('product_variant_id', null);
                                    }
                                    self::recalculateRowAndSubtotal($get, $set);
                                })
                                ->helperText('Type any color or pick from suggestions')
                                ->columnSpan(['sm' => 2, 'md' => 2]),

                            Forms\Components\Select::make('product_condition')
                                ->label('Condition')
                                ->options([
                                    'Brandnew' => 'Brandnew',
                                    'Used' => 'Used',
                                ])
                                ->default('Brandnew')
                                ->native(false)
                                ->required()
                                ->columnSpan(['sm' => 1, 'md' => 2]),

                            Forms\Components\TextInput::make('price_per_unit')
                                ->label('Price Per Unit (Rs.)')
                                ->numeric()
                                ->minValue(0)
                                ->default(0.00)
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateRowAndSubtotal($get, $set))
                                ->columnSpan(['sm' => 1, 'md' => 2]),

                            Forms\Components\TextInput::make('product_quantity')
                                ->label('Quantity')
                                ->numeric()
                                ->minValue(1)
                                ->default(1)
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateRowAndSubtotal($get, $set))
                                ->columnSpan(['sm' => 1, 'md' => 1]),

                            Forms\Components\TextInput::make('total')
                                ->label('Total (Rs.)')
                                ->numeric()
                                ->readOnly()
                                ->default(0.00)
                                ->dehydrated()
                                ->extraInputAttributes(['style' => 'font-weight: 700; font-family: monospace; color: #38bdf8; min-width: 120px;'])
                                ->columnSpan(['sm' => 2, 'md' => 2]),
                        ])
                        ->columns(['sm' => 2, 'md' => 15])
                        ->defaultItems(fn () => request()->query('purchase_order_id') ? 0 : 1)
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => isset($state['product_name']) && ! empty($state['product_name'])
                                ? "{$state['product_name']}".(! empty($state['color_name']) ? " (🎨 {$state['color_name']})" : '')." [{$state['product_condition']}] × {$state['product_quantity']} — Rs. ".number_format((float) ($state['total'] ?? 0), 2)
                                : 'Product Item'
                        )
                        ->columnSpanFull(),

                    // 3. Sub Total Footer
                    Forms\Components\Group::make([
                        Forms\Components\TextInput::make('sub_total')
                            ->label('Sub Total (Sum of Total)')
                            ->prefix('Rs. ')
                            ->numeric()
                            ->readOnly()
                            ->default(0.00)
                            ->dehydrated()
                            ->extraInputAttributes(['style' => 'font-size: 1.15rem; font-weight: 800; color: #38bdf8;']),
                    ])->columnSpanFull(),
                ]),
        ];
    }

    public static function recalculateRowAndSubtotal(Get $get, Set $set): void
    {
        $price = (float) ($get('price_per_unit') ?: 0);
        $qty = (int) ($get('product_quantity') ?: 1);
        $rowTotal = round($price * $qty, 2);
        $set('total', $rowTotal);

        // Calculate sum of all items
        $items = $get('../../items') ?: [];
        $subTotal = 0;
        foreach ($items as $item) {
            $p = (float) ($item['price_per_unit'] ?? 0);
            $q = (int) ($item['product_quantity'] ?? 1);
            $subTotal += round($p * $q, 2);
        }
        $set('../../sub_total', round($subTotal, 2));
    }
}
