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

class WallMountProductsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding TV Wall Mounts, Monitor Arms, Laptop Stand & Cooling Pad products...');

        // 1. Ensure Brand: Others
        $brand = Brand::where('slug', 'others')->first();
        if (!$brand) {
            $brand = Brand::updateOrCreate(
                ['slug' => 'others'],
                [
                    'name' => 'Others',
                    'description' => 'Universal OEM hardware, mounts, stands, and accessories.',
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

        // 3. Ensure Main Categories: Products (ID: 27) and Peripherals (ID: 12)
        $productsCat = Category::where('slug', 'products')->first();
        if (!$productsCat) {
            $productsCat = Category::create([
                'name' => 'Products',
                'slug' => 'products',
                'parent_id' => null,
                'image' => 'images/categories/speakers.webp',
                'description' => 'Consumer electronics, audio devices, smart accessories, and peripheral products.',
                'is_visible' => true,
                'sort_order' => 5,
            ]);
        }

        $peripheralsCat = Category::where('slug', 'peripherals')->first();
        if (!$peripheralsCat) {
            $peripheralsCat = Category::create([
                'name' => 'Peripherals',
                'slug' => 'peripherals',
                'parent_id' => null,
                'image' => 'images/categories/keyboards.webp',
                'description' => 'Computer peripherals, input devices, audio headsets, cooling pads, and cables.',
                'is_visible' => true,
                'sort_order' => 3,
            ]);
        }

        // 4. Ensure Subcategories:
        // A) Cooling Pads under Peripherals
        $coolingPadsSubCat = Category::where('parent_id', $peripheralsCat->id)
            ->where(function ($q) {
                $q->where('slug', 'cooling-pads')->orWhere('name', 'Cooling Pads');
            })
            ->first();

        if (!$coolingPadsSubCat) {
            $coolingPadsSubCat = Category::create([
                'name' => 'Cooling Pads',
                'slug' => 'cooling-pads',
                'parent_id' => $peripheralsCat->id,
                'image' => 'images/categories/coolers.webp',
                'description' => 'High-performance RGB gaming and office laptop cooling pads and chill mats.',
                'is_visible' => true,
                'sort_order' => 2,
            ]);
        } else {
            $coolingPadsSubCat->update([
                'name' => 'Cooling Pads',
                'slug' => 'cooling-pads',
                'parent_id' => $peripheralsCat->id,
                'is_visible' => true,
            ]);
        }

        // B) Wall Mounts under Products
        $wallMountsSubCat = Category::where('parent_id', $productsCat->id)
            ->where(function ($q) {
                $q->where('slug', 'wall-mounts')->orWhere('name', 'Wall Mounts');
            })
            ->first();

        if (!$wallMountsSubCat) {
            $wallMountsSubCat = Category::create([
                'name' => 'Wall Mounts',
                'slug' => 'wall-mounts',
                'parent_id' => $productsCat->id,
                'image' => 'images/categories/computers.webp',
                'description' => 'Heavy-duty TV wall brackets, articulating full-motion arms, monitor desk mounts, and ceiling stands.',
                'is_visible' => true,
                'sort_order' => 18,
            ]);
        } else {
            $wallMountsSubCat->update([
                'name' => 'Wall Mounts',
                'slug' => 'wall-mounts',
                'parent_id' => $productsCat->id,
                'is_visible' => true,
                'sort_order' => 18,
            ]);
        }

        // C) Laptop Stands under Products
        $laptopStandsSubCat = Category::where('parent_id', $productsCat->id)
            ->where(function ($q) {
                $q->where('slug', 'laptop-stands')->orWhere('name', 'Laptop Stands');
            })
            ->first();

        if (!$laptopStandsSubCat) {
            $laptopStandsSubCat = Category::create([
                'name' => 'Laptop Stands',
                'slug' => 'laptop-stands',
                'parent_id' => $productsCat->id,
                'image' => 'images/categories/tables.webp',
                'description' => 'Ergonomic aluminum foldable laptop risers, desktop stands, and tablet holders.',
                'is_visible' => true,
                'sort_order' => 19,
            ]);
        } else {
            $laptopStandsSubCat->update([
                'name' => 'Laptop Stands',
                'slug' => 'laptop-stands',
                'parent_id' => $productsCat->id,
                'is_visible' => true,
                'sort_order' => 19,
            ]);
        }

        $allSubCategories = [
            'wall-mounts' => [
                'model' => $wallMountsSubCat,
                'parent' => $productsCat,
                'specs' => [
                    'Mount Type',
                    'Screen Size Compatibility',
                    'VESA Compatibility',
                    'Maximum Load Capacity',
                    'Distance to Wall',
                    'Tilt & Swivel Range',
                    'Material',
                ],
            ],
            'laptop-stands' => [
                'model' => $laptopStandsSubCat,
                'parent' => $productsCat,
                'specs' => [
                    'Stand Type',
                    'Device Compatibility',
                    'Height Adjustment',
                    'Material',
                    'Portability',
                    'Weight Capacity',
                ],
            ],
            'cooling-pads' => [
                'model' => $coolingPadsSubCat,
                'parent' => $peripheralsCat,
                'specs' => [
                    'Cooling Type',
                    'Laptop Size Support',
                    'Fan Configuration',
                    'Fan Speed Control',
                    'Lighting',
                    'Height Adjustment',
                    'USB Interface',
                ],
            ],
        ];

        // 5. Register Specifications
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

        foreach ($allSubCategories as $subKey => $subData) {
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
            ['grn_number' => 'GRN-2026-WM001'],
            [
                'grn_type' => 'direct',
                'direct_po_number' => 'DIR-PO-2026-WM01',
                'supplier_id' => $supplier->id,
                'notes' => 'Direct batch GRN for TV Wall Mounts, Ceiling Brackets, Monitor Arms, Laptop Cooling Pad & Stands',
                'sub_total' => 0.00,
            ]
        );
        }

        // 7. Load Product Data JSON
        $dataPath = database_path('seeders/wall_mount_products_data.json');
        if (!File::exists($dataPath)) {
            $this->command->error("Data file not found: {$dataPath}");
            return;
        }

        $productsData = json_decode(File::get($dataPath), true);
        $totalGrnAmount = 0;

        $sourceBaseDir = base_path('ProductData/WALL MOUNT');
        $targetStorageBase = storage_path('app/public/products/wall-mount');
        if (!File::isDirectory($targetStorageBase)) {
            File::makeDirectory($targetStorageBase, 0755, true, true);
        }

        foreach ($productsData as $data) {
            $subCatSlug = $data['sub_category_slug'];
            $subCatEntry = $allSubCategories[$subCatSlug] ?? null;

            if (!$subCatEntry) {
                $this->command->error("Subcategory entry not found for slug: {$subCatSlug}");
                continue;
            }

            $subCatObj = $subCatEntry['model'];
            $mainCatObj = $subCatEntry['parent'];

            $price = (float) $data['price'];
            $salePrice = (float) $data['sale_price'];
            $costPrice = (float) $data['cost_price'];
            $initialStock = (int) ($data['stock'] ?? 10);

            // Folder resolution
            $sourceFolderPath = $sourceBaseDir . DIRECTORY_SEPARATOR . $data['folder'];
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
                    $images[] = 'products/wall-mount/' . $data['slug'] . '/' . $cleanName;
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
                    'sku' => $data['product_code'],
                    'brand_id' => $brand->id,
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
                    'warranty_duration' => $data['warranty_duration'] ?? 6,
                    'warranty_unit' => $data['warranty_unit'] ?? 'Months',
                    'images' => $images,
                    'main_specifications' => $data['main_specs'] ?? [],
                    'technical_specifications' => $data['tech_specs'] ?? [],
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
                    'product_condition' => 'Brand New',
                ]
            );

            $totalGrnAmount += ($costPrice * $initialStock);
            }

            $this->command->line("  [OK] Seeded: {$data['name']} [Cat: {$mainCatObj->name} -> Sub: {$subCatObj->name}] (Price: LKR " . number_format($salePrice, 2) . " | Stock: {$initialStock} | Images: " . count($images) . ")");
        }

        // Update GRN total
        if ($grn) { $grn->update(['sub_total' => $totalGrnAmount]); }

        // Sync Product Available Quantities across the system
        if (method_exists(Product::class, 'syncAllProductAvailableQuantities')) {
            Product::syncAllProductAvailableQuantities();
        }

        $this->command->info("All 12 products successfully seeded with GRN total: LKR {$totalGrnAmount}");
    }
}

