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

class LaptopDisplayProductsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding Laptop Display products...');

        // 1. Ensure Brand: Others
        $othersBrand = Brand::updateOrCreate(
            ['slug' => 'others'],
            [
                'name' => 'Others',
                'description' => 'Universal OEM & Replacement Laptop Displays & Accessories',
                'is_visible' => true,
            ]
        );

        // 2. Ensure Supplier: SMART PC COMPUTERS PVT LTD
        $supplier = Supplier::updateOrCreate(
            ['name' => 'SMART PC COMPUTERS PVT LTD'],
            [
                'contact_no' => '0912229521 / 0777900745',
                'email' => 'sales@smartpccomputers.lk',
                'address' => 'No 200 Wackwella Rd, Galle',
            ]
        );

        // 3. Ensure Main Category: Monitors & Displays (ID: 22)
        $monitorsCat = Category::where('slug', 'monitors-displays')
            ->orWhere('name', 'Monitors & Displays')
            ->first();

        if (!$monitorsCat) {
            $monitorsCat = Category::updateOrCreate(
                ['slug' => 'monitors-displays'],
                [
                    'name' => 'Monitors & Displays',
                    'parent_id' => null,
                    'image' => 'images/categories/monitors-displays.png',
                    'description' => 'Computer monitors, desktop displays, gaming screens, and replacement laptop display panels.',
                    'is_visible' => true,
                    'sort_order' => 3,
                ]
            );
        } else {
            $monitorsCat->update(['is_visible' => true]);
        }

        // 4. Ensure Subcategory: Displays under Monitors & Displays
        $displaysSubCat = Category::where('parent_id', $monitorsCat->id)
            ->where(function ($q) {
                $q->where('slug', 'displays')->orWhere('name', 'Displays');
            })
            ->first();

        if (!$displaysSubCat) {
            $displaysSubCat = Category::create([
                'name' => 'Displays',
                'slug' => 'displays',
                'parent_id' => $monitorsCat->id,
                'image' => 'images/categories/displays.webp',
                'description' => 'Replacement laptop display panels, LED/LCD screens, and display assemblies.',
                'is_visible' => true,
                'sort_order' => 1,
            ]);
        } else {
            $displaysSubCat->update([
                'name' => 'Displays',
                'slug' => 'displays',
                'parent_id' => $monitorsCat->id,
                'is_visible' => true,
            ]);
        }

        // 5. Ensure Product Specification Attributes for Displays
        $mainSpecTitles = [
            'Brand',
            'Screen Size',
            'Panel',
            'Resolution',
            'Connector',
            'Refresh Rate',
            'Surface Finish',
            'Mounting',
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
                    'category_id' => $monitorsCat->id,
                    'sub_category_id' => $displaysSubCat->id,
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
                        'category_id' => $monitorsCat->id,
                        'sub_category_id' => $displaysSubCat->id,
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
        $sourceBase = base_path('ProductData/Products-display');
        $destBase = storage_path('app/public/products/displays');
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
                $dbPath = 'products/displays/' . $relPath;
                $imageFileMap[$relPath] = $dbPath;
            }
        }

        $this->command->info('Copied ' . count($imageFileMap) . ' display images to storage/app/public/products/displays');

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
        $dataFile = __DIR__ . '/laptop_display_products_data.json';
        if (!File::exists($dataFile)) {
            $this->command->error("Data file not found: {$dataFile}");
            return;
        }

        $items = json_decode(File::get($dataFile), true);
        $this->command->info("Loaded " . count($items) . " items from laptop_display_products_data.json");

        // 8. Create Direct GRN Record to back Inventory
        $seedGrns = env('SEED_MOCK_GRNS', app()->environment('local'));
        $grn = null;
        if ($seedGrns) {
            $grn = Grn::updateOrCreate(
            ['grn_number' => 'GRN-2026-DISP001'],
            [
                'grn_type' => 'direct',
                'direct_po_number' => 'DIR-PO-2026-DISP01',
                'supplier_id' => $supplier->id,
                'notes' => 'Direct GRN batch for Replacement Laptop Displays & Screens from SMART PC COMPUTERS PVT LTD.',
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

            $skuBase = $data['model'] ?: $data['product_code'];
            $sku = $skuBase;
            $skuCounter = 1;
            while (Product::where('sku', $sku)->where('product_code', '!=', $data['product_code'])->exists()) {
                $sku = "{$skuBase}-{$skuCounter}";
                $skuCounter++;
            }

            $isFeatured = stripos($data['name'], 'IPS') !== false || stripos($data['name'], 'Narrow') !== false;

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

            // Sync Categories: Monitors & Displays + Displays subcategory
            $product->categories()->sync([$monitorsCat->id, $displaysSubCat->id]);

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

        $this->command->info("Successfully seeded {$seededCount} Laptop Display products with specifications, images, inventory, and GRN!");
    }
}
