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

class HikvisionCameraProductsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding Hikvision Cameras & DVRs under Cameras Category...');

        // 1. Ensure Brand: HIKVISION
        $brand = Brand::updateOrCreate(
            ['slug' => 'hikvision'],
            [
                'name' => 'HIKVISION',
                'description' => 'Hikvision Digital Technology - World leading IoT solution provider with video as its core competency.',
                'is_visible' => true,
            ]
        );

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

        // 3. Ensure Main Category: Cameras (ID: 39)
        $camerasCat = Category::where('slug', 'cameras')->first();
        if (!$camerasCat) {
            $camerasCat = Category::updateOrCreate(
                ['slug' => 'cameras'],
                [
                    'name' => 'Cameras',
                    'parent_id' => null,
                    'image' => 'images/categories/bullet-cameras.webp',
                    'description' => 'Security cameras, CCTV systems, DVRs, NVRs, and smart surveillance equipment.',
                    'is_visible' => true,
                    'sort_order' => 8,
                ]
            );
        } else {
            $camerasCat->update(['is_visible' => true]);
        }

        // 4. Ensure Subcategories under Cameras
        $subCategoriesDef = [
            'dvrs' => [
                'name' => 'DVRs',
                'description' => 'Hikvision High-Definition Digital Video Recorders (1080P Lite & 4.0MP Lite DVRs)',
                'image' => 'images/categories/nvr.webp',
                'sort_order' => 1,
            ],
            'value-series-tvi-cameras' => [
                'name' => 'Value Series TVI Cameras',
                'description' => 'Hikvision 2.0MP Turbo HD Value Series TVI Cameras (Indoor & Outdoor Bullet & Turret Cameras)',
                'image' => 'images/categories/bullet-cameras.webp',
                'sort_order' => 2,
            ],
            'silver-color-view-cameras' => [
                'name' => 'Silver ColorVu Cameras',
                'description' => 'Hikvision 2.0MP Silver ColorVu Full-Color Night Vision Cameras with Built-in Mic',
                'image' => 'images/categories/bullet-cameras.webp',
                'sort_order' => 3,
            ],
            'gold-color-view-cameras' => [
                'name' => 'Gold ColorVu Cameras',
                'description' => 'Hikvision 2.0MP Gold ColorVu Premium 24/7 Colorful Imaging Cameras with F1.0 Super Aperture & Built-in Mic',
                'image' => 'images/categories/bullet-cameras.webp',
                'sort_order' => 4,
            ],
        ];

        $subCategoryModels = [];
        foreach ($subCategoriesDef as $slug => $def) {
            $subCat = Category::where('parent_id', $camerasCat->id)
                ->where(function ($q) use ($slug, $def) {
                    $q->where('slug', $slug)
                        ->orWhere('name', $def['name'])
                        ->orWhere('slug', 'tvi-cameras')
                        ->orWhere('name', 'TVI Cameras');
                })
                ->first();

            if (!$subCat) {
                $subCat = Category::create([
                    'name' => $def['name'],
                    'slug' => $slug,
                    'parent_id' => $camerasCat->id,
                    'image' => $def['image'],
                    'description' => $def['description'],
                    'is_visible' => true,
                    'sort_order' => $def['sort_order'],
                ]);
            } else {
                $subCat->update([
                    'name' => $def['name'],
                    'slug' => $slug,
                    'parent_id' => $camerasCat->id,
                    'is_visible' => true,
                    'sort_order' => $def['sort_order'],
                ]);
            }
            $subCategoryModels[$slug] = $subCat;
            if ($slug === 'value-series-tvi-cameras') {
                $subCategoryModels['tvi-cameras'] = $subCat;
            }
        }

        // 5. Ensure Product Specification Definitions for Cameras & Subcategories
        $mainSpecTitles = [
            'Resolution',
            'Night Vision',
            'Connectivity',
            'Audio',
            'Storage Support',
            'Field of View',
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

        foreach ($subCategoryModels as $subCat) {
            foreach ($mainSpecTitles as $t) {
                ProductSpecification::updateOrInsert(
                    [
                        'category_id' => $camerasCat->id,
                        'sub_category_id' => $subCat->id,
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
                            'category_id' => $camerasCat->id,
                            'sub_category_id' => $subCat->id,
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
            ['grn_number' => 'GRN-2026-HIK001'],
            [
                'grn_type' => 'direct',
                'direct_po_number' => 'DIR-PO-2026-HIK01',
                'supplier_id' => $supplier->id,
                'notes' => 'Direct batch GRN for Hikvision DVRs and Cameras (TVI, Silver ColorVu, Gold ColorVu)',
                'sub_total' => 0.00,
            ]
        );
        }

        // 7. Load Product Data JSON
        $dataPath = database_path('seeders/hikvision_products_data.json');
        if (!File::exists($dataPath)) {
            $this->command->error("Data file not found: {$dataPath}");
            return;
        }

        $productsData = json_decode(File::get($dataPath), true);
        $totalGrnAmount = 0;

        $sourceBaseDir = base_path('ProductData/HIKVISION DS-2CE12DF3T-LFS CAMERA');
        $targetStorageBase = storage_path('app/public/products/hikvision');
        if (!File::isDirectory($targetStorageBase)) {
            File::makeDirectory($targetStorageBase, 0755, true, true);
        }

        foreach ($productsData as $data) {
            $subCatSlug = $data['sub_category_slug'];
            $subCatObj = $subCategoryModels[$subCatSlug] ?? null;

            if (!$subCatObj) {
                $this->command->error("Subcategory not found for slug: {$subCatSlug}");
                continue;
            }

            $price = (float) $data['price'];
            $salePrice = (float) $data['sale_price'];
            $costPrice = (float) $data['cost_price'];
            $initialStock = (int) ($data['stock'] ?? 10);

            // Folder resolution
            $sourceFolderPath = $sourceBaseDir . DIRECTORY_SEPARATOR . $data['section'] . DIRECTORY_SEPARATOR . $data['folder'];
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

                    // Create clean filename
                    $cleanName = 'image_' . $imgIndex . '.' . $ext;
                    $destPath = $destFolder . DIRECTORY_SEPARATOR . $cleanName;
                    File::copy($file->getRealPath(), $destPath);
                    $images[] = 'products/hikvision/' . $data['slug'] . '/' . $cleanName;
                    $imgIndex++;
                }
            }

            // Fallback placeholder if no images found
            if (empty($images)) {
                $images = ['images/categories/bullet-cameras.webp'];
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
                    'warranty_duration' => $data['warranty_duration'] ?? 2,
                    'warranty_unit' => $data['warranty_unit'] ?? 'Years',
                    'images' => $images,
                    'main_specifications' => $data['main_specs'] ?? [],
                    'technical_specifications' => $data['tech_specs'] ?? [],
                    'is_visible' => true,
                    'is_featured' => false,
                ]
            );

            // Sync Category: Cameras and Subcategory
            $product->categories()->sync([$camerasCat->id, $subCatObj->id]);

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

            $this->command->line("  [OK] Seeded: {$data['name']} [Sub: {$subCatObj->name}] (Price: LKR " . number_format($salePrice, 2) . " | Stock: {$initialStock} | Images: " . count($images) . ")");
        }

        // Update GRN total
        if ($grn) { $grn->update(['sub_total' => $totalGrnAmount]); }

        // Sync Product Available Quantities across the system
        if (method_exists(Product::class, 'syncAllProductAvailableQuantities')) {
            Product::syncAllProductAvailableQuantities();
        }

        $this->command->info("All 25 Hikvision camera & DVR products successfully seeded with GRN total: LKR {$totalGrnAmount}");
    }
}

