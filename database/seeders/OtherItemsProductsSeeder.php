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

class OtherItemsProductsSeeder extends Seeder
{
    /**
     * Seed products from OTHER ITEM section of HIKVISION-DPP.pdf
     */
    public function run(): void
    {
        $this->command->info("Starting Other Items Products Seeder...");

        // 1. Ensure Brands
        $brands = [
            'hikvision' => [
                'name' => 'HIKVISION',
                'description' => 'World leading provider of security products and CCTV accessories.',
                'logo' => 'images/brands/hikvision.png',
            ],
            'pelcco' => [
                'name' => 'PELCCO',
                'description' => 'Reliable manufacturer of CCTV coaxial cables and surveillance accessories.',
                'logo' => 'images/brands/others.png',
            ],
            'acl' => [
                'name' => 'ACL',
                'description' => 'Sri Lanka\'s premier manufacturer of high-grade copper cables and electrical accessories.',
                'logo' => 'images/brands/others.png',
            ],
            'others' => [
                'name' => 'Others',
                'description' => 'Standard high-quality unbranded hardware and accessories.',
                'logo' => 'images/brands/others.png',
            ],
        ];

        $brandModels = [];
        foreach ($brands as $slug => $b) {
            $model = Brand::whereRaw('LOWER(name) = ?', [strtolower($b['name'])])
                ->orWhere('slug', $slug)
                ->first();

            if (!$model) {
                $model = Brand::create([
                    'name' => $b['name'],
                    'slug' => $slug,
                    'description' => $b['description'],
                    'logo' => $b['logo'],
                    'is_visible' => true,
                ]);
            } else {
                $model->update(['is_visible' => true]);
            }
            $brandModels[$slug] = $model;
        }

        // 2. Ensure Supplier: ComLink Distribution
        $supplier = Supplier::where('name', 'ComLink Distribution')->first();
        if (!$supplier) {
            $supplier = Supplier::firstOrCreate(
                ['name' => 'ComLink Distribution'],
                [
                    'contact_no' => '+94 11 234 5678',
                    'email' => 'surveillance@comlink.lk',
                    'address' => 'Colombo 04, Sri Lanka',
                ]
            );
        }

        // 3. Ensure Parent Categories
        $parentCatsDef = [
            'cameras' => [
                'name' => 'Cameras',
                'image' => 'images/categories/bullet-cameras.webp',
                'description' => 'Security cameras, CCTV systems, DVRs, NVRs, and smart surveillance equipment.',
                'sort_order' => 8,
            ],
            'products' => [
                'name' => 'Products',
                'image' => 'images/categories/default.webp',
                'description' => 'Electronic accessories, display mounts, office hardware, and peripherals.',
                'sort_order' => 7,
            ],
            'networking' => [
                'name' => 'Networking',
                'image' => 'images/categories/routers.webp',
                'description' => 'Network switches, server rack cabinets, routers, and structured cabling solutions.',
                'sort_order' => 9,
            ],
        ];

        $parentModels = [];
        foreach ($parentCatsDef as $slug => $def) {
            $cat = Category::where('slug', $slug)
                ->orWhere(function ($q) use ($def) {
                    $q->whereNull('parent_id')
                        ->whereRaw('LOWER(name) = ?', [strtolower($def['name'])]);
                })
                ->first();

            if (!$cat) {
                $cat = Category::create([
                    'name' => $def['name'],
                    'slug' => $slug,
                    'parent_id' => null,
                    'image' => $def['image'],
                    'description' => $def['description'],
                    'is_visible' => true,
                    'sort_order' => $def['sort_order'],
                ]);
            } else {
                $cat->update(['is_visible' => true]);
            }
            $parentModels[$slug] = $cat;
        }

        // 4. Ensure Subcategories
        $subCatsDef = [
            'cctv-accessories' => [
                'parent_slug' => 'cameras',
                'name' => 'CCTV Accessories',
                'description' => 'Network cables, coaxial cables, power supplies, BNC connectors, and video baluns.',
                'image' => 'images/categories/bullet-cameras.webp',
                'sort_order' => 10,
            ],
            'dvr-accessories' => [
                'parent_slug' => 'cameras',
                'name' => 'DVR Accessories',
                'description' => 'Tamper-proof DVR security boxes, heavy lockboxes, and enclosure hardware.',
                'image' => 'images/categories/nvr.webp',
                'sort_order' => 11,
            ],
            'wall-mounts' => [
                'parent_slug' => 'products',
                'name' => 'Wall Mounts',
                'description' => 'Fixed, tilting, and universal wall mount brackets for monitors and TVs.',
                'image' => 'images/categories/default.webp',
                'sort_order' => 12,
            ],
            'network-cabinets' => [
                'parent_slug' => 'networking',
                'name' => 'Network Cabinets',
                'description' => '19-inch wall mount server and data rack cabinets (3U, 5U, 7U).',
                'image' => 'images/categories/routers.webp',
                'sort_order' => 1,
            ],
        ];

        $subCategoryModels = [];
        foreach ($subCatsDef as $subSlug => $def) {
            $parentCat = $parentModels[$def['parent_slug']];
            $subCat = Category::where('parent_id', $parentCat->id)
                ->where(function ($q) use ($subSlug, $def) {
                    $q->where('slug', $subSlug)
                        ->orWhere('name', $def['name']);
                })
                ->first();

            if (!$subCat) {
                $subCat = Category::create([
                    'name' => $def['name'],
                    'slug' => $subSlug,
                    'parent_id' => $parentCat->id,
                    'image' => $def['image'],
                    'description' => $def['description'],
                    'is_visible' => true,
                    'sort_order' => $def['sort_order'],
                ]);
            } else {
                $subCat->update([
                    'name' => $def['name'],
                    'slug' => $subSlug,
                    'parent_id' => $parentCat->id,
                    'is_visible' => true,
                    'sort_order' => $def['sort_order'],
                ]);
            }
            $subCategoryModels[$subSlug] = $subCat;
        }

        // 5. Ensure Product Specification Definitions
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
                        'category_id' => $subCat->parent_id,
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
                            'category_id' => $subCat->parent_id,
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
        $grn = Grn::updateOrCreate(
            ['grn_number' => 'GRN-2026-OTHER01'],
            [
                'grn_type' => 'direct',
                'direct_po_number' => 'DIR-PO-2026-OTHER01',
                'supplier_id' => $supplier->id,
                'notes' => 'Direct batch GRN for CCTV Accessories, Wall Mounts, DVR Enclosures, and Network Cabinets from HIKVISION-DPP.pdf',
                'sub_total' => 0.00,
            ]
        );

        // 7. Load Product Data JSON
        $dataPath = database_path('seeders/other_items_products_data.json');
        if (!File::exists($dataPath)) {
            $this->command->error("Data file not found: {$dataPath}");
            return;
        }

        $productsData = json_decode(File::get($dataPath), true);
        $totalGrnAmount = 0;

        $sourceBaseDir = base_path('ProductData/HIKVISION');
        $targetStorageBase = storage_path('app/public/products/accessories');
        if (!File::isDirectory($targetStorageBase)) {
            File::makeDirectory($targetStorageBase, 0755, true, true);
        }

        $seededCount = 0;

        foreach ($productsData as $data) {
            $subCatSlug = $data['sub_category_slug'];
            $subCatObj = $subCategoryModels[$subCatSlug] ?? null;
            $parentCat = $parentModels[$data['category_slug']] ?? null;
            $brandObj = $brandModels[$data['brand_slug']] ?? $brandModels['others'];

            if (!$subCatObj || !$parentCat) {
                $this->command->error("Category or Subcategory not found for: {$data['name']}");
                continue;
            }

            $price = (float) $data['price'];
            $salePrice = (float) $data['sale_price'];
            $costPrice = (float) $data['cost_price'];
            $initialStock = (int) ($data['stock'] ?? 20);

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
                    $images[] = 'products/accessories/' . $data['slug'] . '/' . $cleanName;
                    $imgIndex++;
                }
            }

