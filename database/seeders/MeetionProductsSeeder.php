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

class MeetionProductsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding Meetion Gaming & Office Peripherals products...');

        // 1. Ensure Brand Meetion
        $brand = Brand::where('name', 'Meetion')
            ->orWhere('slug', 'meetion')
            ->first();

        if ($brand) {
            $brand->update([
                'name' => 'Meetion',
                'slug' => 'meetion',
                'description' => 'Meetion - Gaming Peripherals, Mechanical Keyboards, Ergonomic Mice, Headsets & Gaming Chairs.',
                'is_visible' => true,
            ]);
        } else {
            $brand = Brand::create([
                'name' => 'Meetion',
                'slug' => 'meetion',
                'description' => 'Meetion - Gaming Peripherals, Mechanical Keyboards, Ergonomic Mice, Headsets & Gaming Chairs.',
                'is_visible' => true,
            ]);
        }

        // 2. Ensure Supplier
        $supplier = Supplier::where('name', 'like', '%Meetion%')
            ->orWhere('name', 'like', '%TechZone%')
            ->first();

        if (!$supplier) {
            $supplier = Supplier::create([
                'name' => 'Meetion Distribution Lanka',
                'contact_person' => 'National Distributor',
                'email' => 'meetion@distributors.lk',
                'phone' => '+94 11 456 7890',
                'address' => 'Colombo, Sri Lanka',
            ]);
        }

        // 3. Ensure Main Categories (Peripherals & Products)
        $peripheralsCat = Category::where('slug', 'peripherals')->first();
        if (!$peripheralsCat) {
            $peripheralsCat = Category::create([
                'name' => 'Peripherals',
                'slug' => 'peripherals',
                'parent_id' => null,
                'description' => 'Gaming and office peripherals, keyboards, mice, headsets, and creator accessories.',
                'is_visible' => true,
                'sort_order' => 3,
            ]);
        } else {
            $peripheralsCat->update(['is_visible' => true]);
        }

        $productsCat = Category::where('slug', 'products')->first();
        if (!$productsCat) {
            $productsCat = Category::create([
                'name' => 'Products',
                'slug' => 'products',
                'parent_id' => null,
                'description' => 'Desktop computers, ergonomic chairs, power backup systems, and office equipment.',
                'is_visible' => true,
                'sort_order' => 6,
            ]);
        } else {
            $productsCat->update(['is_visible' => true]);
        }

        // 4. Subcategories under Peripherals
        $peripheralsSubDefs = [
            'mouse'        => 'Mouse',
            'keyboards'    => 'Keyboards',
            'headsets'     => 'Headsets',
            'mouse-pad'    => 'Mouse Pad',
            'speakers'     => 'Speakers',
            'webcam'       => 'Webcam',
            'cooling-pads' => 'Cooling Pads',
            'gaming-kits'  => 'Gaming Kits',
            'combo-packs'  => 'Combo Packs',
        ];

        $subModels = [];
        $pOrder = 1;
        foreach ($peripheralsSubDefs as $slug => $name) {
            $sub = Category::where('parent_id', $peripheralsCat->id)
                ->where(function ($q) use ($slug, $name) {
                    $q->where('slug', $slug)->orWhere('name', $name);
                })
                ->first();

            if (!$sub) {
                $sub = Category::create([
                    'name' => $name,
                    'slug' => $slug,
                    'parent_id' => $peripheralsCat->id,
                    'description' => "{$name} under Peripherals",
                    'is_visible' => true,
                    'sort_order' => $pOrder++,
                ]);
            } else {
                $sub->update([
                    'name' => $name,
                    'slug' => $slug,
                    'parent_id' => $peripheralsCat->id,
                    'is_visible' => true,
                ]);
            }
            $subModels[$slug] = $sub;
        }

        // Subcategory under Products: Chairs
        $chairsSub = Category::where('parent_id', $productsCat->id)
            ->where(function ($q) {
                $q->where('slug', 'chairs')->orWhere('name', 'Chairs');
            })
            ->first();

        if (!$chairsSub) {
            $chairsSub = Category::create([
                'name' => 'Chairs',
                'slug' => 'chairs',
                'parent_id' => $productsCat->id,
                'description' => 'Gaming chairs and ergonomic office chairs.',
                'is_visible' => true,
                'sort_order' => 4,
            ]);
        } else {
            $chairsSub->update([
                'name' => 'Chairs',
                'slug' => 'chairs',
                'parent_id' => $productsCat->id,
                'is_visible' => true,
            ]);
        }
        $subModels['chairs'] = $chairsSub;

        // 5. Seed ProductSpecification attributes
        $subSpecTitles = [
            'mouse' => ['Resolution (DPI)', 'Connectivity', 'Sensor Type', 'Number of Buttons', 'Lighting / Backlight', 'Switch Durability'],
            'keyboards' => ['Keyboard Type', 'Switch Type', 'Layout / Keys', 'Connectivity', 'Backlight / RGB', 'Anti-Ghosting'],
            'headsets' => ['Audio Output', 'Connectivity', 'Microphone', 'Driver Diameter', 'Lighting / Backlight', 'Frequency Response'],
            'mouse-pad' => ['Dimensions / Size', 'Surface Material', 'Base Material', 'Edge Treatment', 'Lighting', 'Compatibility'],
            'speakers' => ['Speaker Configuration', 'Total Output Power', 'Connectivity', 'Lighting / Backlight', 'Frequency Response', 'Volume Control'],
            'webcam' => ['Video Resolution', 'Field of View', 'Microphone', 'Focus Type', 'Connectivity', 'Privacy Features'],
            'cooling-pads' => ['Compatible Laptop Size', 'Fan Configuration', 'Fan Speed (RPM)', 'Lighting / Backlight', 'USB Ports', 'Elevation Angles'],
            'gaming-kits' => ['Kit Includes', 'Lighting / Backlight', 'Mouse DPI', 'Keyboard Keys', 'Headset Audio', 'Connectivity'],
            'combo-packs' => ['Package Includes', 'Connectivity', 'Keyboard Type', 'Mouse DPI', 'Silent Clicking', 'Wireless Range'],
            'chairs' => ['Chair Type', 'Upholstery Material', 'Reclining Mechanism', 'Armrest Type', 'Footrest', 'Gas Lift Cylinder'],
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

        foreach ($subSpecTitles as $subSlug => $titles) {
            $subObj = $subModels[$subSlug] ?? null;
            if (!$subObj) {
                continue;
            }
            $mainCatId = $subSlug === 'chairs' ? $productsCat->id : $peripheralsCat->id;

            $mOrder = 1;
            foreach ($titles as $t) {
                ProductSpecification::updateOrInsert(
                    [
                        'category_id' => $mainCatId,
                        'sub_category_id' => $subObj->id,
                        'specification_type' => 'main',
                        'title' => $t,
                    ],
                    [
                        'sub_section' => null,
                        'sort_order' => $mOrder++,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }

            foreach ($techSpecsDef as $sec => $secTitles) {
                $tOrder = 1;
                foreach ($secTitles as $st) {
                    ProductSpecification::updateOrInsert(
                        [
                            'category_id' => $mainCatId,
                            'sub_category_id' => $subObj->id,
                            'specification_type' => 'technical',
                            'sub_section' => $sec,
                            'title' => $st,
                        ],
                        [
                            'sort_order' => $tOrder++,
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }
            }
        }

        // 6. Copy Product Images from source directory to storage/app/public/products/meetion
        $sourceBase = base_path('ProductData/Products-MEETION-NEW-PRICE-LIST-JULY-2026');
        $destBase = storage_path('app/public/products/meetion');
        if (!File::exists($destBase)) {
            File::makeDirectory($destBase, 0755, true, true);
        }

        $imageFileMap = [];
        if (File::exists($sourceBase)) {
            $files = File::allFiles($sourceBase);
            foreach ($files as $file) {
                $relPath = str_replace('\\', '/', substr($file->getPathname(), strlen($sourceBase) + 1));
                $targetPath = $destBase . '/' . str_replace('/', DIRECTORY_SEPARATOR, $relPath);
                $targetDir = dirname($targetPath);
                if (!File::exists($targetDir)) {
                    File::makeDirectory($targetDir, 0755, true, true);
                }
                if (!File::exists($targetPath) || File::size($targetPath) !== $file->getSize()) {
                    File::copy($file->getPathname(), $targetPath);
                }
                $dbPath = 'products/meetion/' . $relPath;
                $imageFileMap[$relPath] = $dbPath;
            }
        }

        $this->command->info('Copied ' . count($imageFileMap) . ' Meetion images to storage/app/public/products/meetion');

        $getFolderImages = function(string $folder) use ($imageFileMap) {
            $found = [];
            $prefix = strtolower(trim(str_replace('\\', '/', $folder), '/')) . '/';
            foreach ($imageFileMap as $rel => $dbPath) {
                if (str_starts_with(strtolower($rel), $prefix)) {
                    $found[] = $dbPath;
                }
            }
            sort($found);
            return array_slice($found, 0, 4);
        };

        // 7. Load Data File
        $dataFile = __DIR__ . '/meetion_products_data.json';
        if (!File::exists($dataFile)) {
            $this->command->error("Data file not found: {$dataFile}");
            return;
        }

        $items = json_decode(File::get($dataFile), true);
        $this->command->info("Loaded " . count($items) . " items from meetion_products_data.json");

        // 8. Create Direct GRN Record to back Inventory
        $seedGrns = env('SEED_MOCK_GRNS', app()->environment('local'));
        $grn = null;
        if ($seedGrns) {
            $grn = Grn::updateOrCreate(
            ['grn_number' => 'GRN-2026-MEETION001'],
            [
                'grn_type' => 'direct',
                'direct_po_number' => 'DIR-PO-2026-MEETION01',
                'supplier_id' => $supplier->id,
                'notes' => 'Direct GRN batch for MEETION Gaming & Office Peripherals Product Line.',
                'sub_total' => 0.00,
            ]
        );
        }

        $totalGrnAmount = 0.00;
        $seededCount = 0;

        foreach ($items as $data) {
            $subSlug = $data['subcat'];
            $catSlug = $data['cat'];
            $parentCat = ($catSlug === 'products') ? $productsCat : $peripheralsCat;
            $subCategory = $subModels[$subSlug] ?? null;

            // Fetch images
            $images = $getFolderImages($data['folder']);
            if (empty($images)) {
                $images = ['images/placeholder.png'];
            }

            $stockCount = 25;
            $price = (float) $data['price'];
            $salePrice = (float) $data['sale_price'];
            $costPrice = (float) $data['cost_price'];

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

            $isFeatured = in_array($subSlug, ['chairs', 'gaming-kits', 'cooling-pads']) ||
                stripos($data['name'], 'mechanical') !== false ||
                stripos($data['name'], 'rgb') !== false ||
                stripos($data['name'], 'ergonomic') !== false;

            $product = Product::updateOrCreate(
                ['product_code' => $data['product_code']],
                [
                    'brand_id' => $brand->id,
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

            // Sync Categories
            $catIds = [$parentCat->id];
            if ($subCategory) {
                $catIds[] = $subCategory->id;
            }
            $product->categories()->sync($catIds);

            // Sync InventoryProduct
            $invProduct = InventoryProduct::updateOrCreate(
                ['product_code' => $data['product_code']],
                [
                    'product_name' => $data['name'],
                    'product_image' => $images[0] ?? null,
                    'supplier_id' => $supplier->id,
                ]
            );

            // Create GrnItem so /admin/inventory calculates stock & GRN purchase cost
            if ($seedGrns && $grn) {
                GrnItem::updateOrCreate(
                [
                    'grn_id' => $grn->id,
                    'product_code' => $data['product_code'],
                ],
                [
                    'inventory_product_id' => $invProduct->id,
                    'product_name' => $data['name'],
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

        $this->command->info("Successfully seeded {$seededCount} MEETION products with specifications, images, inventory, and GRN!");
    }
}
