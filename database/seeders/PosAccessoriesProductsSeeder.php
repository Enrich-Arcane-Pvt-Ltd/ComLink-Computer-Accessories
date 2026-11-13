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

class PosAccessoriesProductsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding POS Printers & Accessories products under Products Category...');

        // 1. Ensure Brands
        $brands = [
            'xprinter' => Brand::updateOrCreate(
                ['slug' => 'xprinter'],
                [
                    'name' => 'XPRINTER',
                    'description' => 'Xprinter Commercial Thermal Receipt & Barcode Printers',
                    'is_visible' => true,
                ]
            ),
            'epson' => Brand::updateOrCreate(
                ['slug' => 'epson'],
                [
                    'name' => 'EPSON',
                    'description' => 'Epson Industry Standard Point of Sale Hardware & Thermal Printers',
                    'is_visible' => true,
                ]
            ),
            'zebra' => Brand::updateOrCreate(
                ['slug' => 'zebra'],
                [
                    'name' => 'ZEBRA',
                    'description' => 'Zebra Enterprise Barcode Scanners & Thermal Printers',
                    'is_visible' => true,
                ]
            ),
            'dbl' => Brand::updateOrCreate(
                ['slug' => 'dbl'],
                [
                    'name' => 'DBL',
                    'description' => 'DBL Handheld & Desktop 2D/1D Optical Barcode Scanners',
                    'is_visible' => true,
                ]
            ),
            'others' => Brand::updateOrCreate(
                ['slug' => 'others'],
                [
                    'name' => 'Others',
                    'description' => 'Universal OEM POS Hardware, Cash Drawers & Consumables',
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
                    'address' => 'Peripherals & POS Equipment Distribution Center, Colombo 04',
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
                    'description' => 'Office equipment, computers, accessories, printers, POS hardware, and supplies.',
                    'is_visible' => true,
                    'sort_order' => 5,
                ]
            );
        } else {
            $productsCat->update(['is_visible' => true]);
        }

        // 4. Ensure Subcategory: POS Printers & Accessories under Products
        $posSubCat = Category::where('parent_id', $productsCat->id)
            ->where(function ($q) {
                $q->where('slug', 'pos-printers-accessories')
                  ->orWhere('name', 'POS Printers & Accessories')
                  ->orWhere('name', 'POS Printers and Accessories');
            })
            ->first();

        if (!$posSubCat) {
            $posSubCat = Category::create([
                'name' => 'POS Printers & Accessories',
                'slug' => 'pos-printers-accessories',
                'parent_id' => $productsCat->id,
                'image' => 'images/categories/pos-printers-accessories.webp',
                'description' => 'Point of sale thermal receipt printers, barcode label printers, 2D desktop & handheld barcode scanners, cash drawers, and POS supplies.',
                'is_visible' => true,
                'sort_order' => 11,
            ]);
        } else {
            $posSubCat->update([
                'name' => 'POS Printers & Accessories',
                'slug' => 'pos-printers-accessories',
                'parent_id' => $productsCat->id,
                'is_visible' => true,
            ]);
        }

        // 5. Ensure Product Specification Attributes for POS Printers & Accessories
        $mainSpecTitles = [
            'Device Type / Function',
            'Print / Scan Technology',
            'Print / Scan Speed',
            'Resolution',
            'Interface / Connectivity',
            'Media / Paper Capacity',
            'Power Source & Design',
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
                    'sub_category_id' => $posSubCat->id,
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
                        'sub_category_id' => $posSubCat->id,
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
        $seedGrns = env('SEED_MOCK_GRNS', app()->environment('local'));
        $grn = null;
        if ($seedGrns) {
            $grn = Grn::updateOrCreate(
            ['grn_number' => 'GRN-2026-POS001'],
            [
                'grn_type' => 'direct',
                'direct_po_number' => 'DIR-PO-2026-POS01',
                'supplier_id' => $supplier->id,
                'notes' => 'Direct batch GRN for POS Printers and Accessories (XPRINTER, EPSON, ZEBRA, DBL, Others)',
                'sub_total' => 0.00,
            ]
        );
        }

        // 7. Load Product Data JSON
        $dataPath = database_path('seeders/pos_accessories_products_data.json');
        if (!File::exists($dataPath)) {
            $this->command->error("Data file not found: {$dataPath}");
            return;
        }

        $productsData = json_decode(File::get($dataPath), true);
        $totalGrnAmount = 0;

        $sourceBaseDir = base_path('ProductData/POS PRINTERS AND ACCESSORIES');
        $targetStorageBase = storage_path('app/public/products/pos-printers-accessories');
        if (!File::isDirectory($targetStorageBase)) {
            File::makeDirectory($targetStorageBase, 0755, true, true);
        }

        // Read all folders in source directory to handle encoding/special characters
        $sourceFolders = [];
        if (File::isDirectory($sourceBaseDir)) {
            foreach (File::directories($sourceBaseDir) as $dirPath) {
                $sourceFolders[basename($dirPath)] = $dirPath;
            }
        }

        foreach ($productsData as $data) {
            $brandSlug = $data['brand_slug'];
            $brandObj = $brands[$brandSlug] ?? $brands['others'];

            $price = (float) $data['price'];
            $salePrice = (float) $data['sale_price'];
            $costPrice = (float) $data['cost_price'];
            $hasVariants = !empty($data['has_variants']);
            $productCode = $data['product_code'];

            // Match folder accurately
            $matchedDirPath = null;
            foreach ($sourceFolders as $fName => $fFullPath) {
                if ($productCode === 'XPR-XP80T-WIFI' && str_contains($fName, 'XP-80T') && str_contains($fName, '+')) {
                    $matchedDirPath = $fFullPath;
                    break;
                } elseif ($productCode === 'XPR-XP80T-USB' && str_contains($fName, 'XP-80T') && !str_contains($fName, '+')) {
                    $matchedDirPath = $fFullPath;
                    break;
                } elseif ($productCode === 'XPR-XP246B-BAR' && str_contains($fName, 'XP-246B')) {
                    $matchedDirPath = $fFullPath;
                    break;
                } elseif ($productCode === 'XPR-XPT80Q-REC' && str_contains($fName, 'XP-T80Q')) {
                    $matchedDirPath = $fFullPath;
                    break;
                } elseif ($productCode === 'XPR-XPT361U-COM' && str_contains($fName, 'XP-T361U')) {
                    $matchedDirPath = $fFullPath;
                    break;
                } elseif ($productCode === 'EPS-TMT81III-541' && str_contains($fName, 'TM-T81')) {
                    $matchedDirPath = $fFullPath;
                    break;
                } elseif ($productCode === 'ZEB-DS9308-2D' && str_contains($fName, 'DS9308')) {
                    $matchedDirPath = $fFullPath;
                    break;
                } elseif ($productCode === 'ZEB-ZD230TA-BAR' && str_contains($fName, 'ZD230TA')) {
                    $matchedDirPath = $fFullPath;
                    break;
                } elseif ($productCode === 'DBL-6255M-2D' && str_contains($fName, '6255M')) {
                    $matchedDirPath = $fFullPath;
                    break;
                } elseif ($productCode === 'DBL-6208RB-BT' && str_contains($fName, '6208RB')) {
                    $matchedDirPath = $fFullPath;
                    break;
                } elseif ($productCode === 'DBL-8602-DESK' && str_contains($fName, '8602')) {
                    $matchedDirPath = $fFullPath;
                    break;
                } elseif ($productCode === 'OTH-CASH-DRAWER' && str_contains($fName, 'CASH DRAWER')) {
                    $matchedDirPath = $fFullPath;
                    break;
                } elseif ($productCode === 'OTH-POS-PWR-24V3A' && str_contains($fName, 'POWER ADAPTER')) {
                    $matchedDirPath = $fFullPath;
                    break;
                } elseif ($productCode === 'OTH-WAX-RIB-110X300' && str_contains($fName, 'WAX RIBBON')) {
                    $matchedDirPath = $fFullPath;
                    break;
                } elseif ($productCode === 'OTH-THERM-ROLL-80MM' && str_contains($fName, 'THERMAL PAPER ROLL')) {
                    $matchedDirPath = $fFullPath;
                    break;
                }
            }

            $destFolder = $targetStorageBase . '/' . $data['slug'];
            if (!File::isDirectory($destFolder)) {
                File::makeDirectory($destFolder, 0755, true, true);
            }

            // Copy Images
            $images = [];
            $variantImages = [];

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
                    $images[] = 'products/pos-printers-accessories/' . $data['slug'] . '/' . $fileName;
                }

                // If variants are present, map them to their specific images
                if ($hasVariants && !empty($data['variants'])) {
                    foreach ($data['variants'] as $v) {
                        $vImgFile = $v['image_file'] ?? null;
                        if ($vImgFile && File::exists($matchedDirPath . '/' . $vImgFile)) {
                            $vDestPath = $destFolder . '/' . $vImgFile;
                            if (!File::exists($vDestPath)) {
                                File::copy($matchedDirPath . '/' . $vImgFile, $vDestPath);
                            }
                            $variantImages[$v['color_name']] = 'products/pos-printers-accessories/' . $data['slug'] . '/' . $vImgFile;
                        }
                    }
                }
            }

            // Fallback placeholder if no images found
            if (empty($images)) {
                $images = ['images/categories/pos-printers-accessories.webp'];
            }

            // Calculate total initial stock
            $initialStock = 0;
            if ($hasVariants) {
                foreach ($data['variants'] as $v) {
                    $initialStock += (int) ($v['stock'] ?? 10);
                }
            } else {
                $initialStock = (int) ($data['stock'] ?? 10);
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
                    'type' => $hasVariants ? 'variable' : 'simple',
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

            // Sync Category: Products and POS Printers & Accessories
            $product->categories()->sync([$productsCat->id, $posSubCat->id]);

            // Sync InventoryProduct
            $invProduct = InventoryProduct::updateOrCreate(
                ['product_code' => $data['product_code']],
                [
                    'product_name' => $data['name'],
                    'product_image' => $images[0],
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
                            'stock' => ($seedGrns ? $vStock : 0),
                            'inventory_stock' => ($seedGrns ? $vStock : 0),
                            'show_quantity' => ($seedGrns ? $vStock : 0),
                            'sku' => $vSku,
                            'image' => $vImg,
                        ]
                    );

                    // Create GrnItem for this variant
                    if ($seedGrns && $grn) {
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
                }

                // Remove legacy non-variant item if exists
                if ($grn) { GrnItem::where('grn_id', $grn->id)
                    ->where('product_code', $data['product_code'])
                    ->whereNull('product_variant_id')
                    ->whereNull('color_name')
                    ->delete(); }

                // Sync parent Product and InventoryProduct with the 1st color variation details
                $product->syncWithFirstVariant();
            } else {
                // Remove any leftover variants if simple
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
            }

            $this->command->line("  ✓ Seeded: {$data['name']} [{$brandObj->name}] (" . ($hasVariants ? count($data['variants']) . ' variants' : 'Stock: ' . $initialStock) . ")");
        }

        // Update GRN total
        if ($grn) { $grn->update(['sub_total' => $totalGrnAmount]); }

        // Sync Product Available Quantities across the system
        if (method_exists(Product::class, 'syncAllProductAvailableQuantities')) {
            Product::syncAllProductAvailableQuantities();
        }

        $this->command->info("All 15 POS Printers & Accessories products successfully seeded with GRN total: LKR {$totalGrnAmount}");
    }
}

