<?php

declare(strict_types=1);

namespace App\Filament\Resources\InventoryResource\Tables;

use App\Models\GrnItem;
use App\Models\InventoryProduct;
use App\Models\OrderItem;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class InventoryTable
{
    public static function table(Table $table): Table
    {
        $currency = config('app.currency_symbol', 'Rs. ');

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('No')
                    ->rowIndex()
                    ->extraCellAttributes(['style' => 'vertical-align: top; padding-top: 10px; padding-bottom: 10px;']),

                Tables\Columns\ImageColumn::make('product_image')
                    ->label('Image')
                    ->getStateUsing(fn (InventoryProduct $record): ?string => $record->product_image
                        ?? $record->storefrontProduct?->images[0]
                        ?? null
                    )
                    ->disk('public')
                    ->defaultImageUrl('/images/placeholder.png')
                    ->extraImgAttributes(['class' => 'rounded-lg object-cover ring-1 ring-slate-200 dark:ring-slate-700'])
                    ->extraCellAttributes(['style' => 'vertical-align: top; padding-top: 10px; padding-bottom: 10px;']),

                Tables\Columns\ViewColumn::make('product_info')
                    ->label('Product Details')
                    ->view('filament.tables.columns.inventory-single-product-name')
                    ->searchable(query: fn ($query, $search) => $query
                        ->where('product_name', 'like', '%' . $search . '%')
                        ->orWhere('product_code', 'like', '%' . $search . '%')
                    )
                    ->sortable(query: fn ($query, $direction) => $query->orderBy('product_name', $direction))
                    ->extraHeaderAttributes(['style' => 'width: 240px; max-width: 250px;'])
                    ->extraCellAttributes(['style' => 'vertical-align: top; padding-top: 10px; padding-bottom: 10px; width: 240px; max-width: 250px;']),

                Tables\Columns\TextColumn::make('supplier_display')
                    ->label('Supplier')
                    ->getStateUsing(fn (InventoryProduct $record): string => $record->supplier?->name
                        ?? $record->grnItems->sortByDesc('id')->first()?->grn?->supplier?->name
                        ?? 'Direct Stock'
                    )
                    ->description(fn (InventoryProduct $record): string => 'Last Added: ' . ($record->grnItems->sortByDesc('id')->first()?->created_at?->format('M d, Y h:i A') ?? 'N/A'))
                    ->icon('heroicon-o-truck')
                    ->iconColor('success')
                    ->weight('medium')
                    ->extraCellAttributes(['style' => 'vertical-align: top; padding-top: 10px; padding-bottom: 10px; min-width: 170px;']),

                Tables\Columns\ViewColumn::make('stock_counts')
                    ->label('Inventory Stock')
                    ->view('filament.tables.columns.inventory-stock-counts')
                    ->extraCellAttributes(['style' => 'vertical-align: top; padding-top: 10px; padding-bottom: 10px; min-width: 160px;']),

                Tables\Columns\ViewColumn::make('pricing_info')
                    ->label('Pricing (Web & GRN)')
                    ->view('filament.tables.columns.inventory-pricing')
                    ->extraCellAttributes(['style' => 'vertical-align: top; padding-top: 10px; padding-bottom: 10px; min-width: 155px;']),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordAction('stock_history')
            ->actions([
                // ── 1. Set Pricing & Show on Website (Directly from Inventory) ──
                Tables\Actions\Action::make('web_pricing')
                    ->label(fn (InventoryProduct $record): string => $record->storefrontProduct?->is_visible ? 'Visible on Web' : 'Show on Web')
                    ->tooltip('Set Selling Price, Promotion & Show Product on Website')
                    ->icon(fn (InventoryProduct $record): string => $record->storefrontProduct?->is_visible ? 'heroicon-m-check-circle' : 'heroicon-o-globe-alt')
                    ->color(fn (InventoryProduct $record): string => $record->storefrontProduct?->is_visible ? 'success' : 'warning')
                    ->button()
                    ->size('xs')
                    ->modalHeading(fn (InventoryProduct $record): string => "Show on Web & Pricing — {$record->product_name}")
                    ->modalDescription('Configure selling price, discount promotion, and website visibility directly from Inventory.')
                    ->modalWidth(MaxWidth::FourExtraLarge)
                    ->fillForm(function (InventoryProduct $record): array {
                        $product = $record->storefrontProduct;
                        $grnPrice = (float) ($record->latest_grn_price ?? $product?->grn_price ?? 0);
                        $availStock = $record->available_stock;

                        $origPrice = $product && (float) $product->price > 0 ? (float) $product->price : null;
                        $salePrice = $product && $product->sale_price !== null && (float) $product->sale_price > 0
                            ? (float) $product->sale_price
                            : $origPrice;
                        $discountPct = ($origPrice && $salePrice && $salePrice < $origPrice)
                            ? (int) round((($origPrice - $salePrice) / $origPrice) * 100)
                            : 0;

                        $hasVariants = $product ? $product->hasColorVariants() : false;
                        $variantRows = [];

                        if ($hasVariants && $product) {
                            foreach ($product->variants as $v) {
                                $vGrnStock = (int) GrnItem::where('product_code', $record->product_code)
                                    ->where(function ($q) use ($v) {
                                        $q->where('product_variant_id', $v->id)
                                            ->orWhere('color_name', $v->color_name);
                                    })
                                    ->sum('product_quantity');
                                $vGrnPrice = GrnItem::where('product_code', $record->product_code)
                                    ->where(function ($q) use ($v) {
                                        $q->where('product_variant_id', $v->id)
                                            ->orWhere('color_name', $v->color_name);
                                    })
                                    ->latest('id')
                                    ->value('price_per_unit');

                                $vOrig = (float) ($v->price ?? 0);
                                $vSale = $v->sale_price !== null && (float) $v->sale_price > 0 ? (float) $v->sale_price : $vOrig;
                                $vPct = ($vOrig > 0 && $vSale > 0 && $vSale < $vOrig)
                                    ? (int) round((($vOrig - $vSale) / $vOrig) * 100)
                                    : (int) ($v->discount_percentage ?? 0);

                                $variantRows[] = [
                                    'id'                  => $v->id,
                                    'color_name'          => $v->color_name,
                                    'variant_name'        => $v->variant_name ?? "{$record->product_name} - {$v->color_name}",
                                    'inventory_stock'     => $vGrnStock > 0 ? $vGrnStock : (int) ($v->inventory_stock ?? 0),
                                    'grn_price'           => $vGrnPrice !== null ? round((float) $vGrnPrice, 2) : (float) ($v->grn_price ?? $grnPrice),
                                    'price'               => $vOrig > 0 ? $vOrig : null,
                                    'discount_percentage' => $vPct,
                                    'sale_price'          => $vSale > 0 ? $vSale : null,
                                ];
                            }
                        }

                        return [
                            'has_color_variants'  => $hasVariants,
                            'available_stock'     => $availStock,
                            'grn_price'           => round($grnPrice, 2),
                            'price'               => $origPrice,
                            'discount_percentage' => $discountPct,
                            'sale_price'          => $salePrice,
                            'is_featured'         => (bool) ($product?->is_featured ?? false),
                            'is_visible'          => $product ? (bool) $product->is_visible : true,
                            'color_variants'      => $variantRows,
                        ];
                    })
                    ->form([
                        Forms\Components\Hidden::make('has_color_variants'),

                        // Standard Single Product Pricing
                        Forms\Components\Section::make('Pricing & Promotion')
                            ->description('Set the Original Price and Latest Selling Price for the website. Discount % calculates automatically.')
                            ->hidden(fn (Get $get) => (bool) $get('has_color_variants'))
                            ->schema([
                                Forms\Components\TextInput::make('available_stock')
                                    ->label('Available Inventory Stock')
                                    ->numeric()
                                    ->readOnly()
                                    ->dehydrated(false)
                                    ->suffix('units'),

                                Forms\Components\TextInput::make('grn_price')
                                    ->label('GRN Unit Cost Price')
                                    ->numeric()
                                    ->prefix($currency)
                                    ->readOnly()
                                    ->helperText('Loaded from latest GRN (Admin reference only)'),

                                Forms\Components\TextInput::make('price')
                                    ->label('Original Price')
                                    ->numeric()
                                    ->prefix($currency)
                                    ->required(fn (Get $get) => ! (bool) $get('has_color_variants'))
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
                                    ->default(0)
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
                                    ->label('Latest Price (Selling Price)')
                                    ->numeric()
                                    ->prefix($currency)
                                    ->required(fn (Get $get) => ! (bool) $get('has_color_variants'))
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                        $orig = (float) $get('price');
                                        $latest = (float) $state;
                                        if ($orig > 0 && $latest > 0) {
                                            $discount = max(0, min(100, round((($orig - $latest) / $orig) * 100)));
                                            $set('discount_percentage', $discount);
                                        }
                                    }),
                            ])->columns(3),

                        // Color Variants Pricing
                        Forms\Components\Section::make('Color Variants Pricing & Stock')
                            ->description('Set Original Price, Discount %, and Latest Selling Price for each color variant.')
                            ->visible(fn (Get $get) => (bool) $get('has_color_variants'))
                            ->schema([
                                Forms\Components\Repeater::make('color_variants')
                                    ->label('Color Variants')
                                    ->addable(false)
                                    ->deletable(false)
                                    ->reorderable(false)
                                    ->schema([
                                        Forms\Components\Hidden::make('id'),

                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\TextInput::make('color_name')
                                                    ->label('Color')
                                                    ->readOnly(),

                                                Forms\Components\TextInput::make('inventory_stock')
                                                    ->label('GRN Stock Quantity')
                                                    ->numeric()
                                                    ->readOnly(),

                                                Forms\Components\TextInput::make('grn_price')
                                                    ->label('GRN Cost Price')
                                                    ->numeric()
                                                    ->prefix($currency)
                                                    ->readOnly(),
                                            ]),

                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\TextInput::make('price')
                                                    ->label('Original Price')
                                                    ->numeric()
                                                    ->prefix($currency)
                                                    ->required()
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
                                                    ->required()
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
                                    ]),
                            ]),

                        // Website Display Toggles
                        Forms\Components\Section::make('Website Visibility & Promotion')
                            ->schema([
                                Forms\Components\Toggle::make('is_visible')
                                    ->label('Show Product on Website (Available to Customers)')
                                    ->helperText('Enable to immediately display this product on the storefront')
                                    ->default(true),

                                Forms\Components\Toggle::make('is_featured')
                                    ->label('Best Seller (Featured Product)')
                                    ->helperText('Feature this product prominently on the homepage and best-seller sections')
                                    ->default(false),
                            ])->columns(2),
                    ])
                    ->modalSubmitActionLabel('Save & Update Website')
                    ->action(function (InventoryProduct $record, array $data): void {
                        $totalStock = (int) $record->grnItems()->sum('product_quantity');
                        $latestGrnItem = $record->grnItems()->latest('id')->first();
                        $supplierId = $record->supplier_id ?? $latestGrnItem?->grn?->supplier_id;

                        $product = $record->storefrontProduct;
                        if (! $product) {
                            $slugBase = Str::slug($record->product_name) ?: strtolower($record->product_code);
                            $slug = $slugBase;
                            $i = 1;
                            while (Product::where('slug', $slug)->exists()) {
                                $slug = "{$slugBase}-{$i}";
                                $i++;
                            }

                            $product = Product::create([
                                'product_code'      => $record->product_code,
                                'name'              => $record->product_name,
                                'slug'              => $slug,
                                'supplier_id'       => $supplierId,
                                'type'              => 'simple',
                                'condition'         => 'Brand New',
                                'price'             => (float) ($data['price'] ?? 0),
                                'sale_price'        => ! empty($data['sale_price']) ? (float) $data['sale_price'] : (float) ($data['price'] ?? 0),
                                'grn_price'         => $latestGrnItem ? (float) $latestGrnItem->price_per_unit : (float) ($data['grn_price'] ?? 0),
                                'inventory_stock'   => $totalStock,
                                'show_quantity'     => $totalStock,
                                'quantity'          => $totalStock,
                                'stock_added_count' => $totalStock,
                                'stock_added_date'  => now(),
                                'is_visible'        => (bool) ($data['is_visible'] ?? true),
                                'is_featured'       => (bool) ($data['is_featured'] ?? false),
                                'images'            => $record->product_image ? [$record->product_image] : [],
                            ]);
                        } else {
                            $sold = (int) $product->sold_count;
                            $avail = max(0, $totalStock - $sold);

                            if (! empty($data['has_color_variants']) && ! empty($data['color_variants'])) {
                                $prices = [];
                                $salePrices = [];
                                foreach ($data['color_variants'] as $vData) {
                                    if (! empty($vData['id'])) {
                                        $variant = $product->variants()->find($vData['id']);
                                        if ($variant) {
                                            $vStock = (int) ($vData['inventory_stock'] ?? $variant->inventory_stock ?? 0);
                                            $vSold = (int) OrderItem::where('variant_id', $variant->id)
                                                ->whereHas('order', fn ($q) => $q->where('payment_status', 'Paid'))
                                                ->sum('quantity');
                                            $vAvail = max(0, $vStock - $vSold);
                                            $vPrice = (float) ($vData['price'] ?? 0);
                                            $vSale = ! empty($vData['sale_price']) ? (float) $vData['sale_price'] : $vPrice;

                                            $variant->update([
                                                'inventory_stock'     => $vStock,
                                                'show_quantity'       => $vStock,
                                                'stock'               => $vAvail,
                                                'grn_price'           => isset($vData['grn_price']) ? (float) $vData['grn_price'] : $variant->grn_price,
                                                'price'               => $vPrice,
                                                'discount_percentage' => (int) ($vData['discount_percentage'] ?? 0),
                                                'sale_price'          => $vSale,
                                            ]);

                                            if ($vPrice > 0) {
                                                $prices[] = $vPrice;
                                            }
                                            if ($vSale > 0) {
                                                $salePrices[] = $vSale;
                                            }
                                        }
                                    }
                                }

                                $minPrice = ! empty($prices) ? min($prices) : (float) $product->price;
                                $minSale = ! empty($salePrices) ? min($salePrices) : $minPrice;

                                $product->update([
                                    'supplier_id'     => $supplierId ?? $product->supplier_id,
                                    'price'           => $minPrice,
                                    'sale_price'      => $minSale,
                                    'inventory_stock' => $totalStock,
                                    'show_quantity'   => $totalStock,
                                    'quantity'        => $avail,
                                    'is_visible'      => (bool) ($data['is_visible'] ?? true),
                                    'is_featured'     => (bool) ($data['is_featured'] ?? false),
                                ]);
                            } else {
                                $origPrice = (float) ($data['price'] ?? 0);
                                $salePrice = ! empty($data['sale_price']) ? (float) $data['sale_price'] : $origPrice;

                                $product->update([
                                    'supplier_id'     => $supplierId ?? $product->supplier_id,
                                    'price'           => $origPrice,
                                    'sale_price'      => $salePrice,
                                    'grn_price'       => $latestGrnItem ? (float) $latestGrnItem->price_per_unit : (float) ($data['grn_price'] ?? $product->grn_price),
                                    'inventory_stock' => $totalStock,
                                    'show_quantity'   => $totalStock,
                                    'quantity'        => $avail,
                                    'is_visible'      => (bool) ($data['is_visible'] ?? true),
                                    'is_featured'     => (bool) ($data['is_featured'] ?? false),
                                ]);
                            }
                        }

                        Notification::make()
                            ->title($product->is_visible ? 'Product is now live on the website!' : 'Product pricing updated (Hidden from web)')
                            ->body("\"{$record->product_name}\" has been synced with {$record->available_stock} units in stock.")
                            ->success()
                            ->send();
                    }),

                // ── 2. Stock History modal ─────────────────────────────────────
                Tables\Actions\Action::make('stock_history')
                    ->label('Stock History')
                    ->tooltip('View Complete Stock-Wise History')
                    ->icon('heroicon-o-clock')
                    ->iconButton()
                    ->color('primary')
                    ->modalHeading(fn (InventoryProduct $record) => 'Stock-Wise History - ' . $record->product_name)
                    ->modalWidth(MaxWidth::SevenExtraLarge)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(fn (InventoryProduct $record) => view('filament.pages.stock-report-modal', [
                        'reportData' => app(\App\Services\StockManagementService::class)->generateReportForProduct($record),
                    ])),

                Tables\Actions\ViewAction::make()
                    ->iconButton()
                    ->tooltip('View Inventory Details'),

                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->tooltip('Edit Product & Web Pricing Details'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
