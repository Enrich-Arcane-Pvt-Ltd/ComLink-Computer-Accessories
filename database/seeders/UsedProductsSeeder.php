<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Grn;
use App\Models\GrnItem;
use App\Models\InventoryProduct;
use App\Models\Product;
use App\Models\ProductSpecification;
use App\Models\ProductVariant;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class UsedProductsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding Used Computer Hardware, Systems, Monitors & Components...');

        // 1. Ensure Brands
        $brands = [
            'others' => [
                'name' => 'Others',
                'description' => 'Universal OEM hardware, unbranded components, and compatible accessories.',
            ],
            'intel' => [
                'name' => 'Intel',
                'description' => 'Leader in desktop processors, chipsets, and computing technology.',
            ],
            'nvidia' => [
                'name' => 'NVIDIA',
                'description' => 'World leader in graphics processing units (GPUs) and visual computing.',
            ],
            'samsung' => [
                'name' => 'Samsung',
                'description' => 'Global innovator in memory, V-NAND flash storage, and semiconductor technology.',
            ],
            'wd' => [
                'name' => 'WD',
                'description' => 'Western Digital storage solutions including internal desktop and laptop drives.',
            ],
        ];

        $brandModels = [];
        foreach ($brands as $slug => $meta) {
            $brandModels[$slug] = Brand::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $meta['name'],
                    'description' => $meta['description'],
                    'is_visible' => true,
                ]
            );
        }

        // 2. Ensure Supplier: TechZone Distributors (Pvt) Ltd
        $supplier = Supplier::where('name', 'like', '%TechZone%')->first();
        if (!$supplier) {
            $supplier = Supplier::firstOrCreate(
                ['name' => 'TechZone Distributors (Pvt) Ltd'],
                [
                    'contact_no' => '0112508888 / 0777123456',
                    'email' => 'sales@techzone.lk',
                    'address' => 'No 452, Galle Road, Colombo 03',
                ]
            );
        }

        // 3. Ensure Main Categories
        $productsCat = Category::firstOrCreate(
            ['slug' => 'products'],
            [
                'name' => 'Products',
                'parent_id' => null,
                'image' => 'images/categories/speakers.webp',
                'description' => 'Desktop computers, consumer electronics, and office hardware.',
                'is_visible' => true,
                'sort_order' => 5,
            ]
        );

        $monitorsCat = Category::firstOrCreate(
            ['slug' => 'monitors-displays'],
            [
                'name' => 'Monitors & Displays',
                'parent_id' => null,
                'image' => 'images/categories/computers.webp',
                'description' => 'Desktop monitors, LCD, LED displays, and presentation screens.',
                'is_visible' => true,
                'sort_order' => 4,
            ]
        );

        $pcComponentsCat = Category::firstOrCreate(
            ['slug' => 'pc-components'],
            [
                'name' => 'PC Components',
                'parent_id' => null,
                'image' => 'images/categories/processors.webp',
                'description' => 'Internal computer hardware, processors, motherboards, GPUs, RAM, and storage.',
                'is_visible' => true,
                'sort_order' => 2,
            ]
        );

        // 4. Ensure Subcategories
        $subCatDefs = [
            'system-units' => [
                'name' => 'System Units',
                'parent' => $productsCat,
                'image' => 'images/categories/computers.webp',
                'description' => 'Complete desktop system units, towers, and refurbished office computers.',
                'specs' => ['Processor', 'Memory (RAM)', 'Storage', 'Optical Drive', 'Form Factor'],
            ],
            'monitors' => [
                'name' => 'Monitors',
                'parent' => $monitorsCat,
                'image' => 'images/categories/computers.webp',
                'description' => 'Square and widescreen LCD/LED monitors for CCTV, office work, and gaming.',
                'specs' => ['Screen Size', 'Resolution', 'Panel Technology', 'Input Ports', 'Refresh Rate'],
            ],
            'processors' => [
                'name' => 'Processors',
                'parent' => $pcComponentsCat,
                'image' => 'images/categories/processors.webp',
                'description' => 'Intel Core desktop processors and CPUs.',
                'specs' => ['Processor Model', 'Socket Type', 'Cores / Threads', 'Clock Speed', 'Cache Memory'],
            ],
            'motherboards' => [
                'name' => 'Motherboards',
                'parent' => $pcComponentsCat,
                'image' => 'images/categories/motherboards.webp',
                'description' => 'Desktop motherboards for various Intel socket generations.',
                'specs' => ['Form Factor', 'Socket Type', 'Chipset', 'RAM Support', 'Expansion Slots'],
            ],
            'graphic-cards' => [
                'name' => 'Graphic Cards',
                'parent' => $pcComponentsCat,
                'image' => 'images/categories/graphic_cards.webp',
                'description' => 'Dedicated NVIDIA GeForce graphics cards for gaming and display expansion.',
                'specs' => ['GPU Engine', 'Video Memory (VRAM)', 'Memory Bus', 'Interface', 'Display Outputs'],
            ],
            'ssd' => [
                'name' => 'SSD',
                'parent' => $pcComponentsCat,
                'image' => 'images/categories/ssd.webp',
                'description' => 'High-speed SATA solid state drives for rapid boot and storage.',
                'specs' => ['Capacity', 'Interface', 'Form Factor', 'Read Speed', 'Write Speed'],
            ],
            'hard-disk' => [
                'name' => 'Hard Disk',
                'parent' => $pcComponentsCat,
                'image' => 'images/categories/hard_disk.webp',
                'description' => 'Desktop and laptop SATA mechanical hard disk drives.',
                'specs' => ['Storage Capacity', 'Form Factor', 'Rotational Speed', 'Interface', 'Buffer / Cache'],
            ],
            'ram' => [
                'name' => 'RAM',
                'parent' => $pcComponentsCat,
                'image' => 'images/categories/memory.webp',
                'description' => 'DDR2, DDR3, and DDR4 desktop and laptop system memory modules.',
                'specs' => ['Memory Capacity', 'Memory Type', 'Module Type', 'Frequency Speed', 'Voltage'],
            ],
            'processor-fans' => [
                'name' => 'Processor Fans',
                'parent' => $pcComponentsCat,
                'image' => 'images/categories/coolers.webp',
                'description' => 'Original heatsink and cooling fans for Intel desktop CPU sockets.',
                'specs' => ['Compatible Sockets', 'Cooler Type', 'Connector Type', 'Max Fan Speed', 'Heatsink Material'],
            ],
            'power-supply' => [
                'name' => 'Power Supply',
                'parent' => $pcComponentsCat,
                'image' => 'images/categories/power_supply.webp',
                'description' => 'Standard ATX desktop power supply units (PSU) with PCIe power.',
                'specs' => ['Rated Wattage', 'Form Factor', 'PCIe Connector', 'Cooling Fan', 'Main Connector'],
            ],
        ];

        $subCatModels = [];
        foreach ($subCatDefs as $slug => $def) {
            $cat = Category::where('parent_id', $def['parent']->id)
                ->where(function ($q) use ($slug, $def) {
                    $q->where('slug', $slug)->orWhere('name', $def['name']);
                })
                ->first();

            if (!$cat) {
                $cat = Category::create([
                    'name' => $def['name'],
                    'slug' => $slug,
                    'parent_id' => $def['parent']->id,
                    'image' => $def['image'],
                    'description' => $def['description'],
                    'is_visible' => true,
                ]);
            } else {
                $cat->update([
                    'name' => $def['name'],
                    'slug' => $slug,
                    'parent_id' => $def['parent']->id,
                    'is_visible' => true,
                ]);
            }

            $subCatModels[$slug] = [
                'model' => $cat,
                'parent' => $def['parent'],
                'specs' => $def['specs'],
            ];
        }

        // 5. Register Specifications
        $techSpecsDef = [
            'general' => [
                'Item Condition',
                'Model / Series',
                'Form Factor',
                'Compatibility Standard',
            ],
            'performance' => [
                'Operational Capacity',
                'Speed / Frequency',
                'Thermal & Power Efficiency',
                'Interface Transfer Rate',
            ],
            'compatibility' => [
                'Supported Platforms / OS',
                'Interface & Connector Ports',
                'Physical Standards',
                'Warranty Coverage',
            ],
        ];

        foreach ($subCatModels as $subKey => $subData) {
            $sCat = $subData['model'];
            $pCat = $subData['parent'];

            foreach ($subData['specs'] as $specTitle) {
                ProductSpecification::updateOrInsert(
                    [
                        'category_id' => $pCat->id,
                        'sub_category_id' => $sCat->id,
                        'specification_type' => 'main',
                        'title' => $specTitle,
                    ],
                    [
                        'sub_section' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            foreach ($techSpecsDef as $subSec => $titles) {
                foreach ($titles as $st) {
                    ProductSpecification::updateOrInsert(
                        [
                            'category_id' => $pCat->id,
                            'sub_category_id' => $sCat->id,
                            'specification_type' => 'technical',
                            'title' => $st,
                        ],
                        [
                            'sub_section' => $subSec,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }

        // 6. Direct GRN for Inventory
        $seedGrns = env('SEED_MOCK_GRNS', app()->environment('local'));
        $grn = null;
        if ($seedGrns) {
            $grn = Grn::updateOrCreate(
            ['grn_number' => 'GRN-2026-USED001'],
            [
                'grn_type' => 'direct',
                'direct_po_number' => 'DIR-PO-2026-USED01',
                'supplier_id' => $supplier->id,
                'notes' => 'Direct batch GRN for Used Desktop Systems, Monitors, Processors, Motherboards, GPUs, SSDs, HDDs, RAM, Fans & PSUs',
                'sub_total' => 0.00,
            ]
        );
        }

        // 7. Load Product Data JSON
        $dataPath = database_path('seeders/used_products_data.json');
        if (!File::exists($dataPath)) {
            $this->command->error("Data file not found: {$dataPath}");
            return;
        }

        $productsData = json_decode(File::get($dataPath), true);
        $totalGrnAmount = 0;

        $sourceBaseDir = base_path('ProductData/Used Items');
        $targetStorageBase = storage_path('app/public/products/used-items');
        if (!File::isDirectory($targetStorageBase)) {
            File::makeDirectory($targetStorageBase, 0755, true, true);
        }

        foreach ($productsData as $data) {
            $subCatSlug = $data['subcategory_slug'];
            $subCatEntry = $subCatModels[$subCatSlug] ?? null;

            if (!$subCatEntry) {
                $this->command->error("Subcategory entry not found for slug: {$subCatSlug}");
                continue;
            }

            $subCatObj = $subCatEntry['model'];
            $mainCatObj = $subCatEntry['parent'];

            $brandSlug = $data['brand_slug'];
            $brandObj = $brandModels[$brandSlug] ?? $brandModels['others'];

            $price = (float) $data['price'];
            $salePrice = (float) $data['sale_price'];
            $costPrice = (float) $data['cost_price'];
            $initialStock = 10;

            // Folder resolution
            $sourceFolderPath = $sourceBaseDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $data['image_folder_rel']);
            $destFolder = $targetStorageBase . DIRECTORY_SEPARATOR . $data['slug'];
            if (!File::isDirectory($destFolder)) {
                File::makeDirectory($destFolder, 0755, true, true);
            }

            // Copy Images
            $images = [];
            if (File::isDirectory($sourceFolderPath)) {
                $files = File::files($sourceFolderPath);
                $imgIndex = 1;
                foreach ($files as $file) {
                    $ext = strtolower($file->getExtension());
                    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                        continue;
                    }

                    $cleanName = 'image_' . $imgIndex . '.' . $ext;
                    $destPath = $destFolder . DIRECTORY_SEPARATOR . $cleanName;
                    File::copy($file->getRealPath(), $destPath);
                    $images[] = 'products/used-items/' . $data['slug'] . '/' . $cleanName;
                    $imgIndex++;
                }
            }

            // Fallback placeholder if no images found
            if (empty($images)) {
                $images = ['images/categories/computers.webp'];
            }

            // Create / Update Product
            $product = Product::updateOrCreate(
                ['product_code' => $data['product_code']],
                [
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                    'sku' => $data['sku'],
                    'brand_id' => $brandObj->id,
                    'supplier_id' => $supplier->id,
                    'description' => $data['description'],
                    'type' => 'simple',
                    'condition' => 'Used',
                    'price' => $price,
                    'sale_price' => $salePrice,
                    'cost_price' => $costPrice,
                    'grn_price' => $costPrice,
                    'quantity' => $initialStock,
                    'inventory_stock' => $initialStock,
                    'show_quantity' => $initialStock,
                    'stock_added_count' => $initialStock,
                    'stock_added_date' => now(),
                    'security_stock' => 3,
                    'warranty_duration' => $data['warranty_duration'] ?? 1,
                    'warranty_unit' => $data['warranty_unit'] ?? 'Months',
                    'images' => $images,
                    'main_specifications' => $data['main_specifications'] ?? [],
                    'technical_specifications' => $data['technical_specifications'] ?? [],
                    'is_visible' => true,
                    'is_featured' => false,
                ]
            );

            // Sync Category: Main Category and Subcategory
            $product->categories()->sync([$mainCatObj->id, $subCatObj->id]);

            // Sync InventoryProduct
            $invProduct = InventoryProduct::updateOrCreate(
                ['product_code' => $data['product_code']],
                [
                    'product_name' => $data['name'],
                    'product_image' => $images[0],
                    'supplier_id' => $supplier->id,
                ]
            );

            // Clean up any stale variants
            ProductVariant::where('product_id', $product->id)->delete();

            // Create GrnItem
            if ($seedGrns && $grn) {
                GrnItem::updateOrCreate(
                [
                    'grn_id' => $grn->id,
                    'product_code' => $data['product_code'],
                    'product_variant_id' => null,
                ],
                [
                    'inventory_product_id' => $invProduct->id,
                    'product_name' => $data['name'],
                    'color_name' => null,
                    'product_quantity' => $initialStock,
                    'price_per_unit' => $costPrice,
                    'total' => $costPrice * $initialStock,
                    'product_condition' => 'Used',
                ]
            );

            $totalGrnAmount += ($costPrice * $initialStock);
            }

            $this->command->line("  [OK] Seeded: {$data['name']} [Brand: {$brandObj->name} | Sub: {$subCatObj->name}] (Price: LKR " . number_format($salePrice, 2) . " | Imgs: " . count($images) . ")");
        }

        // Update GRN total
        if ($grn) { $grn->update(['sub_total' => $totalGrnAmount]); }

        // Sync Product Available Quantities across the system
        if (method_exists(Product::class, 'syncAllProductAvailableQuantities')) {
            Product::syncAllProductAvailableQuantities();
        }

        $this->command->info("All " . count($productsData) . " products successfully seeded with GRN total: LKR " . number_format($totalGrnAmount, 2));
    }
}

