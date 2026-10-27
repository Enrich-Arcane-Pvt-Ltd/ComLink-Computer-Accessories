<?php

namespace App\Filament\Resources\InventoryProductResource\Schemas;

use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryProduct;
use App\Models\Product;
use App\Models\ProductSpecification;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Str;

class InventoryProductForm
{
    public static function schema(): array
    {
        return [
            // 0. Product Configuration Type
            Forms\Components\Section::make('Product Type & Variant Options')
                ->description('Choose whether this is a standard product with a single color, or a product with multiple color variants.')
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
                        ->live(),
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
                        ->afterStateUpdated(function (Set $set, Get $get, $state, ?InventoryProduct $record) {
                            $set('sub_category_id', null);
                            if (! $record) {
                                $code = self::generateProductCode(
                                    $get('product_name'),
                                    $state ? (int) $state : null,
                                    null
                                );
                                $set('product_code', $code);
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
                        ->afterStateUpdated(function (Set $set, Get $get, $state, ?InventoryProduct $record) {
                            if (! $record) {
                                $catId = $get('category_id');
                                $code = self::generateProductCode(
                                    $get('product_name'),
                                    $catId ? (int) $catId : null,
                                    $state ? (int) $state : null
                                );
                                $set('product_code', $code);
                            }
                        }),

                    Forms\Components\Select::make('brand_id')
                        ->label('Select the Brand')
                        ->options(fn () => Brand::pluck('name', 'id'))
                        ->required()
                        ->searchable()
                        ->preload(),
                ])->columns(3),

            // 2. Primary / General Information (Without Supplier, Stock, or Pricing)
            Forms\Components\Section::make('General Information')
                ->description('Enter product name, condition, and overview. Product Code is auto-generated from Product Name + Category + Sub Category and remains editable.')
                ->schema([
                    Forms\Components\TextInput::make('product_name')
                        ->label('Product Name')
                        ->placeholder('e.g. Logitech G304 Wireless Gaming Mouse')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Set $set, ?string $state, Get $get, ?InventoryProduct $record) {
                            if (! $get('technical_specifications.model_series') && $state) {
                                $set('technical_specifications.model_series', $state);
                            }

                            if (! $record && $state) {
                                $catId = $get('category_id');
                                $subId = $get('sub_category_id');
                                $code = self::generateProductCode(
                                    $state,
                                    $catId ? (int) $catId : null,
                                    $subId ? (int) $subId : null
                                );
                                $set('product_code', $code);
                            }

                            if ((bool) $get('has_color_variants') && $state) {
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

                    Forms\Components\TextInput::make('product_code')
                        ->label('Product Code')
                        ->placeholder('Auto-generated: {Name}-{Cat}-{SubCat}')
                        ->required()
                        ->unique(InventoryProduct::class, 'product_code', ignoreRecord: true)
                        ->maxLength(50)
                        ->helperText('Auto-generated from {Product Name + Category + Sub Category}. Editable & unique.')
                        ->suffixAction(
                            Forms\Components\Actions\Action::make('regenerate_code')
                                ->icon('heroicon-m-arrow-path')
                                ->tooltip('Regenerate Product Code from Name & Category')
                                ->action(function (Set $set, Get $get, ?InventoryProduct $record) {
                                    $catId = $get('category_id');
                                    $subId = $get('sub_category_id');
                                    $code = self::generateProductCode(
                                        $get('product_name'),
                                        $catId ? (int) $catId : null,
                                        $subId ? (int) $subId : null,
                                        $record?->id
                                    );
                                    $set('product_code', $code);
                                })
                        )
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

                    Forms\Components\Textarea::make('description')
                        ->label('Product Overview')
                        ->rows(4)
                        ->columnSpanFull()
                        ->placeholder('Provide an engaging summary, highlight key features, and compatibility notes...'),
                ])->columns(3),

            // 3. Color Variants (Visible when 'Add Product with Different Colors' is selected)
            Forms\Components\Section::make('Product Color Variants')
                ->description('Configure individual color options with their specific images and conditions. Stock and pricing will be managed via GRN and Inventory.')
                ->visible(fn (Get $get) => (bool) $get('has_color_variants'))
                ->schema([
                    Forms\Components\Repeater::make('color_variants')
                        ->label('Color Variants Breakdown')
                        ->addActionLabel('+ Add Another Color Option')
                        ->defaultItems(1)
                        ->reorderable()
                        ->cloneable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => (! empty($state['color_name'])
                            ? "Color: {$state['color_name']} — " . ($state['variant_name'] ?? '')
                            : 'New Color Option'
                        ))
                        ->schema([
                            Forms\Components\Hidden::make('id'),

                            Forms\Components\Grid::make(4)
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
                                            $parentName = $get('../../product_name') ?: 'Product';
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

                                    Forms\Components\FileUpload::make('image')
                                        ->label('Color Image')
                                        ->disk('public')
                                        ->visibility('public')
                                        ->image()
                                        ->directory('products/variants')
                                        ->imageEditor()
                                        ->required(),
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

            // 5. Dynamic Specifications
            Forms\Components\Section::make('Product Specifications')
                ->description('Hardware specifications tailored to this specific category and component. Managed dynamically from Product Specifications catalog.')
                ->schema([
                    Forms\Components\Tabs::make('Specifications')
                        ->tabs([
                            // Tab 1: Main Specifications
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
                                                if (strtolower(trim($spec->title)) === 'brand') {
                                                    continue;
                                                }

                                                $key = Str::slug($spec->title, '_');

                                                $previousValues = Product::whereHas('categories', function ($q) use ($targetCatId) {
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
     * Generate a short, unique product code based on:
     * {Product name + Category + sub category}
     * Example: LOGG304-PER-MIC (or LOGG304-PER-MIC-01 if duplicate)
     */
    public static function generateProductCode(?string $name, ?int $catId = null, ?int $subCatId = null, ?int $ignoreInventoryId = null): string
    {
        // 1. Product Name part (concise, up to 6-7 chars)
        $words = array_values(array_filter(preg_split('/\s+/', trim((string) $name))));
        $namePart = '';
        if (! empty($words)) {
            $firstWord = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $words[0]));
            $secondWord = isset($words[1]) ? strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $words[1])) : '';
            if ($secondWord !== '') {
                $namePart = substr($firstWord, 0, 3) . substr($secondWord, 0, 4);
            } else {
                $namePart = substr($firstWord, 0, 6);
            }
        }
        $namePart = substr($namePart, 0, 7);

        // 2. Category part (3 chars)
        $catPart = '';
        if ($catId) {
            $cat = Category::find($catId);
            if ($cat) {
                $catPart = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $cat->name), 0, 3));
            }
        }

        // 3. Sub Category part (3 chars)
        $subPart = '';
        if ($subCatId) {
            $sub = Category::find($subCatId);
            if ($sub) {
                $subPart = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $sub->name), 0, 3));
            }
        }

        $parts = array_values(array_filter([$namePart, $catPart, $subPart]));
        $base = ! empty($parts) ? implode('-', $parts) : 'PRD-' . strtoupper(Str::random(4));

        // Ensure uniqueness across both InventoryProduct and Product tables
        $code = $base;
        $counter = 1;
        while (self::isCodeTaken($code, $ignoreInventoryId)) {
            $code = $base . '-' . str_pad((string) $counter, 2, '0', STR_PAD_LEFT);
            $counter++;
        }

        return $code;
    }

    protected static function isCodeTaken(string $code, ?int $ignoreInventoryId = null): bool
    {
        $invQuery = InventoryProduct::where('product_code', $code);
        if ($ignoreInventoryId) {
            $invQuery->where('id', '!=', $ignoreInventoryId);
            $existingCode = InventoryProduct::where('id', $ignoreInventoryId)->value('product_code');
            if ($existingCode === $code) {
                return false;
            }
        }

        if ($invQuery->exists()) {
            return true;
        }

        return Product::where('product_code', $code)->exists();
    }
}
