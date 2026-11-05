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

class AoasWeiboProductsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding AOAS & WEIBO Computer Peripherals products...');

        // 1. Ensure Brands: AOAS and WEIBO
        $aoasBrand = Brand::updateOrCreate(
            ['slug' => 'aoas'],
            [
                'name' => 'AOAS',
                'description' => 'AOAS - High-value gaming peripherals, wireless & Bluetooth mice, keyboards, and combo packs.',
                'is_visible' => true,
            ]
        );

        $weiboBrand = Brand::updateOrCreate(
            ['slug' => 'weibo'],
            [
                'name' => 'WEIBO',
                'description' => 'WEIBO - Professional gaming headsets, mechanical keyboards, and computer peripherals.',
                'is_visible' => true,
            ]
        );

        $brands = [
            'AOAS' => $aoasBrand,
            'WEIBO' => $weiboBrand,
        ];

        // 2. Ensure Supplier
        $supplier = Supplier::where('name', 'like', '%TechZone%')
            ->orWhere('name', 'like', '%AOAS%')
            ->first();

        if (!$supplier) {
            $supplier = Supplier::firstOrCreate(
                ['name' => 'TechZone Distributors (Pvt) Ltd'],
                [
                    'contact_person' => 'Supplier Operations Manager',
                    'email' => 'sales@techzonedistributors.lk',
                    'phone' => '+94 11 234 5678',
                    'address' => 'Peripherals Distribution Center, Colombo 04',
                    'status' => 'active',
                ]
            );
        }

        // 3. Ensure Main Category: Peripherals
        $peripheralsCat = Category::where('slug', 'peripherals')->first();
        if (!$peripheralsCat) {
            $peripheralsCat = Category::updateOrCreate(
                ['slug' => 'peripherals'],
                [
                    'name' => 'Peripherals',
                    'parent_id' => null,
                    'image' => 'images/categories/peripherals.png',
                    'description' => 'Gaming and office peripherals, keyboards, mice, headsets, mouse pads, and combo accessories.',
                    'is_visible' => true,
                    'sort_order' => 2,
                ]
            );
        }

        // 4. Ensure Subcategories under Peripherals
        $subCategories = [
            'mouse' => 'Mouse',
            'keyboards' => 'Keyboards',
            'headsets' => 'Headsets',
            'mouse-pad' => 'Mouse Pad',
            'combo-packs' => 'Combo Packs',
        ];

        $subModels = [];
        $order = 1;
        foreach ($subCategories as $slug => $name) {
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
                    'image' => "images/categories/{$slug}.webp",
                    'description' => "{$name} under Peripherals",
                    'is_visible' => true,
                    'sort_order' => $order++,
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

        // 5. Ensure Product Specification Attributes exist for these subcategories
        $subSpecTitles = [
            'mouse' => ['Resolution (DPI)', 'Connectivity', 'Sensor Type', 'Number of Buttons', 'Lighting / Backlight', 'Switch Durability'],
            'keyboards' => ['Keyboard Type', 'Switch Type', 'Layout / Keys', 'Connectivity', 'Backlight / RGB', 'Anti-Ghosting'],
            'headsets' => ['Audio Output', 'Connectivity', 'Microphone', 'Driver Diameter', 'Lighting / Backlight', 'Frequency Response'],
            'mouse-pad' => ['Dimensions / Size', 'Surface Material', 'Base Material', 'Edge Treatment', 'Lighting', 'Compatibility'],
            'combo-packs' => ['Package Includes', 'Connectivity', 'Keyboard Type', 'Mouse DPI', 'Silent Clicking', 'Wireless Range'],
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

            $mOrder = 1;
            foreach ($titles as $t) {
                ProductSpecification::updateOrInsert(
                    [
                        'category_id' => $peripheralsCat->id,
                        'sub_category_id' => $subObj->id,
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
                            'sub_category_id' => $subObj->id,
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
        }

        // 6. Copy Product Images from ProductData to Storage Public
        $sourceBase = base_path('ProductData/ASOS & WEIBO PRODUCT');
        $destBase = storage_path('app/public/products/aoas-weibo');
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
                $dbPath = 'products/aoas-weibo/' . $relPath;
                $imageFileMap[$relPath] = $dbPath;
            }
        }

        $this->command->info('Copied ' . count($imageFileMap) . ' AOAS & WEIBO images to storage/app/public/products/aoas-weibo');

        // Helper to retrieve images for a folder or specific model
        $getFolderImages = function(string $folder, ?string $modelFilter = null) use ($imageFileMap) {
            $found = [];
            $cleanFolder = strtolower(preg_replace('/\s+/', ' ', trim(str_replace('\\', '/', $folder))));
            foreach ($imageFileMap as $rel => $dbPath) {
                $cleanRel = strtolower(preg_replace('/\s+/', ' ', trim(str_replace('\\', '/', $rel))));
                if (str_starts_with($cleanRel, $cleanFolder . '/')) {
                    if ($modelFilter) {
                        if (stripos(basename($rel), $modelFilter) !== false) {
                            $found[] = $dbPath;
                        }
                    } else {
                        $found[] = $dbPath;
                    }
                }
            }
            if (empty($found) && $modelFilter) {
                foreach ($imageFileMap as $rel => $dbPath) {
                    $cleanRel = strtolower(preg_replace('/\s+/', ' ', trim(str_replace('\\', '/', $rel))));
                    if (str_starts_with($cleanRel, $cleanFolder . '/')) {
                        $found[] = $dbPath;
                    }
                }
            }
            sort($found);
            return array_slice($found, 0, 4);
        };

        // 7. Load Data File
        $dataFile = __DIR__ . '/aoas_weibo_products_data.json';
        if (!File::exists($dataFile)) {
            $this->command->error("Data file not found: {$dataFile}");
            return;
        }

        $items = json_decode(File::get($dataFile), true);
        $this->command->info("Loaded " . count($items) . " items from aoas_weibo_products_data.json");

        // 8. Create Direct GRN Record to back Inventory
        $grn = Grn::updateOrCreate(
            ['grn_number' => 'GRN-2026-AOAS001'],
            [
                'grn_type' => 'direct',
                'direct_po_number' => 'DIR-PO-2026-AOAS01',
                'supplier_id' => $supplier->id,
                'notes' => 'Direct GRN batch for AOAS & WEIBO Gaming & Office Peripherals Product Line.',
                'sub_total' => 0.00,
            ]
        );

        $totalGrnAmount = 0.00;
        $seededCount = 0;

        foreach ($items as $data) {
            $brandObj = $brands[$data['brand']] ?? $aoasBrand;
            $subSlug = $data['subcat'];
            $subCategory = $subModels[$subSlug] ?? null;

            $price = (float) $data['price'];
            $salePrice = (float) $data['sale_price'];
            $costPrice = (float) $data['cost_price'];

            $hasVariants = !empty($data['has_variants']) && !empty($data['variants']);
            $productType = $hasVariants ? 'variable' : 'simple';

            // Collect images
            $variantImages = [];
            if ($hasVariants) {
                foreach ($data['variants'] as $vData) {
                    $vImgFile = $vData['image_file'] ?? null;
                    if ($vImgFile) {
                        foreach ($imageFileMap as $rel => $dbPath) {
                            if (basename($rel) === $vImgFile) {
                                $variantImages[$vData['color_name']] = $dbPath;
                                break;
                            }
                        }
                    }
                }
                $images = array_values($variantImages);
                if (empty($images)) {
                    $images = $getFolderImages($data['folder']);
                }
                $stockCount = array_sum(array_column($data['variants'], 'stock'));
            } else {
                $images = $getFolderImages($data['folder']);
                if (empty($images)) {
                    $images = ['images/placeholder.png'];
                }
                $stockCount = 25;
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

            $isFeatured = stripos($data['name'], '7.1') !== false ||
                stripos($data['name'], 'combo') !== false ||
                stripos($data['name'], 'mechanical') !== false ||
                stripos($data['name'], 'gaming') !== false;

            $product = Product::updateOrCreate(
                ['product_code' => $data['product_code']],
                [
                    'brand_id' => $brandObj->id,
                    'supplier_id' => $supplier->id,
                    'name' => $data['name'],
                    'slug' => $slug,
                    'sku' => $sku,
                    'description' => $data['description'],
                    'price' => $price,
                    'sale_price' => $salePrice,
                    'cost_price' => $costPrice,
                    'grn_price' => $costPrice,
                    'quantity' => $stockCount,
                    'inventory_stock' => $stockCount,
                    'show_quantity' => $stockCount,
                    'stock_added_count' => $stockCount,
                    'stock_added_date' => now(),
                    'security_stock' => 5,
                    'is_visible' => true,
                    'is_featured' => $isFeatured,
                    'type' => $productType,
                    'condition' => 'Brand New',
                    'warranty_duration' => $data['warranty_duration'],
                    'warranty_unit' => $data['warranty_unit'],
                    'images' => $images,
                    'main_specifications' => $data['main_specs'] ?? [],
                    'technical_specifications' => $data['tech_specs'] ?? [],
                ]
            );

            // Sync Categories
            $catIds = [$peripheralsCat->id];
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

            if ($hasVariants) {
                // Sync ProductVariant records and individual GrnItems
                foreach ($data['variants'] as $vData) {
                    $vColor = $vData['color_name'];
                    $vImg = $variantImages[$vColor] ?? ($images[0] ?? null);
                    $vStock = (int) ($vData['stock'] ?? 10);
                    $vSku = $vData['sku'] ?? ($product->sku . '-' . strtoupper(substr($vColor, 0, 3)));

                    $variant = ProductVariant::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'color_name' => $vColor,
                        ],
                        [
                            'variant_name' => $vData['variant_name'],
                            'condition' => 'Brand New',
                            'price' => $price,
                            'sale_price' => $salePrice,
                            'grn_price' => $costPrice,
                            'discount_percentage' => ($price > 0 && $salePrice < $price) ? (int) round((($price - $salePrice) / $price) * 100) : 0,
                            'stock' => $vStock,
                            'inventory_stock' => $vStock,
                            'show_quantity' => $vStock,
                            'sku' => $vSku,
                            'image' => $vImg,
                        ]
                    );

                    // Create GrnItem for this variant
                    GrnItem::updateOrCreate(
                        [
                            'grn_id' => $grn->id,
                            'product_code' => $data['product_code'],
                            'color_name' => $vColor,
                        ],
                        [
                            'inventory_product_id' => $invProduct->id,
                            'product_variant_id' => $variant->id,
                            'product_name' => $vData['variant_name'],
                            'product_quantity' => $vStock,
                            'price_per_unit' => $costPrice,
                            'total' => $costPrice * $vStock,
                            'product_condition' => 'Brand New',
                        ]
                    );

                    $totalGrnAmount += ($costPrice * $vStock);
                }

                // Remove legacy generic GRN item without variant ID for this product
                GrnItem::where('grn_id', $grn->id)
                    ->where('product_code', $data['product_code'])
                    ->whereNull('product_variant_id')
                    ->whereNull('color_name')
                    ->delete();
            } else {
                // Remove any leftover variants if not variable
                ProductVariant::where('product_id', $product->id)->delete();

                // Create GrnItem so /admin/inventory calculates stock & GRN purchase cost
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

        $grn->update(['sub_total' => $totalGrnAmount]);

        // Sync all product stock levels across system
        Product::syncAllProductAvailableQuantities();

        $this->command->info("Successfully seeded {$seededCount} AOAS & WEIBO products with specifications, images, inventory, and GRN!");
    }
}
