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
                ->description('Manage purchase order number, supplier selection, and approval status.')
                ->schema([
                    Forms\Components\TextInput::make('po_number')
                        ->label('Purchase Order No')
                        ->default(fn () => PurchaseOrder::generatePoNumber())
                        ->disabled(fn (string $operation) => $operation === 'edit')
                        ->dehydrated()
                        ->required()
                        ->helperText('Auto-generated sequence: COM-P-{Year}-000001'),

                    Forms\Components\Select::make('supplier_id')
                        ->label('Select Supplier')
                        ->options(fn () => Supplier::pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->reactive()
                        ->required(),

                    Forms\Components\Select::make('status')
                        ->label('Purchase Order Status')
                        ->options([
                            'Planning' => 'Planning',
                            'Approved' => 'Approved',
                            'Rejected' => 'Rejected',
                        ])
                        ->default('Planning')
                        ->native(false)
                        ->required(),

                    Forms\Components\Textarea::make('notes')
                        ->label('Order Notes / Remarks')
                        ->placeholder('Optional delivery instructions or notes for supplier...')
                        ->columnSpanFull(),
                ])->columns(3),

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

                            Forms\Components\Select::make('product_code')
                                ->label('Product Code')
                                ->options(function (Get $get) {
                                    $supplierId = $get('../../supplier_id');
                                    $query = InventoryProduct::query();
                                    if ($supplierId) {
                                        $query->where('supplier_id', $supplierId);
                                    }
                                    return $query->get()->mapWithKeys(fn ($p) => [
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
                                    }
                                })
                                ->required()
                                ->columnSpan(['sm' => 2, 'md' => 4]),

                            Forms\Components\TextInput::make('product_name')
                                ->label('Product Name')
                                ->required()
                                ->readOnly()
                                ->helperText('Auto-filled from Inventory Products')
                                ->columnSpan(['sm' => 2, 'md' => 4]),

                            Forms\Components\TextInput::make('product_quantity')
                                ->label('Product Quantity')
                                ->numeric()
                                ->minValue(1)
                                ->default(1)
                                ->required()
                                ->columnSpan(['sm' => 1, 'md' => 2]),

                            Forms\Components\Select::make('product_condition')
                                ->label('Product Condition')
                                ->options([
                                    'Brandnew' => 'Brandnew',
                                    'Used' => 'Used',
                                ])
                                ->default('Brandnew')
                                ->native(false)
                                ->required()
                                ->columnSpan(['sm' => 1, 'md' => 2]),
                        ])
                        ->columns(['sm' => 2, 'md' => 12])
                        ->defaultItems(1)
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => 
                            isset($state['product_name']) && !empty($state['product_name']) 
                                ? "{$state['product_name']} ({$state['product_condition']}) × {$state['product_quantity']}" 
                                : 'New Item'
                        )
                        ->columnSpanFull(),
                ]),
        ];
    }
}
