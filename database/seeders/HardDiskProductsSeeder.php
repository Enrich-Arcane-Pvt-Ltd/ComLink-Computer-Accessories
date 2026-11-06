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

class HardDiskProductsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding Surveillance Hard Disk products under PC Components Category...');

        // 1. Ensure Brands
        $brands = [
            'wd' => Brand::updateOrCreate(
                ['slug' => 'wd'],
                [
                    'name' => 'WD',
                    'description' => 'Western Digital Internal & Surveillance Hard Drives',
                    'is_visible' => true,
                ]
            ),
            'seagate' => Brand::updateOrCreate(
                ['slug' => 'seagate'],
                [
                    'name' => 'Seagate',
                    'description' => 'Seagate SkyHawk Surveillance & Enterprise Storage Drives',
                    'is_visible' => true,
                ]
            ),
            'others' => Brand::updateOrCreate(
                ['slug' => 'others'],
                [
                    'name' => 'Others',
                    'description' => 'Universal OEM Storage & Hardware Components',
                    'is_visible' => true,
                ]
            ),
        ];

        // 2. Ensure Supplier: SMART PC COMPUTERS PVT LTD (Matching PDF invoice header)
        $supplier = Supplier::where('name', 'like', '%SMART PC%')->first();
        if (!$supplier) {
            $supplier = Supplier::firstOrCreate(
                ['name' => 'SMART PC COMPUTERS PVT LTD'],
                [
                    'contact_no' => '0912229521 / 0765327731',
                    'email' => 'sales@smartpccomputers.lk',
                    'address' => 'No 200, Wackwella Rd, Galle',
                ]
            );
        }

        // 3. Ensure Main Category: PC Components (ID: 2)
        $pcComponentsCat = Category::where('slug', 'pc-components')->first();
        if (!$pcComponentsCat) {
            $pcComponentsCat = Category::updateOrCreate(
                ['slug' => 'pc-components'],
                [
                    'name' => 'PC Components',
                    'parent_id' => null,
                    'image' => 'images/categories/pc-components.png',
                    'description' => 'Core computer hardware, processors, motherboards, storage drives, and components.',
                    'is_visible' => true,
                    'sort_order' => 1,
                ]
            );
        } else {
            $pcComponentsCat->update(['is_visible' => true]);
        }

        // 4. Ensure Subcategory: Hard Disk (ID: 8) under PC Components
        $hardDiskSubCat = Category::where('parent_id', $pcComponentsCat->id)
            ->where(function ($q) {
                $q->where('slug', 'hard-disk')->orWhere('name', 'Hard Disk');
            })
            ->first();

        if (!$hardDiskSubCat) {
            $hardDiskSubCat = Category::create([
                'name' => 'Hard Disk',
                'slug' => 'hard-disk',
                'parent_id' => $pcComponentsCat->id,
                'image' => 'images/categories/hard-disk.webp',
                'description' => '3.5-inch desktop and 24/7 surveillance internal hard disk drives.',
                'is_visible' => true,
                'sort_order' => 6,
            ]);
        } else {
            $hardDiskSubCat->update([
                'name' => 'Hard Disk',
                'slug' => 'hard-disk',
                'parent_id' => $pcComponentsCat->id,
                'is_visible' => true,
            ]);
        }

        // 5. Ensure Product Specification Attributes for Hard Disk
        $mainSpecTitles = [
            'Brand',
            'HDD Color Type',
            'Storage Capacity',
            'Storage Type',
            'Interface',
            'Cache Size',
            'Rotational Speed (RPM)',
            'Workload Rating',
            'Cameras Supported',
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
                    'category_id' => $pcComponentsCat->id,
                    'sub_category_id' => $hardDiskSubCat->id,
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
                        'category_id' => $pcComponentsCat->id,
                        'sub_category_id' => $hardDiskSubCat->id,
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

        // 6. Direct GRN for Inventory
        $grn = Grn::updateOrCreate(
            ['grn_number' => 'GRN-2026-HDD001'],
            [
                'grn_type' => 'direct',
                'direct_po_number' => 'DIR-PO-2026-HDD01',
                'supplier_id' => $supplier->id,
                'notes' => 'Direct batch GRN for Surveillance Hard Drives (WD Purple & Seagate SkyHawk)',
                'sub_total' => 0.00,
            ]
        );

        // 7. Load Product Data JSON
        $dataPath = database_path('seeders/hard_disk_products_data.json');
        if (!File::exists($dataPath)) {
            $this->command->error("Data file not found: {$dataPath}");
            return;
        }

        $productsData = json_decode(File::get($dataPath), true);
        $totalGrnAmount = 0;

        $sourceBaseDir = base_path('ProductData/HARD DISK');
        $targetStorageBase = storage_path('app/public/products/hard-disk');
        if (!File::isDirectory($targetStorageBase)) {
            File::makeDirectory($targetStorageBase, 0755, true, true);
        }

        // Read all folders in source directory to handle case variations
        $sourceFolders = [];
        if (File::isDirectory($sourceBaseDir)) {
            foreach (File::directories($sourceBaseDir) as $dirPath) {
                $sourceFolders[strtolower(basename($dirPath))] = $dirPath;
            }
        }

        foreach ($productsData as $data) {
            $brandSlug = $data['brand_slug'];
            $brandObj = $brands[$brandSlug] ?? $brands['others'];

            $price = (float) $data['price'];
            $salePrice = (float) $data['sale_price'];
            $costPrice = (float) $data['cost_price'];
            $initialStock = (int) ($data['stock'] ?? 10);
            $targetFolder = strtolower($data['folder']);

            // Find folder
            $matchedDirPath = $sourceFolders[$targetFolder] ?? null;
            if (!$matchedDirPath) {
                foreach ($sourceFolders as $sName => $sFull) {
                    if (str_contains($sName, strtolower(substr($data['folder'], 0, 10)))) {
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
                    $images[] = 'products/hard-disk/' . $data['slug'] . '/' . $fileName;
                }
            }

            // Fallback placeholder if no images found
            if (empty($images)) {
                $images = ['images/categories/hard-disk.webp'];
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
                    'warranty_duration' => $data['warranty_duration'] ?? 2,
                    'warranty_unit' => $data['warranty_unit'] ?? 'Years',
                    'images' => $images,
                    'main_specifications' => $data['main_specs'] ?? [],
                    'technical_specifications' => $data['tech_specs'] ?? [],
                    'is_visible' => true,
                    'is_featured' => false,
                ]
            );

            // Sync Category: PC Components and Hard Disk
            $product->categories()->sync([$pcComponentsCat->id, $hardDiskSubCat->id]);

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

            $this->command->line("  ✓ Seeded: {$data['name']} [{$brandObj->name}] (Price: LKR " . number_format($salePrice, 2) . " | Stock: {$initialStock})");
        }

        // Update GRN total
        $grn->update(['sub_total' => $totalGrnAmount]);

        // Sync Product Available Quantities across the system
        if (method_exists(Product::class, 'syncAllProductAvailableQuantities')) {
            Product::syncAllProductAvailableQuantities();
        }

        $this->command->info("All 6 Surveillance Hard Disk products successfully seeded with GRN total: LKR {$totalGrnAmount}");
    }
}

