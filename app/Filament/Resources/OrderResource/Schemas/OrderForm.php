<?php

namespace App\Filament\Resources\OrderResource\Schemas;

use App\Models\Order;
use App\Models\Product;
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
                        ->schema([
                            Forms\Components\Select::make('product_id')
                                ->label('Select Catalog Product')
                                ->options(fn () => Product::pluck('name', 'id'))
                                ->searchable()
                                ->reactive()
                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                    if ($state) {
                                        $p = Product::find($state);
                                        if ($p) {
                                            $set('product_name', $p->name);
                                            $price = (float) ($p->sale_price ?? $p->price);
                                            $set('unit_price', $price);
                                            $qty = (int) ($get('quantity') ?: 1);
                                            $set('total_price', round($price * $qty, 2));

                                            $images = is_array($p->images) ? $p->images : [];
                                            $img = count($images) > 0 ? $images[0] : null;
                                            $imgUrl = $img ? ((str_starts_with($img, 'http') || str_starts_with($img, '/')) ? $img : asset('storage/' . $img)) : '/images/placeholder.png';
                                            $set('product_image', $imgUrl);
                                        }
                                    }
                                })
                                ->columnSpan(['sm' => 2, 'md' => 4]),

                            Forms\Components\TextInput::make('product_name')
                                ->label('Product Name')
                                ->required()
                                ->columnSpan(['sm' => 2, 'md' => 3]),

                            Forms\Components\TextInput::make('unit_price')
                                ->label('Price Per Unit (Rs.)')
                                ->numeric()
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                    $qty = (int) ($get('quantity') ?: 1);
                                    $set('total_price', round((float) $state * $qty, 2));
                                })
                                ->columnSpan(['sm' => 1, 'md' => 2]),

                            Forms\Components\TextInput::make('quantity')
                                ->label('No. of Units')
                                ->numeric()
                                ->default(1)
                                ->minValue(1)
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                    $unitPrice = (float) ($get('unit_price') ?: 0);
                                    $set('total_price', round($unitPrice * (int) $state, 2));
                                })
                                ->columnSpan(['sm' => 1, 'md' => 1]),

                            Forms\Components\TextInput::make('total_price')
                                ->label('Amount (Rs.)')
                                ->numeric()
                                ->readOnly()
                                ->columnSpan(['sm' => 2, 'md' => 2]),

                            Forms\Components\Hidden::make('product_image')
                                ->default('/images/placeholder.png'),
                        ])
                        ->columns(12)
                        ->collapsible()
                        ->defaultItems(1)
                        ->addActionLabel('+ Add Product Item')
                        ->live()
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            $items = $get('items') ?? [];
                            $total = 0;
                            foreach ($items as $item) {
                                $total += (float) ($item['total_price'] ?? 0);
                            }
                            $set('subtotal', $total);
                            $set('total_amount', $total);
                        }),

                    Forms\Components\Grid::make(3)
                        ->schema([
                            Forms\Components\TextInput::make('subtotal')
                                ->label('Subtotal (Rs.)')
                                ->numeric()
                                ->readOnly()
                                ->prefix('Rs. '),

                            Forms\Components\TextInput::make('shipping_fee')
                                ->label('Shipping Fee (Rs.)')
                                ->numeric()
                                ->default(0.00)
                                ->prefix('Rs. '),

                            Forms\Components\TextInput::make('total_amount')
                                ->label('Total Amount of Full Order (Rs.)')
                                ->numeric()
                                ->required()
                                ->prefix('Rs. ')
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
}
