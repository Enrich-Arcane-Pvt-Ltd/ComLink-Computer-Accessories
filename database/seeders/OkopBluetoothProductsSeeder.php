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

class OkopBluetoothProductsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding OKOP Bluetooth Speakers & Earbuds under Products Category...');

        // 1. Ensure Brand: OKOP
        $brand = Brand::updateOrCreate(
            ['slug' => 'okop'],
            [
                'name' => 'OKOP',
                'description' => 'OKOP Acoustic Audio, High-Power Party Speakers & Smart Wireless Earbuds',
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

        // 3. Ensure Main Category: Products (ID: 27)
        $productsCat = Category::where('slug', 'products')->first();
        if (!$productsCat) {
            $productsCat = Category::updateOrCreate(
                ['slug' => 'products'],
                [
                    'name' => 'Products',
                    'parent_id' => null,
                    'image' => 'images/categories/speakers.webp',
                    'description' => 'Consumer electronics, audio devices, smart accessories, and peripheral products.',
                    'is_visible' => true,
                    'sort_order' => 5,
                ]
            );
        } else {
            $productsCat->update(['is_visible' => true]);
        }

        // 4. Ensure Subcategories under Products: Bluetooth Speakers & Earbuds
        $subCategoriesDef = [
            'bluetooth-speakers' => [
                'name' => 'Bluetooth Speakers',
                'description' => 'Portable and high-power wireless Bluetooth speakers, RGB party boomboxes, and karaoke sets.',
                'image' => 'images/categories/speakers.webp',
                'sort_order' => 16,
                'main_specs' => [
                    'Speaker Type / Channel',
                    'Bluetooth Version & Range',
                    'Output Power (RMS)',
                    'Battery Capacity & Playtime',
                    'Audio Inputs & Connectivity',
                    'Lighting / RGB Effects',
                ],
            ],
            'earbuds' => [
                'name' => 'Earbuds',
                'description' => 'True Wireless Stereo (TWS) Bluetooth earbuds and headsets with high-definition audio.',
                'image' => 'images/categories/headsets.webp',
                'sort_order' => 17,
                'main_specs' => [
                    'Headset / Earbud Type',
                    'Bluetooth Version & Range',
                    'Driver Size & Audio Quality',
                    'Battery Life & Charging Case',
                    'Microphone & Calls',
                    'Charging Interface & Controls',
                ],
            ],
        ];

        $subCategoryModels = [];
        foreach ($subCategoriesDef as $slug => $def) {
            $subCat = Category::where('parent_id', $productsCat->id)
                ->where(function ($q) use ($slug, $def) {
                    $q->where('slug', $slug)->orWhere('name', $def['name']);
                })
                ->first();

            if (!$subCat) {
                $subCat = Category::create([
                    'name' => $def['name'],
                    'slug' => $slug,
                    'parent_id' => $productsCat->id,
                    'image' => $def['image'],
                    'description' => $def['description'],
                    'is_visible' => true,
                    'sort_order' => $def['sort_order'],
                ]);
            } else {
                $subCat->update([
                    'name' => $def['name'],
                    'slug' => $slug,
                    'parent_id' => $productsCat->id,
                    'is_visible' => true,
                    'sort_order' => $def['sort_order'],
                ]);
            }
            $subCategoryModels[$slug] = $subCat;

            // ProductSpecification registration for main specs
            foreach ($def['main_specs'] as $specTitle) {
                ProductSpecification::updateOrInsert(
                    [
                        'category_id' => $productsCat->id,
                        'sub_category_id' => $subCat->id,
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

            // Technical Specs registration
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

            foreach ($techSpecsDef as $subSec => $titles) {
                foreach ($titles as $st) {
                    ProductSpecification::updateOrInsert(
                        [
                            'category_id' => $productsCat->id,
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

        // 5. Direct GRN for Inventory
        $seedGrns = env('SEED_MOCK_GRNS', app()->environment('local'));
        $grn = null;
        if ($seedGrns) {
            $grn = Grn::updateOrCreate(
            ['grn_number' => 'GRN-2026-OKOP001'],
            [
                'grn_type' => 'direct',
                'direct_po_number' => 'DIR-PO-2026-OKOP01',
                'supplier_id' => $supplier->id,
                'notes' => 'Direct batch GRN for OKOP Bluetooth Speakers & TWS Earbuds',
                'sub_total' => 0.00,
            ]
        );
        }

        // 6. Load Product Data JSON
        $dataPath = database_path('seeders/okop_products_data.json');
        if (!File::exists($dataPath)) {
            $this->command->error("Data file not found: {$dataPath}");
            return;
        }

        $productsData = json_decode(File::get($dataPath), true);
        $totalGrnAmount = 0;

        $sourceBaseDir = base_path('ProductData/OKOP BLUETOOTH SPEAKER & EARBUDS');
        $targetStorageBase = storage_path('app/public/products/okop');
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
                    $images[] = 'products/okop/' . $data['slug'] . '/' . $cleanName;
                    $imgIndex++;
                }
            }

            // Fallback placeholder if no images found
            if (empty($images)) {
                $images = ['images/categories/speakers.webp'];
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

            // Sync Category: Products and Subcategory
            $product->categories()->sync([$productsCat->id, $subCatObj->id]);

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

        $this->command->info("All 16 OKOP products successfully seeded with GRN total: LKR {$totalGrnAmount}");
    }
}

