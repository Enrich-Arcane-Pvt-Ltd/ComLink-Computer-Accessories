<?php

namespace App\Filament\Resources\ProductResource\Schemas;

use App\Models\Brand;
use App\Models\Category;
use App\Models\GrnItem;
use App\Models\InventoryProduct;
use App\Models\Product;
use App\Models\ProductSpecification;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Str;

class ProductForm
{
    public static function schema(): array
    {
        $currency = config('app.currency_symbol', 'Rs. ');

        return [
            // 1. Category, Subcategory & Brand Selection
            Forms\Components\Section::make('Product Classification')
                ->description('Select where this product lives in the store catalog.')
                ->schema([
                    Forms\Components\Select::make('category_id')
                        ->label('Select the Category')
                        ->options(fn () => Category::whereNull('parent_id')->pluck('name', 'id'))
                        ->required()
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(fn (Set $set) => $set('sub_category_id', null))
                        ->afterStateHydrated(function (Set $set, ?Product $record) {
                            if ($record) {
                                $main = $record->categories->firstWhere('parent_id', null);
                                if ($main) {
                                    $set('category_id', $main->id);
                                }
                            }
                        }),

                    Forms\Components\Select::make('sub_category_id')
                        ->label('Select the Sub Category')
                        ->options(function (Get $get) {
                            $catId = $get('category_id');
                            if (! $catId) {
                                return [];
                            }

                            return Category::where('parent_id', $catId)->pluck('name', 'id');
                        })
                        ->visible(function (Get $get) {
                            $catId = $get('category_id');
                            if (! $catId) {
                                return false;
                            }

                            return Category::where('parent_id', $catId)->exists();
                        })
                        ->required(function (Get $get) {
                            $catId = $get('category_id');
                            if (! $catId) {
                                return false;
                            }

                            return Category::where('parent_id', $catId)->exists();
                        })
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateHydrated(function (Set $set, ?Product $record) {
                            if ($record) {
                                $sub = $record->categories->firstWhere('parent_id', '!=', null);
                                if ($sub) {
                                    $set('sub_category_id', $sub->id);
                                }
                            }
                        }),

                    Forms\Components\Select::make('brand_id')
                        ->label('Select the Brand')
                        ->options(fn () => Brand::pluck('name', 'id'))
                        ->required()
                        ->searchable()
                        ->preload(),
                ])->columns(3),

            // 2. Primary Information
            Forms\Components\Section::make('General Information')
                ->schema([
                    Forms\Components\Select::make('supplier_id')
                        ->label('Supplier Name')
                        ->relationship('supplier', 'name')
                        ->searchable()
                        ->preload()
                        ->placeholder('Select Supplier')
                        ->live()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')
                                ->label('Supplier Name')
                                ->required(),
                            Forms\Components\TextInput::make('contact_no')
                                ->label('Contact No'),
                            Forms\Components\TextInput::make('email')
                                ->label('Email')
                                ->email(),
                            Forms\Components\Textarea::make('address')
                                ->label('Address'),
                        ])
                        ->columnSpan(1),

                    Forms\Components\Select::make('product_code')
                        ->label('Product Code')
                        ->searchable()
                        ->preload()
                        ->placeholder('Select Product Code from Inventory')
                        ->options(function (Get $get) {
                            $supplierId = $get('supplier_id');

                            $query = InventoryProduct::query();
                            if ($supplierId) {
                                $query->where('supplier_id', $supplierId);
                            }
                            $inventoryCodes = $query->pluck('product_name', 'product_code')->toArray();

                            $grnQuery = GrnItem::query();
                            if ($supplierId) {
                                $grnQuery->whereHas('grn', fn ($q) => $q->where('supplier_id', $supplierId));
                            }
                            $grnCodes = $grnQuery->pluck('product_name', 'product_code')->toArray();

                            $all = array_merge($inventoryCodes, $grnCodes);
                            $options = [];
                            foreach ($all as $code => $name) {
                                $options[$code] = "{$code} - {$name}";
                            }

                            return $options;
                        })
                        ->live()
                        ->afterStateUpdated(function (Set $set, ?string $state, Get $get) {
                            if (! $state) {
                                return;
                            }

                            // 1. Product Name
                            $name = GrnItem::where('product_code', $state)->latest('id')->value('product_name')
                                ?? InventoryProduct::where('product_code', $state)->value('product_name');
                            if ($name) {
                                $set('name', $name);
                                $set('slug', Str::slug($name));
                                if (! $get('technical_specifications.model_series')) {
                                    $set('technical_specifications.model_series', $name);
                                }
                            }

                            // 2. Product Condition
                            $rawCondition = GrnItem::where('product_code', $state)->latest('id')->value('product_condition');
                            if ($rawCondition) {
                                $normalizedCondition = (strtolower($rawCondition) === 'used') ? 'Used' : 'Brand New';
                                $set('condition', $normalizedCondition);
                            }

                            // 3. Inventory Stock
                            $inventoryStock = (int) GrnItem::where('product_code', $state)->sum('product_quantity');
                            $set('inventory_stock', $inventoryStock);

                            // 4. Show Quantity (default to inventory stock or adjust)
                            $currentShow = (int) $get('show_quantity');
                            if ($currentShow <= 0 || $currentShow > $inventoryStock) {
                                $set('show_quantity', $inventoryStock);
                                $set('quantity', $inventoryStock);
                            }

                            // 5. GRN Price
                            $grnPrice = GrnItem::where('product_code', $state)->latest('id')->value('price_per_unit');
                            if ($grnPrice !== null) {
                                $set('grn_price', round((float) $grnPrice, 2));
                                $set('cost_price', round((float) $grnPrice, 2));
                            }

                            // 6. Supplier (auto-populate if not selected)
                            if (! $get('supplier_id')) {
                                $supId = InventoryProduct::where('product_code', $state)->value('supplier_id')
                                    ?? GrnItem::where('product_code', $state)->latest('id')->first()?->grn?->supplier_id;
                                if ($supId) {
                                    $set('supplier_id', $supId);
                                }
                            }
                        })
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('name')
                        ->label('Product Name')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Set $set, ?string $state, Get $get) {
                            $set('slug', Str::slug($state));
                            if (! $get('technical_specifications.model_series')) {
                                $set('technical_specifications.model_series', $state);
                            }
                        })
                        ->columnSpan(1),