            if (empty($images)) {
                $images = ['images/categories/default.webp'];
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
                    'warranty_duration' => $data['warranty_duration'] ?? 1,
                    'warranty_unit' => $data['warranty_unit'] ?? 'Years',
                    'images' => $images,
                    'main_specifications' => $data['main_specs'] ?? [],
                    'technical_specifications' => $data['tech_specs'] ?? [],
                    'is_visible' => true,
                    'is_featured' => false,
                ]
            );

            // Sync Categories: Parent and Subcategory
            $product->categories()->sync([$parentCat->id, $subCatObj->id]);

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
            $seededCount++;

            $this->command->line("  [OK] Seeded: {$data['name']} [{$parentCat->name} > {$subCatObj->name}] (Price: LKR " . number_format($salePrice, 2) . " | Stock: {$initialStock} | Images: " . count($images) . ")");
        }

        // Update GRN total
        $grn->update(['sub_total' => $totalGrnAmount]);

        // Sync Product Available Quantities across the system
        if (method_exists(Product::class, 'syncAllProductAvailableQuantities')) {
            Product::syncAllProductAvailableQuantities();
        }

        $this->command->info("Seeding complete! Seeded: {$seededCount} products. GRN Total: LKR " . number_format($totalGrnAmount, 2));
    }
}

