<?php

namespace App\Filament\Resources\OrderResource\Schemas;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;

class OrderForm
{
    public static function schema(): array
    {
        return [
            // 1. Order Status & Metadata
            Forms\Components\Section::make('Order Information')
                ->description('Manage order status and payment details.')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->label('Order Status')
                        ->options([
                            'Processing' => 'Processing',
                            'Packaging' => 'Packaging',
                            'Shipping' => 'Shipping',
                            'Delivered' => 'Delivered',
                        ])
                        ->required()
                        ->native(false)
                        ->default('Processing')
                        ->selectablePlaceholder(false)
                        ->helperText('Changing status automatically updates the customer status in their My Orders dashboard.'),

                    Forms\Components\TextInput::make('order_number')
                        ->label('Order Number')
                        ->default(fn () => Order::generateOrderNumber())
                        ->disabled(fn (string $operation) => $operation === 'edit')
                        ->dehydrated()
                        ->required(),

                    Forms\Components\Select::make('payment_status')
                        ->label('Payment Status')
                        ->options([
                            'Paid' => 'Paid',
                            'Pending' => 'Pending',
                            'Failed' => 'Failed',
                            'Refunded' => 'Refunded',
                        ])
                        ->default('Paid')
                        ->native(false),

                    Forms\Components\TextInput::make('payment_method')
                        ->label('Payment Method')
                        ->default('Stripe Card Payment'),
                ])->columns(2),

            // 2. User Information
            Forms\Components\Section::make('User Information')
                ->description('Customer identity and primary/secondary contact numbers.')
                ->schema([
                    Forms\Components\TextInput::make('customer_name')
                        ->label('User Name')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Full name of customer'),

                    Forms\Components\TextInput::make('customer_email')
                        ->label('Email Address')
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->placeholder('customer@example.com'),

                    Forms\Components\TextInput::make('customer_phone')
                        ->label('Primary Phone')
                        ->tel()
                        ->required()
                        ->maxLength(50)
                        ->placeholder('+94 77 123 4567'),

                    Forms\Components\TextInput::make('customer_secondary_phone')
                        ->label('Secondary Phone (Optional)')
                        ->tel()
                        ->maxLength(50)
                        ->placeholder('+94 11 234 5678'),
                ])->columns(2),

            // 3. Order Details Table (Visual view + Repeater)
            Forms\Components\Section::make('Order Details')
                ->description('Ordered products, quantities, unit prices, and grand total calculation.')
                ->schema([
                    // Visual table summary in Edit/View
                    Forms\Components\ViewField::make('order_items_table')
                        ->label('')
                        ->view('filament.forms.components.order-items-table')
                        ->visible(fn (string $operation) => $operation === 'edit' || $operation === 'view'),

                    // Repeater for managing order items
                    Forms\Components\Repeater::make('items')
                        ->relationship('items')
                        ->label('Order Items Editor')
                        ->addActionLabel('+ Add Product Item')
                        ->defaultItems(1)
                        ->collapsible()
                        ->live()
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            self::recalculateOrderTotals($get, $set);
                        })
                        ->schema([
                            Forms\Components\Hidden::make('color'),
                            Forms\Components\Hidden::make('category_name'),
                            Forms\Components\Hidden::make('product_image')
                                ->default('/images/placeholder.png'),

                            // Row 1: Product Selection & Variant Selection
                            Forms\Components\Select::make('product_id')
                                ->label('Select Catalog Product')
                                ->options(function () {
                                    return Product::query()
                                        ->orderBy('name')
                                        ->pluck('name', 'id');
                                })
                                ->searchable()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                    if ($state) {
                                        $product = Product::with('variants')->find($state);
                                        if ($product) {
                                            $set('product_name', $product->name);

                                            // Category
                                            $cat = $product->categories()->first();
                                            $set('category_name', $cat?->name ?? 'Accessories');

                                            // Image
                                            $images = is_array($product->images) ? $product->images : [];
                                            $img = count($images) > 0 ? $images[0] : null;
                                            $imgUrl = $img ? ((str_starts_with($img, 'http') || str_starts_with($img, '/')) ? $img : asset('storage/'.$img)) : '/images/placeholder.png';
                                            $set('product_image', $imgUrl);

                                            // Reset variant selection
                                            $set('variant_id', null);
                                            $set('color', null);

                                            $hasVariants = $product->variants->isNotEmpty();
                                            if (! $hasVariants) {
                                                $availStock = (int) ($product->show_quantity ?? $product->quantity ?? 0);
                                                $set('available_stock', $availStock);

                                                $price = (float) ($product->sale_price ?? $product->price ?? 0);
                                                $set('unit_price', $price);

                                                $qty = (int) ($get('quantity') ?: 1);
                                                if ($availStock > 0 && $qty > $availStock) {
                                                    $qty = $availStock;
                                                    $set('quantity', $qty);
                                                }
                                                $set('total_price', round($price * $qty, 2));
                                            } else {
                                                // Prompt selection of variant
                                                $set('available_stock', 0);
                                                $set('unit_price', (float) ($product->sale_price ?? $product->price ?? 0));
                                                $set('total_price', 0.00);
                                            }
                                        }
                                    } else {
                                        $set('variant_id', null);
                                        $set('color', null);
                                        $set('available_stock', 0);
                                        $set('unit_price', 0.00);
                                        $set('total_price', 0.00);
                                    }
                                    self::recalculateOrderTotals($get, $set, isFromRepeaterItem: true);
                                })
                                ->columnSpan(['sm' => 12, 'md' => 6]),

