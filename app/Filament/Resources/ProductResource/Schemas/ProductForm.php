<?php

namespace App\Filament\Resources\ProductResource\Schemas;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
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
                        ->columnSpan(2),

                    Forms\Components\TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(Product::class, 'slug', ignoreRecord: true)
                        ->disabled()
                        ->dehydrated()
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('sku')
                        ->label('SKU')
                        ->placeholder('e.g. CL-PRC-101')
                        ->unique(Product::class, 'sku', ignoreRecord: true)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('quantity')
                        ->label('Product Quantity (Stock)')
                        ->numeric()
                        ->default(10)
                        ->required()
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
                ->description('Hardware specifications tailored to this specific component or device.')
                ->schema([
                    Forms\Components\Tabs::make('Specifications')
                        ->tabs([
                            // Tab 1: Main Specifications (Reactive depending on Category)
                            Forms\Components\Tabs\Tab::make('Main Specifications')
                                ->icon('heroicon-m-cpu-chip')
                                ->schema([
                                    // A. Laptops / Gaming Laptops
                                    Forms\Components\Group::make([
                                        Forms\Components\TextInput::make('main_specifications.display_size')
                                            ->label('Display Size')
                                            ->placeholder('e.g. 16.0" QHD+ (2560x1600) 240Hz OLED'),
                                        Forms\Components\TextInput::make('main_specifications.graphic_cards')
                                            ->label('Graphic Cards')
                                            ->placeholder('e.g. NVIDIA GeForce RTX 4080 12GB GDDR6'),
                                        Forms\Components\TextInput::make('main_specifications.memory')
                                            ->label('Memory')
                                            ->placeholder('e.g. 32GB DDR5 5600MHz'),
                                        Forms\Components\TextInput::make('main_specifications.processor')
                                            ->label('Processor')
                                            ->placeholder('e.g. Intel Core Ultra 9 185H / AMD Ryzen 9'),
                                        Forms\Components\TextInput::make('main_specifications.storage')
                                            ->label('Storage')
                                            ->placeholder('e.g. 1TB PCIe 4.0 NVMe SSD'),
                                        Forms\Components\TextInput::make('main_specifications.type')
                                            ->label('Type')
                                            ->placeholder('e.g. Gaming Laptop / Ultrabook'),
                                    ])
                                        ->columns(2)
                                        ->visible(fn (Get $get) => self::matchesCategory($get, ['laptop'])),

                                    // B. Processors
                                    Forms\Components\Group::make([
                                        Forms\Components\TextInput::make('main_specifications.generation')
                                            ->label('Generation')
                                            ->placeholder('e.g. AMD Ryzen 7000 Series / Intel 14th Gen'),
                                        Forms\Components\TextInput::make('main_specifications.cores')
                                            ->label('CORES')
                                            ->placeholder('e.g. 8 Cores / 24 Cores'),
                                        Forms\Components\TextInput::make('main_specifications.threads')
                                            ->label('THREADS')
                                            ->placeholder('e.g. 16 Threads / 32 Threads'),
                                    ])
                                        ->columns(3)
                                        ->visible(fn (Get $get) => self::matchesCategory($get, ['processor'])),

                                    // C. Motherboards
                                    Forms\Components\Group::make([
                                        Forms\Components\TextInput::make('main_specifications.performance_level')
                                            ->label('Performance Level')
                                            ->placeholder('e.g. High-End Enthusiast / Mainstream Gaming'),
                                        Forms\Components\TextInput::make('main_specifications.cpu_socket')
                                            ->label('CPU Soket')
                                            ->placeholder('e.g. AM5, LGA 1700, LGA 1851'),
                                        Forms\Components\TextInput::make('main_specifications.chipset_model')
                                            ->label('Chipset Model')
                                            ->placeholder('e.g. AMD B650, Intel Z790, AMD X670E'),
                                        Forms\Components\Select::make('main_specifications.mb_platform')
                                            ->label('MB Platform')
                                            ->options([
                                                'AMD Platform' => 'AMD Platform',
                                                'Intel Platform' => 'Intel Platform',
                                            ]),
                                    ])
                                        ->columns(2)
                                        ->visible(fn (Get $get) => self::matchesCategory($get, ['motherboard'])),

                                    // D. Coolers
                                    Forms\Components\Group::make([
                                        Forms\Components\TextInput::make('main_specifications.fans_size')
                                            ->label('Fans Size')
                                            ->placeholder('e.g. 120mm / 140mm'),
                                        Forms\Components\TextInput::make('main_specifications.radiator_size')
                                            ->label('Radiator Size')
                                            ->placeholder('e.g. 360mm / 240mm / 280mm'),
                                        Forms\Components\Select::make('main_specifications.cooling_type')
                                            ->label('Cooling Type')
                                            ->options([
                                                'AIO Liquid Cooler' => 'AIO Liquid Cooler',
                                                'Air Cooler' => 'Air Cooler',
                                                'Custom Loop' => 'Custom Loop',
                                            ]),
                                    ])
                                        ->columns(3)
                                        ->visible(fn (Get $get) => self::matchesCategory($get, ['cooler'])),

                                    // E. Memory (RAM)
                                    Forms\Components\Group::make([
                                        Forms\Components\TextInput::make('main_specifications.ram_capacity')
                                            ->label('RAM Capacity')
                                            ->placeholder('e.g. 32GB (2x16GB)'),
                                        Forms\Components\TextInput::make('main_specifications.memory_speed')
                                            ->label('Memory Speed')
                                            ->placeholder('e.g. 6000MHz / 3600MHz'),
                                        Forms\Components\Select::make('main_specifications.form_factor')
                                            ->label('Form Factor')
                                            ->options([
                                                'Desktop UDIMM' => 'Desktop UDIMM',
                                                'Laptop SODIMM' => 'Laptop SODIMM',
                                            ]),
                                        Forms\Components\Select::make('main_specifications.memory_gen')
                                            ->label('Memory Gen')
                                            ->options([
                                                'DDR5' => 'DDR5',
                                                'DDR4' => 'DDR4',
                                            ]),
                                    ])
                                        ->columns(2)
                                        ->visible(fn (Get $get) => self::matchesCategory($get, ['memory', 'ram'])),

                                    // F. SSD
                                    Forms\Components\Group::make([
                                        Forms\Components\TextInput::make('main_specifications.generation')
                                            ->label('Generation')
                                            ->placeholder('e.g. PCIe Gen 4.0 x4'),
                                        Forms\Components\TextInput::make('main_specifications.storage_capacity')
                                            ->label('Storage Capacity')
                                            ->placeholder('e.g. 1TB / 2TB / 4TB'),
                                        Forms\Components\Select::make('main_specifications.ssd_type')
                                            ->label('SSD Type')
                                            ->options([
                                                'M.2 NVMe' => 'M.2 NVMe',
                                                '2.5" SATA III' => '2.5" SATA III',
                                            ]),
                                    ])
                                        ->columns(3)
                                        ->visible(fn (Get $get) => self::matchesCategory($get, ['ssd'])),

                                    // G. Hard Disk
                                    Forms\Components\Group::make([
                                        Forms\Components\TextInput::make('main_specifications.hdd_color_type')
                                            ->label('HDD Color Type')
                                            ->placeholder('e.g. WD Blue, WD Black, Seagate Barracuda'),
                                        Forms\Components\TextInput::make('main_specifications.storage_capacity')
                                            ->label('Storage Capacity')
                                            ->placeholder('e.g. 2TB / 4TB / 8TB'),
                                        Forms\Components\TextInput::make('main_specifications.storage_type')
                                            ->label('Storage Type')
                                            ->placeholder('e.g. Internal 3.5" SATA (7200 RPM)'),
                                    ])
                                        ->columns(3)
                                        ->visible(fn (Get $get) => self::matchesCategory($get, ['hard-disk', 'hdd'])),

                                    // H. Graphic Cards
                                    Forms\Components\Group::make([
                                        Forms\Components\TextInput::make('main_specifications.gpu_series')
                                            ->label('GPU Series')
                                            ->placeholder('e.g. GeForce RTX 40 Series / Radeon RX 7000'),
                                        Forms\Components\TextInput::make('main_specifications.vram')
                                            ->label('Memory (VRAM)')
                                            ->placeholder('e.g. 16GB GDDR6X'),
                                        Forms\Components\TextInput::make('main_specifications.accessory_type')
                                            ->label('Accessory Type')
                                            ->placeholder('e.g. Desktop GPU (Triple Fan)'),
                                        Forms\Components\TextInput::make('main_specifications.psu_requirement')
                                            ->label('Power Supply Requirement')
                                            ->placeholder('e.g. 750W or higher'),
                                    ])
                                        ->columns(2)
                                        ->visible(fn (Get $get) => self::matchesCategory($get, ['graphic-card', 'gpu', 'graphics-card'])),

                                    // I. Power Supply
                                    Forms\Components\Group::make([
                                        Forms\Components\TextInput::make('main_specifications.power_output')
                                            ->label('Power Output')
                                            ->placeholder('e.g. 850W / 1000W'),
                                        Forms\Components\Select::make('main_specifications.modular')
                                            ->label('Modular')
                                            ->options([
                                                'Fully Modular' => 'Fully Modular',
                                                'Semi-Modular' => 'Semi-Modular',
                                                'Non-Modular' => 'Non-Modular',
                                            ]),
                                        Forms\Components\Select::make('main_specifications.power_rating')
                                            ->label('Power Rating')
                                            ->options([
                                                '80 PLUS Gold' => '80 PLUS Gold',
                                                '80 PLUS Platinum' => '80 PLUS Platinum',
                                                '80 PLUS Titanium' => '80 PLUS Titanium',
                                                '80 PLUS Bronze' => '80 PLUS Bronze',
                                            ]),
                                    ])
                                        ->columns(3)
                                        ->visible(fn (Get $get) => self::matchesCategory($get, ['power-supply', 'psu'])),

                                    // J. PC Cases
                                    Forms\Components\Group::make([
                                        Forms\Components\TextInput::make('main_specifications.accessory_type')
                                            ->label('Accessory Type')
                                            ->placeholder('e.g. ARGB Fan Kit / Dual-Chamber Chassis'),
                                        Forms\Components\TextInput::make('main_specifications.cooler_size')
                                            ->label('Cooler Size')
                                            ->placeholder('e.g. Up to 360mm Radiator Support'),
                                        Forms\Components\TextInput::make('main_specifications.mb_size')
                                            ->label('MB Size')
                                            ->placeholder('e.g. ATX, Micro-ATX, Mini-ITX, E-ATX'),
                                    ])
                                        ->columns(3)
                                        ->visible(fn (Get $get) => self::matchesCategory($get, ['pc-case', 'case', 'chassis'])),

                                    // K. Monitors & Displays
                                    Forms\Components\Group::make([
                                        Forms\Components\TextInput::make('main_specifications.panel')
                                            ->label('Panel')
                                            ->placeholder('e.g. Fast IPS / OLED / VA'),
                                        Forms\Components\TextInput::make('main_specifications.screen_size')
                                            ->label('Screen Size')
                                            ->placeholder('e.g. 27 Inch / 34 Inch Curved'),
                                    ])
                                        ->columns(2)
                                        ->visible(fn (Get $get) => self::matchesCategory($get, ['monitor', 'display'])),

                                    // L. Computers
                                    Forms\Components\Group::make([
                                        Forms\Components\TextInput::make('main_specifications.computer_type')
                                            ->label('Computer Type')
                                            ->placeholder('e.g. Custom Gaming PC / Workstation'),
                                        Forms\Components\TextInput::make('main_specifications.processor')
                                            ->label('Processor')
                                            ->placeholder('e.g. Intel Core i7 / AMD Ryzen 7'),
                                        Forms\Components\TextInput::make('main_specifications.graphics_card')
                                            ->label('Graphics Card')
                                            ->placeholder('e.g. RTX 4070 Super 12GB'),
                                        Forms\Components\TextInput::make('main_specifications.memory_ram')
                                            ->label('Memory (RAM)')
                                            ->placeholder('e.g. 32GB DDR5 6000MHz'),
                                        Forms\Components\TextInput::make('main_specifications.storage')
                                            ->label('Storage')
                                            ->placeholder('e.g. 1TB NVMe Gen4 SSD'),
                                        Forms\Components\TextInput::make('main_specifications.warranty')
                                            ->label('Warranty')
                                            ->placeholder('e.g. 3 Years Official ComLink Warranty'),
                                    ])
                                        ->columns(3)
                                        ->visible(fn (Get $get) => self::matchesCategory($get, ['computer'])),

                                    // M. Toners and Cartridges
                                    Forms\Components\Group::make([
                                        Forms\Components\TextInput::make('main_specifications.type')
                                            ->label('Type')
                                            ->placeholder('e.g. Ink Bottle / Laser Toner Cartridge'),
                                    ])
                                        ->columns(1)
                                        ->visible(fn (Get $get) => self::matchesCategory($get, ['toner', 'cartridge'])),

                                    // Notice for Brand & Condition only items
                                    Forms\Components\Placeholder::make('standard_specs_notice')
                                        ->label('Main Specifications Included')
                                        ->content('Brand and Condition are automatically applied from the classification and details above.')
                                        ->visible(fn (Get $get) => ! self::matchesAnyDetailedCategory($get)),
                                ]),

                            // Tab 2: Technical Specifications
                            Forms\Components\Tabs\Tab::make('Technical Specifications')
                                ->icon('heroicon-m-wrench-screwdriver')
                                ->schema([
                                    Forms\Components\Section::make('General Specifications')
                                        ->schema([
                                            Forms\Components\TextInput::make('technical_specifications.model_series')
                                                ->label('Model / Series')
                                                ->placeholder('e.g. ASUS ROG Zephyrus G16 (2024)'),
                                            Forms\Components\TextInput::make('technical_specifications.category_segment')
                                                ->label('Category Segment')
                                                ->placeholder('e.g. Processors, Laptops, Keyboards'),
                                            Forms\Components\TextInput::make('technical_specifications.condition')
                                                ->label('Condition')
                                                ->default('100% Brand New (Original Factory Sealed)'),
                                        ])->columns(3),

                                    Forms\Components\Section::make('Technical Performance')
                                        ->schema([
                                            Forms\Components\TextInput::make('technical_specifications.interface_connection')
                                                ->label('Interface / Connection')
                                                ->placeholder('e.g. High-Speed PCIe Gen 4 / USB 3.2 / AM5'),
                                            Forms\Components\TextInput::make('technical_specifications.architecture')
                                                ->label('Architecture')
                                                ->placeholder('e.g. Optimized Next-Gen Microarchitecture with Low Latency'),
                                            Forms\Components\TextInput::make('technical_specifications.thermal_efficiency')
                                                ->label('Thermal Efficiency')
                                                ->placeholder('e.g. Advanced cooling profile with heat dissipation fins'),
                                            Forms\Components\TextInput::make('technical_specifications.power_consumption')
                                                ->label('Power Consumption')
                                                ->placeholder('e.g. Eco-efficient power curve with dynamic voltage scaling'),
                                        ])->columns(2),

                                    Forms\Components\Section::make('Compatibility & Standards')
                                        ->schema([
                                            Forms\Components\TextInput::make('technical_specifications.operating_systems')
                                                ->label('Operating Systems')
                                                ->placeholder('e.g. Windows 11 / Windows 10 (64-bit), Linux Kernel 5.4+'),
                                            Forms\Components\TextInput::make('technical_specifications.certification')
                                                ->label('Certification')
                                                ->placeholder('e.g. CE, FCC, RoHS, EnergyStar Certified'),
                                            Forms\Components\TextInput::make('technical_specifications.dimensions_weight')
                                                ->label('Dimensions & Weight')
                                                ->placeholder('e.g. Standard Form Factor (~450g packaged)'),
                                            Forms\Components\TextInput::make('technical_specifications.included_in_box')
                                                ->label('Included in Box')
                                                ->placeholder('e.g. 1x Hardware Unit, Quick Manual, Safety Guide, Warranty Certificate'),
                                        ])->columns(2),
                                ]),
                        ]),
                ]),
        ];
    }

    /**
     * Check if active category/subcategory matches any of the given keywords
     */
    protected static function matchesCategory(Get $get, array $keywords): bool
    {
        $subId = $get('sub_category_id');
        if ($subId) {
            $sub = Category::find($subId);
            if ($sub) {
                $subSlug = Str::slug($sub->name);
                foreach ($keywords as $kw) {
                    if (str_contains($subSlug, $kw)) {
                        return true;
                    }
                }
            }
        }

        $catId = $get('category_id');
        if ($catId) {
            $cat = Category::find($catId);
            if ($cat) {
                $catSlug = Str::slug($cat->name);
                foreach ($keywords as $kw) {
                    if (str_contains($catSlug, $kw)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    protected static function matchesAnyDetailedCategory(Get $get): bool
    {
        return self::matchesCategory($get, [
            'laptop', 'processor', 'motherboard', 'cooler', 'memory',
            'ram', 'ssd', 'hard-disk', 'hdd', 'graphic', 'gpu',
            'power-supply', 'psu', 'case', 'chassis', 'monitor',
            'display', 'computer', 'toner', 'cartridge',
        ]);
    }
}
