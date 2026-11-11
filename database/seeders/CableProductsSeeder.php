<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Grn;
use App\Models\GrnItem;
use App\Models\InventoryProduct;
use App\Models\Product;
use App\Models\ProductSpecification;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CableProductsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding Computer & Peripheral Cable products...');

        // 1. Ensure Brand: Others
        $othersBrand = Brand::updateOrCreate(
            ['slug' => 'others'],
            [
                'name' => 'Others',
                'description' => 'Universal OEM & Peripheral Connectivity Cables & Accessories',
                'is_visible' => true,
            ]
        );

        // 2. Ensure Supplier: TechZone Distributors (Pvt) Ltd
        $supplier = Supplier::where('name', 'like', '%TechZone%')->first();
        if (!$supplier) {
            $supplier = Supplier::firstOrCreate(
                ['name' => 'TechZone Distributors (Pvt) Ltd'],
                [
                    'contact_no' => '+94 11 234 5678',
                    'email' => 'sales@techzonedistributors.lk',
                    'address' => 'Peripherals Distribution Center, Colombo 04',
                ]
            );
        }

        // 3. Ensure Main Category: Peripherals (ID: 12)
        $peripheralsCat = Category::where('slug', 'peripherals')->first();
        if (!$peripheralsCat) {
            $peripheralsCat = Category::updateOrCreate(
                ['slug' => 'peripherals'],
                [
                    'name' => 'Peripherals',
                    'parent_id' => null,
                    'image' => 'images/categories/peripherals.png',
                    'description' => 'Gaming and office peripherals, keyboards, mice, headsets, cables, and accessories.',
                    'is_visible' => true,
                    'sort_order' => 2,
                ]
            );
        } else {
            $peripheralsCat->update(['is_visible' => true]);
        }

        // 4. Ensure Subcategory: Cables under Peripherals
        $cablesSubCat = Category::where('parent_id', $peripheralsCat->id)
            ->where(function ($q) {
                $q->where('slug', 'cables')->orWhere('name', 'Cables');
            })
            ->first();

        if (!$cablesSubCat) {
            $cablesSubCat = Category::create([
                'name' => 'Cables',
                'slug' => 'cables',
                'parent_id' => $peripheralsCat->id,
                'image' => 'images/categories/cables.webp',
                'description' => 'HDMI, VGA, Network Ethernet Cat6, USB Extension, Printer, and AC Power connectivity cables.',
                'is_visible' => true,
                'sort_order' => 6,
            ]);
        } else {
            $cablesSubCat->update([
                'name' => 'Cables',
                'slug' => 'cables',
                'parent_id' => $peripheralsCat->id,
                'is_visible' => true,
            ]);
        }

        // 5. Ensure Product Specification Attributes for Cables
        $mainSpecTitles = [
            'Cable Type',
            'Length',
            'Connector A',
            'Connector B',
            'Maximum Rating',
            'Conductor Material',
            'Jacket Material',
            'Shielding',
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

        $mOrder = 1;
        foreach ($mainSpecTitles as $t) {
            ProductSpecification::updateOrInsert(
                [
                    'category_id' => $peripheralsCat->id,
                    'sub_category_id' => $cablesSubCat->id,
                    'specification_type' => 'main',
                    'title' => $t,
                ],
                [
                    'sub_section' => null,
                    'sort_order' => $mOrder++,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        foreach ($techSpecsDef as $subSec => $specs) {
            $tOrder = 1;
            foreach ($specs as $st) {
                ProductSpecification::updateOrInsert(
                    [
                        'category_id' => $peripheralsCat->id,
                        'sub_category_id' => $cablesSubCat->id,
                        'specification_type' => 'technical',
                        'title' => $st,
                    ],
                    [
                        'sub_section' => $subSec,
                        'sort_order' => $tOrder++,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        // 6. Copy Product Images to Storage Public
        $sourceBase = base_path('ProductData/CABLE');
        $destBase = storage_path('app/public/products/cables');
        $imageFileMap = [];

        if (File::exists($sourceBase)) {
            $allFiles = File::allFiles($sourceBase);
            foreach ($allFiles as $file) {
                $relPath = str_replace('\\', '/', substr($file->getPathname(), strlen($sourceBase) + 1));
                $targetPath = $destBase . '/' . str_replace('/', DIRECTORY_SEPARATOR, $relPath);
                $targetDir = dirname($targetPath);
                if (!File::exists($targetDir)) {
                    File::makeDirectory($targetDir, 0755, true, true);
                }
                if (!File::exists($targetPath) || File::size($targetPath) !== $file->getSize()) {
                    File::copy($file->getPathname(), $targetPath);
                }
                $dbPath = 'products/cables/' . $relPath;
                $imageFileMap[$relPath] = $dbPath;
            }
        }

        $this->command->info('Copied ' . count($imageFileMap) . ' cable images to storage/app/public/products/cables');

        // Helper to retrieve images for a folder
        $getFolderImages = function(string $folder) use ($imageFileMap) {
            $found = [];
            $cleanFolder = strtolower(preg_replace('/\s+/', ' ', trim(str_replace('\\', '/', $folder))));
            foreach ($imageFileMap as $rel => $dbPath) {
                $cleanRel = strtolower(preg_replace('/\s+/', ' ', trim(str_replace('\\', '/', $rel))));
                if (str_starts_with($cleanRel, $cleanFolder . '/')) {
                    $found[] = $dbPath;
                }
            }
            sort($found);
            return array_slice($found, 0, 4);
        };

        // 7. Load Data File
        $dataFile = __DIR__ . '/cable_products_data.json';
        if (!File::exists($dataFile)) {
            $this->command->error("Data file not found: {$dataFile}");
            return;
        }

        $items = json_decode(File::get($dataFile), true);
        $this->command->info("Loaded " . count($items) . " items from cable_products_data.json");

        // 8. Create Direct GRN Record to back Inventory
        $seedGrns = env('SEED_MOCK_GRNS', app()->environment('local'));
        $grn = null;
        if ($seedGrns) {
            $grn = Grn::updateOrCreate(
            ['grn_number' => 'GRN-2026-CBL001'],
            [
                'grn_type' => 'direct',
                'direct_po_number' => 'DIR-PO-2026-CBL01',
                'supplier_id' => $supplier->id,
                'notes' => 'Direct GRN batch for Computer & Peripheral Cables and Connectivity Accessories.',
                'sub_total' => 0.00,
            ]
        );
        }

        $totalGrnAmount = 0.00;
        $stockCount = 25;
        $seededCount = 0;

        foreach ($items as $data) {
            $price = (float) $data['price'];
            $salePrice = (float) $data['sale_price'];
            $costPrice = (float) $data['cost_price'];

            $images = $getFolderImages($data['folder']);
            if (empty($images)) {
                $images = ['images/placeholder.png'];
            }

            $slugBase = Str::slug($data['name']);
            $slug = $slugBase;
            $sc = 1;
            while (Product::where('slug', $slug)->where('product_code', '!=', $data['product_code'])->exists()) {
                $slug = "{$slugBase}-{$sc}";
                $sc++;
            }

            $skuBase = $data['sku'] ?: $data['product_code'];
            $sku = $skuBase;
            $skuCounter = 1;
            while (Product::where('sku', $sku)->where('product_code', '!=', $data['product_code'])->exists()) {
                $sku = "{$skuBase}-{$skuCounter}";
                $skuCounter++;
            }

            $isFeatured = stripos($data['name'], '4K') !== false || stripos($data['name'], 'High Quality') !== false;

            $product = Product::updateOrCreate(
                ['product_code' => $data['product_code']],
                [
                    'brand_id' => $othersBrand->id,
                    'supplier_id' => $supplier->id,
                    'name' => $data['name'],
                    'slug' => $slug,
                    'sku' => $sku,
                    'description' => $data['description'],
                    'price' => $price,
                    'sale_price' => $salePrice,
                    'cost_price' => $costPrice,
                    'grn_price' => $costPrice,
                    'quantity' => ($seedGrns ? $stockCount : 0),
                    'inventory_stock' => ($seedGrns ? $stockCount : 0),
                    'show_quantity' => ($seedGrns ? $stockCount : 0),
                    'stock_added_count' => ($seedGrns ? $stockCount : 0),
                    'stock_added_date' => now(),
                    'security_stock' => 5,
                    'is_visible' => true,
                    'is_featured' => $isFeatured,
                    'type' => 'simple',
                    'condition' => 'Brand New',
                    'warranty_duration' => $data['warranty_duration'],
                    'warranty_unit' => $data['warranty_unit'],
                    'images' => $images,
                    'main_specifications' => $data['main_specs'] ?? [],
                    'technical_specifications' => $data['tech_specs'] ?? [],
                ]
            );

            // Sync Categories: Peripherals + Cables subcategory
            $product->categories()->sync([$peripheralsCat->id, $cablesSubCat->id]);

            // Sync InventoryProduct
            $invProduct = InventoryProduct::updateOrCreate(
                ['product_code' => $data['product_code']],
                [
                    'product_name' => $data['name'],
                    'product_image' => $images[0] ?? null,
                    'supplier_id' => $supplier->id,
                ]
            );

            // Create GrnItem for Inventory & Stock Analysis
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
                    'product_quantity' => $stockCount,
                    'price_per_unit' => $costPrice,
                    'total' => $costPrice * $stockCount,
                    'product_condition' => 'Brand New',
                ]
            );

            $totalGrnAmount += ($costPrice * $stockCount);
            }
            $seededCount++;
        }

        if ($grn) { $grn->update(['sub_total' => $totalGrnAmount]); }

        // Sync all product stock levels across system
        Product::syncAllProductAvailableQuantities();

        $this->command->info("Successfully seeded {$seededCount} Cable products with specifications, images, inventory, and GRN!");
    }
}

