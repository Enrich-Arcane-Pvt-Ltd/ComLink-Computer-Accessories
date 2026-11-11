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

class NetworkAccessoriesProductsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding Network Accessories & Equipment products under Products Category...');

        // 1. Ensure Brands
        $brands = [
            'tp-link' => Brand::updateOrCreate(
                ['slug' => 'tp-link'],
                [
                    'name' => 'TP-LINK',
                    'description' => 'World Leading Provider of Consumer and Business Networking Devices',
                    'is_visible' => true,
                ]
            ),
            'ruijie' => Brand::updateOrCreate(
                ['slug' => 'ruijie'],
                [
                    'name' => 'RUIJIE',
                    'description' => 'Enterprise Grade Cloud Managed Switches and Network Equipment',
                    'is_visible' => true,
                ]
            ),
            'ag' => Brand::updateOrCreate(
                ['slug' => 'ag'],
                [
                    'name' => 'A&G',
                    'description' => 'Commercial Server Rack Cabinets, Patch Panels & Power Distribution Units',
                    'is_visible' => true,
                ]
            ),
            'redlink' => Brand::updateOrCreate(
                ['slug' => 'redlink'],
                [
                    'name' => 'REDLINK',
                    'description' => 'High-Performance Structured Cabling & Shielded Patch Panels',
                    'is_visible' => true,
                ]
            ),
            'others' => Brand::updateOrCreate(
                ['slug' => 'others'],
                [
                    'name' => 'Others',
                    'description' => 'Universal OEM Networking Hardware & Accessories',
                    'is_visible' => true,
                ]
            ),
        ];

        // 2. Ensure Supplier
        $supplier = Supplier::where('name', 'like', '%TechZone%')->first();
        if (!$supplier) {
            $supplier = Supplier::firstOrCreate(
                ['name' => 'TechZone Distributors (Pvt) Ltd'],
                [
                    'contact_no' => '+94 11 234 5678',
                    'email' => 'sales@techzonedistributors.lk',
                    'address' => 'Peripherals & Network Equipment Distribution Center, Colombo 04',
                ]
            );
        }

        // 3. Ensure Main Category: Products (ID: 27)
        $productsCat = Category::where('slug', 'products')->first();
        if (!$productsCat) {
            $productsCat = Category::updateOrCreate(
                ['slug' => 'products'],
                [
                    'name' => 'Products',
                    'parent_id' => null,
                    'image' => 'images/categories/products.png',
                    'description' => 'Office equipment, computers, accessories, printers, POS hardware, networking equipment, and supplies.',
                    'is_visible' => true,
                    'sort_order' => 5,
                ]
            );
        } else {
            $productsCat->update(['is_visible' => true]);
        }

        // 4. Ensure Subcategories under Products
        $subCategoriesDefs = [
            'switches' => [
                'name' => 'Switches',
                'description' => 'Unmanaged, managed, and gigabit ethernet desktop and rackmount network switches.',
                'image' => 'images/categories/switches.webp',
                'sort_order' => 12,
            ],
            'access-points' => [
                'name' => 'Access Points',
                'description' => 'Ceiling mount, multi-mode, and dual-band commercial wireless access points.',
                'image' => 'images/categories/access-points.webp',
                'sort_order' => 13,
            ],
            'range-extenders' => [
                'name' => 'Range Extenders',
                'description' => 'Wall-plug wireless range extenders and Wi-Fi signal boosters.',
                'image' => 'images/categories/range-extenders.webp',
                'sort_order' => 14,
            ],
            'routers-adapters' => [
                'name' => 'Routers & Adapters',
                'description' => 'Multi-WAN load balance routers, 4G LTE SIM routers, DSL wireless routers, and USB Wi-Fi adapters.',
                'image' => 'images/categories/routers-adapters.webp',
                'sort_order' => 15,
            ],
            'rack-network-accessories' => [
                'name' => 'Rack & Network Accessories',
                'description' => 'Server rack patch panels, 1U cable management organizers, and power distribution units (PDU).',
                'image' => 'images/categories/rack-network-accessories.webp',
                'sort_order' => 16,
            ],
        ];

        $subCats = [];
        foreach ($subCategoriesDefs as $subSlug => $sDef) {
            $subObj = Category::where('parent_id', $productsCat->id)
                ->where(function ($q) use ($subSlug, $sDef) {
                    $q->where('slug', $subSlug)->orWhere('name', $sDef['name']);
                })
                ->first();

            if (!$subObj) {
                $subObj = Category::create([
                    'name' => $sDef['name'],
                    'slug' => $subSlug,
                    'parent_id' => $productsCat->id,
                    'image' => $sDef['image'],
                    'description' => $sDef['description'],
                    'is_visible' => true,
                    'sort_order' => $sDef['sort_order'],
                ]);
            } else {
                $subObj->update([
                    'name' => $sDef['name'],
                    'slug' => $subSlug,
                    'parent_id' => $productsCat->id,
                    'is_visible' => true,
                ]);
            }
            $subCats[$subSlug] = $subObj;

            // Specifications for each subcategory
            $mainSpecTitles = [
                'Device Type / Function',
                'Port / Interface Configuration',
                'Throughput / Switching Capacity',
                'Standards & Protocols',
                'Power Supply & Consumption',
                'Mounting & Form Factor',
            ];

            $techSpecsDef = [
                'general' => [
                    'Model / Series',
                    'Category Segment',
                    'Condition',
                ],
                'performance' => [
                    'Interface / Connection',
                    'Architecture',
                    'Thermal Efficiency',
                    'Power Consumption',
                ],
                'compatibility' => [
                    'Operating Systems',
                    'Certification',
                    'Dimensions & Weight',
                    'Included in Box',
                ],
            ];

            foreach ($mainSpecTitles as $t) {
                ProductSpecification::updateOrInsert(
                    [
                        'category_id' => $productsCat->id,
                        'sub_category_id' => $subObj->id,
                        'specification_type' => 'main',
                        'title' => $t,
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
                            'category_id' => $productsCat->id,
                            'sub_category_id' => $subObj->id,
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

        // 5. Direct GRN for Inventory
        $seedGrns = env('SEED_MOCK_GRNS', app()->environment('local'));
        $grn = null;
        if ($seedGrns) {
            $grn = Grn::updateOrCreate(
            ['grn_number' => 'GRN-2026-NET001'],
            [
                'grn_type' => 'direct',
                'direct_po_number' => 'DIR-PO-2026-NET01',
                'supplier_id' => $supplier->id,
                'notes' => 'Direct batch GRN for Network Accessories & Equipment (TP-LINK, RUIJIE, A&G, REDLINK, Others)',
                'sub_total' => 0.00,
            ]
        );
        }

        // 6. Load Product Data JSON
        $dataPath = database_path('seeders/network_accessories_products_data.json');
        if (!File::exists($dataPath)) {
            $this->command->error("Data file not found: {$dataPath}");
            return;
        }

        $productsData = json_decode(File::get($dataPath), true);
        $totalGrnAmount = 0;

        $sourceBaseDir = base_path('ProductData/NETWORK');
        $targetStorageBase = storage_path('app/public/products/network');
        if (!File::isDirectory($targetStorageBase)) {
            File::makeDirectory($targetStorageBase, 0755, true, true);
        }

        // Read all folders in source directory to handle case/special characters
        $sourceFolders = [];
        if (File::isDirectory($sourceBaseDir)) {
            foreach (File::directories($sourceBaseDir) as $dirPath) {
                $sourceFolders[strtolower(basename($dirPath))] = $dirPath;
            }
        }

        foreach ($productsData as $data) {
            $brandSlug = $data['brand_slug'];
            $brandObj = $brands[$brandSlug] ?? $brands['others'];
            $subSlug = $data['subcat'];
            $subCategory = $subCats[$subSlug] ?? null;

            $price = (float) $data['price'];
            $salePrice = (float) $data['sale_price'];
            $costPrice = (float) $data['cost_price'];
            $initialStock = (int) ($data['stock'] ?? 10);
            $targetFolder = strtolower($data['folder']);

            // Find folder
            $matchedDirPath = $sourceFolders[$targetFolder] ?? null;
            if (!$matchedDirPath) {
                foreach ($sourceFolders as $sName => $sFull) {
                    if (str_contains($sName, strtolower(substr($data['folder'], 0, 20)))) {
                        $matchedDirPath = $sFull;
                        break;
                    }
                }
            }

            $destFolder = $targetStorageBase . '/' . $data['slug'];
            if (!File::isDirectory($destFolder)) {
                File::makeDirectory($destFolder, 0755, true, true);
            }

            // Copy Images
            $images = [];
            if ($matchedDirPath && File::isDirectory($matchedDirPath)) {
                $files = File::files($matchedDirPath);
                foreach ($files as $file) {
                    $fileName = $file->getFilename();
                    $ext = $file->getExtension();
                    if (!in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp'])) {
                        continue;
                    }

                    $destPath = $destFolder . '/' . $fileName;
                    File::copy($file->getRealPath(), $destPath);
                    $images[] = 'products/network/' . $data['slug'] . '/' . $fileName;
                }
            }

            // Fallback placeholder if no images found
            if (empty($images)) {
                $images = ['images/categories/' . $subSlug . '.webp'];
            }

            // Create / Update Product
            $product = Product::updateOrCreate(
                ['product_code' => $data['product_code']],
                [
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                    'sku' => $data['product_code'],
                    'brand_id' => $brandObj->id,
                    'supplier_id' => $supplier->id,
                    'description' => $data['description'],
                    'type' => 'simple',
                    'condition' => 'Brand New',
                    'price' => $price,
                    'sale_price' => $salePrice,
                    'cost_price' => $costPrice,
                    'grn_price' => $costPrice,
                    'quantity' => $initialStock,
                    'inventory_stock' => $initialStock,
                    'show_quantity' => $initialStock,
                    'stock_added_count' => $initialStock,
                    'stock_added_date' => now(),
                    'security_stock' => 5,
                    'warranty_duration' => $data['warranty_duration'] ?? 700,
                    'warranty_unit' => $data['warranty_unit'] ?? 'Days',
                    'images' => $images,
                    'main_specifications' => $data['main_specs'] ?? [],
                    'technical_specifications' => $data['tech_specs'] ?? [],
                    'is_visible' => true,
                    'is_featured' => false,
                ]
            );

            // Sync Category: Products and specific subcategory
            $catIds = [$productsCat->id];
            if ($subCategory) {
                $catIds[] = $subCategory->id;
            }
            $product->categories()->sync($catIds);

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
                    'product_condition' => 'Brand New',
                ]
            );

            $totalGrnAmount += ($costPrice * $initialStock);
            }

            $this->command->line("  ✓ Seeded: {$data['name']} [{$brandObj->name}] (Subcat: {$subCategory->name} | Stock: {$initialStock})");
        }

        // Update GRN total
        if ($grn) { $grn->update(['sub_total' => $totalGrnAmount]); }

        // Sync Product Available Quantities across the system
        if (method_exists(Product::class, 'syncAllProductAvailableQuantities')) {
            Product::syncAllProductAvailableQuantities();
        }

        $this->command->info("All 22 Network Accessories & Equipment products successfully seeded with GRN total: LKR {$totalGrnAmount}");
    }
}

