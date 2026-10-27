<?php

namespace App\Filament\Resources\ProductResource\Schemas;

use App\Models\Brand;
use App\Models\Category;
use App\Models\GrnItem;
use App\Models\InventoryProduct;
use App\Models\Product;
use App\Models\ProductSpecification;
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
            // 0. Product Configuration Type
            Forms\Components\Section::make('Product Type & Variant Options')
                ->description('Choose whether this is a standard product with a single color/price, or a product with multiple colors having different quantities and prices.')
                ->schema([
                    Forms\Components\ToggleButtons::make('has_color_variants')
                        ->label('Product Variant Mode')
                        ->options([
                            0 => 'Standard Single Product',
                            1 => 'Add Product with Different Colors',
                        ])
                        ->icons([
                            0 => 'heroicon-m-cube',
                            1 => 'heroicon-m-swatch',
                        ])
                        ->colors([
                            0 => 'gray',
                            1 => 'primary',
                        ])
                        ->default(0)
                        ->boolean()
                        ->inline()
                        ->live()
                        ->afterStateUpdated(function ($state, Set $set, Get $get) {
                            if ($state) {
                                $productCode = $get('product_code');
                                $productName = $get('name');
                                if ($productCode) {
                                    self::autoPopulateColorVariants($set, $productCode, $productName);
                                }
                            }
                        })
                        ->afterStateHydrated(function ($component, ?Product $record) {
                            if ($record && $record->hasColorVariants()) {
                                $component->state(true);
                            }
                        }),
                ]),

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
                        ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                            $set('sub_category_id', null);
                            // Regenerate product code when category changes
                            $name = $get('name');
                            if ($name && $state) {
                                self::maybeGenerateProductCode($set, $get, $name, (int) $state, null);
                            }
                        })
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
                        ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                            // Regenerate product code when sub-category changes
                            $name = $get('name');
                            $catId = $get('category_id');
                            if ($name && $catId) {
                                self::maybeGenerateProductCode($set, $get, $name, (int) $catId, $state ? (int) $state : null);
                            }
                        })
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
                    // Auto-generated editable product code
                    Forms\Components\TextInput::make('product_code')
                        ->label('Product Code')
                        ->placeholder('Auto-generated from name + category (editable)')
                        ->required()
                        ->unique(Product::class, 'product_code', ignoreRecord: true)
                        ->maxLength(50)
                        ->helperText('Auto-generated unique code. You can edit it. Format: {Name}-{Cat}-{SubCat}')
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                            // On blur, ensure uniqueness (UI feedback via validation)
                        })
                        ->afterStateHydrated(function ($component, ?Product $record) {
                            // Keep existing code when editing
                        }),

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

                            // Auto-generate product code if not set or if it looks like a default
                            self::maybeGenerateProductCode($set, $get, $state, null, null);

                            if ((bool) $get('has_color_variants')) {
                                $variants = $get('color_variants');
                                if (! empty($variants) && is_array($variants)) {
                                    foreach ($variants as $k => $v) {
                                        if (! empty($v['color_name'])) {
                                            $set("color_variants.{$k}.variant_name", "{$state} - {$v['color_name']}");
                                        }
                                    }
                                }
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
                        ->required(fn (Get $get) => ! (bool) $get('has_color_variants'))
                        ->hidden(fn (Get $get) => (bool) $get('has_color_variants'))
                        ->columnSpan(1),

                    Forms\Components\Hidden::make('inventory_stock')
                        ->default(0)
                        ->dehydrated(),

                    Forms\Components\Hidden::make('show_quantity')
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

            // 3. Color Variants (Visible when 'Add Product with Different Colors' is selected)
            Forms\Components\Section::make('Product Color Variants (Different Colors & Quantities)')
                ->description('Configure individual color options with their specific images, conditions, stock counts, and pricing. Each color will be tracked individually.')
                ->visible(fn (Get $get) => (bool) $get('has_color_variants'))
                ->schema([
                    Forms\Components\Repeater::make('color_variants')
                        ->label('Color Variants Breakdown')
                        ->addActionLabel('+ Add Another Color Option')
                        ->defaultItems(1)
                        ->reorderable()
                        ->cloneable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => (! empty($state['color_name']) ? "Color: {$state['color_name']} — ".($state['variant_name'] ?? '').' [Stock: '.($state['inventory_stock'] ?? 0).' units]' : 'New Color Option'))
                        ->schema([
                            Forms\Components\Grid::make(3)
                                ->schema([
                                    Forms\Components\TextInput::make('color_name')
                                        ->label('Color Name')
                                        ->placeholder('e.g. Red, Matte White, Midnight Black')
                                        ->required()
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (Set $set, ?string $state, Get $get) {
                                            if (! $state) {
                                                return;
                                            }
                                            $parentName = $get('../../name') ?: 'Product';
                                            $set('variant_name', "{$parentName} - {$state}");
                                        }),

                                    Forms\Components\TextInput::make('variant_name')
                                        ->label('Product Name with Color')
                                        ->placeholder('Auto-generated: {Product Name} - {Color}')
                                        ->helperText('Name displayed on invoices, cart, and storefront')
                                        ->required(),

                                    Forms\Components\Select::make('condition')
                                        ->label('Select Condition')
                                        ->options([
                                            'Brand New' => 'Brand New',
                                            'Used' => 'Used',
                                        ])
                                        ->default('Brand New')
                                        ->required(),
                                ]),

                            Forms\Components\Grid::make(3)
                                ->schema([
                                    Forms\Components\FileUpload::make('image')
                                        ->label('Color Image')
                                        ->disk('public')
                                        ->visibility('public')
                                        ->image()
                                        ->directory('products/variants')
                                        ->imageEditor()
                                        ->required()
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('inventory_stock')
                                        ->label('Stock Quantity')
                                        ->numeric()
                                        ->default(0)
                                        ->required()
                                        ->helperText('Full physical warehouse count for this color')
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('grn_price')
                                        ->label('GRN Price')
                                        ->numeric()
                                        ->prefix($currency)
                                        ->helperText('Unit cost price for this color')
                                        ->columnSpan(1),
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
                                ]),
                        ])
                        ->columnSpanFull(),
                ]),

            // 4. Product Gallery (4 Images) - Standard Single Product
            Forms\Components\Section::make('Product Gallery (4 Images)')
                ->description('Upload up to 4 high-resolution product photos. The first image serves as the main showcase cover photo. You can drag to reorder and click to crop/edit.')
                ->hidden(fn (Get $get) => (bool) $get('has_color_variants'))
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
                        ->required(fn (Get $get) => ! (bool) $get('has_color_variants')),
                ]),

            // 5. Pricing & Promotion
            Forms\Components\Section::make('Pricing & Promotion')
                ->description('Specify original price and latest sale price. Discount percentage calculates automatically. You can also set pricing later after stock is received via GRN.')
                ->schema([
                    Forms\Components\TextInput::make('grn_price')
                        ->label('GRN / Cost Price')
                        ->numeric()
                        ->prefix($currency)
                        ->dehydrated()
                        ->hidden(fn (Get $get) => (bool) $get('has_color_variants'))
                        ->helperText('Inventory GRN unit cost (Admin reference only, not shown to customers)')
                        ->afterStateHydrated(function (Forms\Components\TextInput $component, ?Product $record) {
                            if ($record && $record->product_code) {
                                $price = GrnItem::where('product_code', $record->product_code)->latest('id')->value('price_per_unit');
                                $component->state($price ? round((float) $price, 2) : $component->getState());
                            }
                        }),

                    Forms\Components\TextInput::make('price')
                        ->label('Original Price')
                        ->numeric()
                        ->prefix($currency)
                        ->hidden(fn (Get $get) => (bool) $get('has_color_variants'))
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
                        ->hidden(fn (Get $get) => (bool) $get('has_color_variants'))
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
                        ->hidden(fn (Get $get) => (bool) $get('has_color_variants'))
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
                                                        $preview .= ' (+'. (count($previousValues) - 6).' more)';
                                                    }
                                                    $input->helperText("Previously used in this subcategory: {$preview}");
                                                } else {
                                                    $input->helperText('No previous values in this subcategory yet. Enter a new value.');
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

    /**
     * Auto-generates the product_code from name + category + sub-category.
     * Only sets the code if it is currently empty (new product create flow).
     * Never overwrites an existing code in edit mode.
     */
    public static function maybeGenerateProductCode(Set $set, Get $get, ?string $name, ?int $catId, ?int $subCatId): void
    {
        // Do not overwrite if a code is already set (editing or user typed one manually)
        $existing = $get('product_code');
        if (! empty($existing)) {
            return;
        }

        $catId    = $catId    ?? (int) $get('category_id') ?: null;
        $subCatId = $subCatId ?? (int) $get('sub_category_id') ?: null;

        if (! $name) {
            return;
        }

        $code = self::generateProductCode($name, $catId, $subCatId);
        $set('product_code', $code);
    }

    /**
     * Generate a short, unique product code.
     * Format: {NameAbbr}-{CatAbbr}-{SubCatAbbr}[-NNN]
     * Max ~30 chars, uppercase.
     */
    public static function generateProductCode(?string $name, ?int $catId, ?int $subCatId): string
    {
        // Build name abbreviation (max 10 chars from first letters of words)
        $words = preg_split('/\s+/', trim((string) $name));
        $namePart = '';
        foreach ($words as $word) {
            $clean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $word));
            if (strlen($clean) === 0) {
                continue;
            }
            // Use first word up to 6 chars, then first letter of rest
            if ($namePart === '') {
                $namePart .= substr($clean, 0, 6);
            } else {
                $namePart .= substr($clean, 0, 1);
            }
        }
        $namePart = substr($namePart, 0, 8); // cap at 8

        // Category abbreviation (first 3 chars, uppercase)
        $catPart = '';
        if ($catId) {
            $cat = Category::find($catId);
            if ($cat) {
                $catPart = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $cat->name), 0, 4));
            }
        }

        // Sub-category abbreviation (first 3 chars, uppercase)
        $subPart = '';
        if ($subCatId) {
            $sub = Category::find($subCatId);
            if ($sub) {
                $subPart = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $sub->name), 0, 4));
            }
        }

        // Build base code
        $parts = array_filter([$namePart, $catPart, $subPart]);
        $base  = implode('-', $parts);

        if (empty($base)) {
            $base = strtoupper(Str::random(8));
        }

        // Ensure uniqueness
        $code = $base;
        $counter = 1;
        while (
            Product::where('product_code', $code)->exists() ||
            InventoryProduct::where('product_code', $code)->exists()
        ) {
            $code = $base . '-' . str_pad((string) $counter, 3, '0', STR_PAD_LEFT);
            $counter++;
        }

        return $code;
    }

    /**
     * Auto-populates the color_variants repeater based on GRN/PO records for a given product_code.
     */
    public static function autoPopulateColorVariants(Set|callable $set, ?string $productCode, ?string $productName = null): void
    {
        if (! $productCode) {
            return;
        }

        if (! $productName) {
            $productName = GrnItem::where('product_code', $productCode)->latest('id')->value('product_name')
                ?? InventoryProduct::where('product_code', $productCode)->value('product_name')
                ?? '';
        }

        // 1. Fetch GRN Items for this product_code that have a color specified
        $grnItems = GrnItem::where('product_code', $productCode)
            ->whereNotNull('color_name')
            ->where('color_name', '!=', '')
            ->get()
            ->groupBy('color_name');

        if ($grnItems->isEmpty()) {
            return;
        }

        $variantsData = [];
        foreach ($grnItems as $colorName => $items) {
            $totalQty = $items->sum('product_quantity');
            $latestItem = $items->sortByDesc('id')->first();

            $variantsData[] = [
                'color_name'      => $colorName,
                'variant_name'    => "{$productName} - {$colorName}",
                'condition'       => 'Brand New',
                'inventory_stock' => $totalQty,
                'grn_price'       => $latestItem ? (float) $latestItem->price_per_unit : null,
                'price'           => 0,
                'sale_price'      => 0,
                'discount_percentage' => 0,
            ];
        }

        $set('color_variants', $variantsData);
    }
}