                            Forms\Components\Select::make('variant_id')
                                ->label('Product Variation / Color')
                                ->placeholder(function (Get $get) {
                                    $pId = $get('product_id');
                                    if (! $pId) {
                                        return 'Select a product first';
                                    }
                                    return ProductVariant::where('product_id', $pId)->exists()
                                        ? 'Select available variation / color'
                                        : 'No variations (Standard Product)';
                                })
                                ->options(function (Get $get) {
                                    $pId = $get('product_id');
                                    if (! $pId) {
                                        return [];
                                    }
                                    return ProductVariant::where('product_id', $pId)
                                        ->get()
                                        ->mapWithKeys(function ($v) {
                                            $name = $v->color_name ?: $v->variant_name ?: "Variant #{$v->id}";
                                            $stock = (int) ($v->show_quantity ?? $v->stock ?? 0);
                                            $price = (float) ($v->sale_price ?? $v->price ?? 0);
                                            return [$v->id => "{$name} — (Stock: {$stock} | Rs. " . number_format($price, 2) . ")"];
                                        });
                                })
                                ->disabled(function (Get $get) {
                                    $pId = $get('product_id');
                                    if (! $pId) {
                                        return true;
                                    }
                                    return ! ProductVariant::where('product_id', $pId)->exists();
                                })
                                ->required(function (Get $get) {
                                    $pId = $get('product_id');
                                    return $pId && ProductVariant::where('product_id', $pId)->exists();
                                })
                                ->live()
                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                    if ($state) {
                                        $variant = ProductVariant::find($state);
                                        if ($variant) {
                                            $set('color', $variant->color_name);

                                            $baseName = Product::where('id', $get('product_id'))->value('name') ?? '';
                                            $displayName = $variant->color_name ? "{$baseName} ({$variant->color_name})" : ($variant->variant_name ?: $baseName);
                                            $set('product_name', $displayName);

                                            $stock = (int) ($variant->show_quantity ?? $variant->stock ?? 0);
                                            $set('available_stock', $stock);

                                            $price = (float) ($variant->sale_price ?? $variant->price ?? $get('unit_price') ?? 0);
                                            $set('unit_price', $price);

                                            $qty = (int) ($get('quantity') ?: 1);
                                            if ($stock > 0 && $qty > $stock) {
                                                $qty = $stock;
                                                $set('quantity', $qty);
                                            }
                                            $set('total_price', round($price * $qty, 2));

                                            if ($variant->image) {
                                                $vImg = (str_starts_with($variant->image, 'http') || str_starts_with($variant->image, '/'))
                                                    ? $variant->image
                                                    : asset('storage/'.$variant->image);
                                                $set('product_image', $vImg);
                                            }
                                        }
                                    } else {
                                        $set('color', null);
                                        $p = Product::find($get('product_id'));
                                        if ($p) {
                                            $set('product_name', $p->name);
                                            $set('available_stock', (int) ($p->show_quantity ?? $p->quantity ?? 0));
                                        }
                                    }
                                    self::recalculateOrderTotals($get, $set, isFromRepeaterItem: true);
                                })
                                ->columnSpan(['sm' => 12, 'md' => 6]),

                            // Row 2: Product Name, Available Quantity, Unit Price, Quantity, Amount
                            Forms\Components\TextInput::make('product_name')
                                ->label('Product Name')
                                ->required()
                                ->readOnly()
                                ->columnSpan(['sm' => 12, 'md' => 4]),

                            Forms\Components\TextInput::make('available_stock')
                                ->label('Available Quantity')
                                ->numeric()
                                ->readOnly()
                                ->dehydrated(false)
                                ->default(0)
                                ->suffix('units')
                                ->extraInputAttributes(['style' => 'font-weight: 700; color: #10b981;'])
                                ->helperText(function (Get $get) {
                                    $pId = $get('product_id');
                                    if (! $pId) {
                                        return 'Select a product first';
                                    }
                                    if (ProductVariant::where('product_id', $pId)->exists() && ! $get('variant_id')) {
                                        return 'Select variation for stock';
                                    }
                                    return 'Current inventory stock';
                                })
                                ->afterStateHydrated(function ($state, Set $set, Get $get) {
                                    $variantId = $get('variant_id');
                                    if ($variantId) {
                                        $v = ProductVariant::find($variantId);
                                        if ($v) {
                                            $set('available_stock', (int) ($v->show_quantity ?? $v->stock ?? 0));
                                            return;
                                        }
                                    }
                                    $productId = $get('product_id');
                                    if ($productId) {
                                        $p = Product::find($productId);
                                        if ($p) {
                                            $set('available_stock', (int) ($p->show_quantity ?? $p->quantity ?? 0));
                                        }
                                    }
                                })
                                ->columnSpan(['sm' => 6, 'md' => 2]),

                            Forms\Components\TextInput::make('unit_price')
                                ->label('Price Per Unit (Rs.)')
                                ->numeric()
                                ->required()
                                ->prefix('Rs. ')
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                    $qty = (int) ($get('quantity') ?: 1);
                                    $set('total_price', round((float) $state * $qty, 2));
                                    self::recalculateOrderTotals($get, $set, isFromRepeaterItem: true);
                                })
                                ->columnSpan(['sm' => 6, 'md' => 2]),

                            Forms\Components\TextInput::make('quantity')
                                ->label('No. of Units')
                                ->numeric()
                                ->default(1)
                                ->minValue(1)
                                ->maxValue(function (Get $get) {
                                    $stock = (int) ($get('available_stock') ?? 0);
                                    return $stock > 0 ? $stock : 1;
                                })
                                ->required()
                                ->live(onBlur: true)
                                ->helperText(function (Get $get) {
                                    $stock = (int) ($get('available_stock') ?? 0);
                                    return $stock > 0 ? "Max available: {$stock} units" : "Select product/variation";
                                })
                                ->rules([
                                    function (Get $get) {
                                        $stock = (int) ($get('available_stock') ?? 0);
                                        return function (string $attribute, $value, \Closure $fail) use ($stock) {
                                            if ($stock > 0 && (int) $value > $stock) {
                                                $fail("The quantity cannot exceed the available stock of {$stock} units.");
                                            }
                                        };
                                    },
                                ])
                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                    $stock = (int) ($get('available_stock') ?? 0);
                                    if ($stock > 0 && (int) $state > $stock) {
                                        $set('quantity', $stock);
                                        $state = $stock;
                                    }
                                    $unitPrice = (float) ($get('unit_price') ?: 0);
                                    $set('total_price', round($unitPrice * (int) $state, 2));
                                    self::recalculateOrderTotals($get, $set, isFromRepeaterItem: true);
                                })
                                ->columnSpan(['sm' => 6, 'md' => 2]),

                            Forms\Components\TextInput::make('total_price')
                                ->label('Amount (Rs.)')
                                ->numeric()
                                ->readOnly()
                                ->prefix('Rs. ')
                                ->dehydrated()
                                ->columnSpan(['sm' => 6, 'md' => 2]),
                        ])
                        ->columns(12),

                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('subtotal')
                                ->label('Subtotal (Rs.)')
                                ->numeric()
                                ->readOnly()
                                ->default(0.00)
                                ->prefix('Rs. ')
                                ->dehydrated(),

                            /*
                            // Shipping Fee commented out / hidden for now as requested
                            Forms\Components\TextInput::make('shipping_fee')
                                ->label('Shipping Fee (Rs.)')
                                ->numeric()
                                ->default(0.00)
                                ->prefix('Rs. '),
                            */
                            Forms\Components\Hidden::make('shipping_fee')
                                ->default(0.00)
                                ->dehydrated(),

                            Forms\Components\TextInput::make('total_amount')
                                ->label('Total Amount of Full Order (Rs.)')
                                ->numeric()
                                ->readOnly()
                                ->required()
                                ->default(0.00)
                                ->prefix('Rs. ')
                                ->dehydrated()
                                ->extraInputAttributes(['style' => 'font-weight: bold; font-size: 16px; color: #06b6d4;']),
                        ]),
                ]),

            // 4. Delivery & Shipping Address
            Forms\Components\Section::make('Delivery & Shipping Address')
                ->description('Physical delivery address for courier dispatch.')
                ->schema([
                    Forms\Components\TextInput::make('shipping_address_line_1')
                        ->label('Street Address')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Street name, building, house number')
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('shipping_address_line_2')
                        ->label('Apartment, Suite, Unit, etc. (Optional)')
                        ->maxLength(255)
                        ->placeholder('Apartment or suite number')
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('shipping_city')
                        ->label('City / Town')
                        ->required()
                        ->maxLength(100)
                        ->placeholder('e.g. Colombo, Kandy, Galle'),

                    Forms\Components\TextInput::make('shipping_state')
                        ->label('State / Province')
                        ->maxLength(100)
                        ->placeholder('e.g. Western Province'),

                    Forms\Components\TextInput::make('shipping_postal_code')
                        ->label('Postal Code')
                        ->maxLength(20)
                        ->placeholder('e.g. 00400'),

                    Forms\Components\TextInput::make('shipping_country')
                        ->label('Country')
                        ->default('Sri Lanka')
                        ->required()
                        ->maxLength(100),
                ])->columns(2),
        ];
    }

    public static function recalculateOrderTotals(Get $get, Set $set, bool $isFromRepeaterItem = false): void
    {
        if ($isFromRepeaterItem) {
            $unitPrice = (float) ($get('unit_price') ?: 0);
            $rawQty = $get('quantity');
            $qty = (is_numeric($rawQty) && (int) $rawQty > 0) ? (int) $rawQty : 1;
            $rowTotal = round($unitPrice * $qty, 2);
            $set('total_price', $rowTotal);

            $items = $get('../../items') ?: [];
            $subtotal = 0.0;
            foreach ($items as $item) {
                $p = (float) ($item['unit_price'] ?? 0);
                $rawItemQty = $item['quantity'] ?? 1;
                $q = (is_numeric($rawItemQty) && (int) $rawItemQty > 0) ? (int) $rawItemQty : 1;
                $subtotal += round($p * $q, 2);
            }
            $subtotal = round($subtotal, 2);
            $set('../../subtotal', $subtotal);
            $set('../../total_amount', $subtotal);
        } else {
            $items = $get('items') ?: [];
            $subtotal = 0.0;
            foreach ($items as $item) {
                $p = (float) ($item['unit_price'] ?? 0);
                $rawItemQty = $item['quantity'] ?? 1;
                $q = (is_numeric($rawItemQty) && (int) $rawItemQty > 0) ? (int) $rawItemQty : 1;
                $subtotal += round($p * $q, 2);
            }
            $subtotal = round($subtotal, 2);
            $set('subtotal', $subtotal);
            $set('total_amount', $subtotal);
        }
    }
}