                    Forms\Components\Select::make('condition')
                        ->label('Select Product Condition')
                        ->options([
                            'Brand New' => 'Brand New',
                            'Used' => 'Used',
                        ])
                        ->default('Brand New')
                        ->required()
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('inventory_stock')
                        ->label('Inventory Stock')
                        ->numeric()
                        ->default(0)
                        ->readOnly()
                        ->dehydrated()
                        ->helperText('Loaded from Inventory physical stock')
                        ->afterStateHydrated(function (Forms\Components\TextInput $component, ?Product $record) {
                            if ($record && $record->product_code) {
                                $stock = (int) GrnItem::where('product_code', $record->product_code)->sum('product_quantity');
                                $component->state($stock);
                            }
                        })
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('show_quantity')
                        ->label('Show Quantity')
                        ->numeric()
                        ->default(0)
                        ->required()
                        ->helperText(function (Get $get) {
                            $invStock = (int) ($get('inventory_stock') ?? 0);
                            $showQty = (int) ($get('show_quantity') ?? 0);
                            $balance = max(0, $invStock - $showQty);
                            return "Balance Inventory in warehouse: {$balance} units (Total: {$invStock} units)";
                        })
                        ->rules([
                            fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                $invStock = (int) ($get('inventory_stock') ?? 0);
                                if ($invStock > 0 && (int) $value > $invStock) {
                                    $fail("Show Quantity ({$value}) cannot exceed Total Inventory Stock ({$invStock} units).");
                                }
                            },
                        ])
                        ->afterStateHydrated(function (Forms\Components\TextInput $component, ?Product $record) {
                            if ($record) {
                                $val = $record->show_quantity ?? $record->quantity ?? 0;
                                $component->state($val);
                            }
                        })
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Set $set, $state, ?Product $record) {
                            if (! $record) {
                                $set('quantity', (int) $state);
                            }
                        })
                        ->columnSpan(1),

                    Forms\Components\Hidden::make('quantity')
                        ->default(0)
                        ->dehydrated(),

                    Forms\Components\Hidden::make('slug')
                        ->dehydrated(),

                    Forms\Components\Textarea::make('description')
                        ->label('Product Overview')
                        ->rows(4)
                        ->columnSpanFull()
                        ->placeholder('Provide an engaging summary, highlight key features, and compatibility notes...'),
                ])->columns(3),

            // 3. Product Gallery (4 Images)
            Forms\Components\Section::make('Product Gallery (4 Images)')
                ->description('Upload up to 4 high-resolution product photos. The first image serves as the main showcase cover photo. You can drag to reorder and click to crop/edit.')
                ->schema([
                    Forms\Components\FileUpload::make('images')
                        ->label('Product Photos (Max 4)')
                        ->disk('public')
                        ->visibility('public')
                        ->image()
                        ->multiple()
                        ->maxFiles(4)
                        ->reorderable()
                        ->directory('products')
                        ->imageEditor()
                        ->panelLayout('grid')
                        ->columnSpanFull()
                        ->required(),
                ]),

            // 4. Pricing & Discount Calculations
            Forms\Components\Section::make('Pricing & Promotion')
                ->description('Specify original price and latest sale price. Discount percentage calculates automatically.')
                ->schema([
                    Forms\Components\TextInput::make('grn_price')
                        ->label('GRN Price')
                        ->numeric()
                        ->prefix($currency)
                        ->readOnly()
                        ->dehydrated()
                        ->helperText('Inventory GRN unit cost (Admin reference only, not shown to customers)')
                        ->afterStateHydrated(function (Forms\Components\TextInput $component, ?Product $record) {
                            if ($record && $record->product_code) {
                                $price = GrnItem::where('product_code', $record->product_code)->latest('id')->value('price_per_unit');
                                $component->state($price ? round((float) $price, 2) : 0);
                            }
                        }),

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
                            }
                        }),

                    Forms\Components\TextInput::make('discount_percentage')
                        ->label('Discount Percentage')
                        ->numeric()
                        ->suffix('%')
                        ->dehydrated(false)
                        ->live(onBlur: true)
                        ->afterStateHydrated(function ($component, ?Product $record) {
                            if ($record && $record->price > 0 && $record->sale_price !== null && $record->sale_price < $record->price) {
                                $pct = round((($record->price - $record->sale_price) / $record->price) * 100);
                                $component->state($pct);
                            }
                        })
                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                            $orig = (float) $get('price');
                            $pct = (float) $state;
                            if ($orig > 0) {
                                $latest = round($orig * (1 - ($pct / 100)), 2);
                                $set('sale_price', $latest);
                            }
                        }),

                    Forms\Components\TextInput::make('sale_price')
                        ->label('Latest Price')
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

                    Forms\Components\Toggle::make('is_featured')
                        ->label('Best Seller (Featured)')
                        ->helperText('Feature this product prominently in homepage and best-seller lists')
                        ->default(false),

                    Forms\Components\Toggle::make('is_visible')
                        ->label('Availability (Visible on Web)')
                        ->helperText('Enable to make this product visible to customers')
                        ->default(true),
                ])->columns(3),

            // 5. Dynamic Specifications
            Forms\Components\Section::make('Product Specifications')
                ->description('Hardware specifications tailored to this specific category and component. Managed dynamically from Product Specifications catalog.')
                ->schema([
                    Forms\Components\Tabs::make('Specifications')
                        ->tabs([
                            // Tab 1: Main Specifications (Dynamically loaded based on selected Category and Sub Category)
                            Forms\Components\Tabs\Tab::make('Main Specifications')
                                ->icon('heroicon-m-cpu-chip')
                                ->schema([
                                    Forms\Components\Group::make()
                                        ->schema(function (Get $get) {
                                            $catId = $get('category_id');
                                            $subId = $get('sub_category_id');

                                            if (! $catId) {
                                                return [
                                                    Forms\Components\Placeholder::make('main_spec_notice')
                                                        ->label('Classification Required')
                                                        ->content('Please select a Category and Sub Category above to display the applicable Main Specifications.'),
                                                ];
                                            }

                                            $query = ProductSpecification::where('category_id', $catId)
                                                ->where('specification_type', 'main');

                                            if ($subId) {
                                                $query->where(function ($q) use ($subId) {
                                                    $q->where('sub_category_id', $subId)
                                                        ->orWhereNull('sub_category_id');
                                                });
                                            } else {
                                                $query->whereNull('sub_category_id');
                                            }

                                            $specs = $query->orderBy('sort_order')->orderBy('id')->get();

                                            if ($specs->isEmpty()) {
                                                return [
                                                    Forms\Components\Placeholder::make('no_main_specs')
                                                        ->label('Main Specifications Notice')
                                                        ->content('No custom main specification attributes have been added for this category yet. Brand and Condition are automatically applied from General Information. You can add new attributes from "Product Specification Attributes" in the Product Catalog navigation.'),
                                                ];
                                            }

                                            $components = [];
                                            $targetCatId = $subId ?: $catId;

                                            foreach ($specs as $spec) {
                                                // Ignore Brand Title because Brand is chosen in General Information
                                                if (strtolower(trim($spec->title)) === 'brand') {
                                                    continue;
                                                }

                                                $key = Str::slug($spec->title, '_');

                                                // Query previous values added under this title for this specific Sub Category (or Category)
                                                $previousValues = \App\Models\Product::whereHas('categories', function ($q) use ($targetCatId) {
                                                    $q->where('categories.id', $targetCatId);
                                                })
                                                ->get(['id', 'main_specifications'])
                                                ->map(fn ($p) => $p->main_specifications[$key] ?? null)
                                                ->filter(fn ($v) => ! is_null($v) && trim((string) $v) !== '')
                                                ->map(fn ($v) => trim((string) $v))
                                                ->unique()
                                                ->values()
                                                ->all();

                                                $input = Forms\Components\TextInput::make("main_specifications.{$key}")
                                                    ->label($spec->title)
                                                    ->placeholder(! empty($previousValues) ? "Select previous or enter new {$spec->title}..." : "Enter {$spec->title}...")
                                                    ->datalist($previousValues);

                                                if (! empty($previousValues)) {
                                                    $preview = implode(', ', array_slice($previousValues, 0, 6));
                                                    if (count($previousValues) > 6) {
                                                        $preview .= ' (+' . (count($previousValues) - 6) . ' more)';
                                                    }
                                                    $input->helperText("Previously used in this subcategory: {$preview}");
                                                } else {
                                                    $input->helperText("No previous values in this subcategory yet. Enter a new value.");
                                                }

                                                $components[] = $input;
                                            }

                                            if (empty($components)) {
                                                return [
                                                    Forms\Components\Placeholder::make('no_specs_remaining')
                                                        ->label('Main Specifications Notice')
                                                        ->content('Brand is configured in General Information. No additional main specification attributes are required for this category.'),
                                                ];
                                            }

                                            return $components;
                                        })
                                        ->columns(2),
                                ]),

                            // Tab 2: Technical Specifications
                            Forms\Components\Tabs\Tab::make('Technical Specifications')
                                ->icon('heroicon-m-wrench-screwdriver')
                                ->schema([
                                    Forms\Components\Section::make('General Specifications')
                                        ->schema(fn (Get $get) => self::buildTechSpecsFields($get, 'general', [
                                            ['title' => 'Model / Series', 'placeholder' => 'e.g. ASUS ROG Zephyrus G16 (2024)'],
                                            ['title' => 'Category Segment', 'placeholder' => 'e.g. Processors, Laptops, Keyboards'],
                                            ['title' => 'Condition', 'placeholder' => 'e.g. 100% Brand New (Original Factory Sealed)'],
                                        ]))->columns(3),

                                    Forms\Components\Section::make('Technical Performance')
                                        ->schema(fn (Get $get) => self::buildTechSpecsFields($get, 'performance', [
                                            ['title' => 'Interface / Connection', 'placeholder' => 'e.g. High-Speed PCIe Gen 4 / USB 3.2 / AM5'],
                                            ['title' => 'Architecture', 'placeholder' => 'e.g. Optimized Next-Gen Microarchitecture with Low Latency'],
                                            ['title' => 'Thermal Efficiency', 'placeholder' => 'e.g. Advanced cooling profile with heat dissipation fins'],
                                            ['title' => 'Power Consumption', 'placeholder' => 'e.g. Eco-efficient power curve with dynamic voltage scaling'],
                                        ]))->columns(2),

                                    Forms\Components\Section::make('Compatibility & Standards')
                                        ->schema(fn (Get $get) => self::buildTechSpecsFields($get, 'compatibility', [
                                            ['title' => 'Operating Systems', 'placeholder' => 'e.g. Windows 11 / Windows 10 (64-bit), Linux Kernel 5.4+'],
                                            ['title' => 'Certification', 'placeholder' => 'e.g. CE, FCC, RoHS, EnergyStar Certified'],
                                            ['title' => 'Dimensions & Weight', 'placeholder' => 'e.g. Standard Form Factor (~450g packaged)'],
                                            ['title' => 'Included in Box', 'placeholder' => 'e.g. 1x Hardware Unit, Quick Manual, Safety Guide, Warranty Certificate'],
                                        ]))->columns(2),
                                ]),
                        ]),
                ]),

            // 6. Warranty Information
            Forms\Components\Section::make('Warranty Information')
                ->description('Specify the product warranty duration and time period.')
                ->schema([
                    Forms\Components\TextInput::make('warranty_duration')
                        ->label('Warranty Period (No. of)')
                        ->placeholder('e.g. 1, 2, 6, 12, 24')
                        ->numeric()
                        ->minValue(0)
                        ->helperText('Number of time periods (e.g. 2 for 2 Years, 6 for 6 Months).'),

                    Forms\Components\Select::make('warranty_unit')
                        ->label('Warranty Time Period Unit')
                        ->options([
                            'Days' => 'Days',
                            'Weeks' => 'Weeks',
                            'Months' => 'Months',
                            'Years' => 'Years',
                        ])
                        ->default('Years')
                        ->searchable(false)
                        ->selectablePlaceholder(false)
                        ->native(false)
                        ->helperText('Select time unit: Days, Weeks, Months, or Years.'),
                ])->columns(2),
        ];
    }

    /**
     * Build dynamic technical specification input fields for a specific technical group.
     */
    protected static function buildTechSpecsFields(Get $get, string $subSection, array $fallbacks): array
    {
        $catId = $get('category_id');
        $subId = $get('sub_category_id');

        $specs = collect();
        if ($catId) {
            $query = ProductSpecification::where('category_id', $catId)
                ->where('specification_type', 'technical')
                ->where('sub_section', $subSection);

            if ($subId) {
                $query->where(function ($q) use ($subId) {
                    $q->where('sub_category_id', $subId)
                        ->orWhereNull('sub_category_id');
                });
            } else {
                $query->whereNull('sub_category_id');
            }

            $specs = $query->orderBy('sort_order')->orderBy('id')->get();
        }

        if ($specs->isEmpty()) {
            $specs = collect($fallbacks)->map(fn ($item) => (object) [
                'title' => $item['title'],
                'placeholder' => $item['placeholder'] ?? '',
            ]);
        }

        $components = [];
        foreach ($specs as $spec) {
            $key = Str::slug($spec->title, '_');
            $components[] = Forms\Components\TextInput::make("technical_specifications.{$key}")
                ->label($spec->title)
                ->placeholder($spec->placeholder ?? "Enter {$spec->title}...");
        }

        return $components;
    }
}
