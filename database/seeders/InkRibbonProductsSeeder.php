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

class InkRibbonProductsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding Ink & Ribbon products under Products Category...');

        // 1. Ensure Brands
        $brands = [
            'ep-ink' => Brand::updateOrCreate(
                ['slug' => 'ep-ink'],
                [
                    'name' => 'EP-INK',
                    'description' => 'Epson Compatible High-Quality Refill Inks and Printing Consumables',
                    'is_visible' => true,
                ]
            ),
            'refill-ink' => Brand::updateOrCreate(
                ['slug' => 'refill-ink'],
                [
                    'name' => 'Refill-Ink',
                    'description' => 'Canon PIXMA MegaTank Compatible High-Yield Consumable Inks',
                    'is_visible' => true,
                ]
            ),
            'kiyo' => Brand::updateOrCreate(
                ['slug' => 'kiyo'],
                [
                    'name' => 'Kiyo',
                    'description' => 'High-Performance Commercial Grade Large Volume Refill Inks',
                    'is_visible' => true,
                ]
            ),
            'others' => Brand::updateOrCreate(
                ['slug' => 'others'],
                [
                    'name' => 'Others',
                    'description' => 'Universal OEM & Impact Printer Ribbon Consumables',
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
                    'address' => 'Peripherals & Consumables Distribution Center, Colombo 04',
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
                    'description' => 'Office equipment, computers, accessories, printers, ink, ribbons, and supplies.',
                    'is_visible' => true,
                    'sort_order' => 5,
                ]
            );
        } else {
            $productsCat->update(['is_visible' => true]);
        }

        // 4. Ensure Subcategory: Ink & Ribbons under Products
        $inkRibbonsSubCat = Category::where('parent_id', $productsCat->id)
            ->where(function ($q) {
                $q->where('slug', 'ink-ribbons')->orWhere('name', 'Ink & Ribbons');
            })
            ->first();

        if (!$inkRibbonsSubCat) {
            $inkRibbonsSubCat = Category::create([
                'name' => 'Ink & Ribbons',
                'slug' => 'ink-ribbons',
                'parent_id' => $productsCat->id,
                'image' => 'images/categories/ink-ribbons.webp',
                'description' => 'Refill ink bottles for Epson and Canon continuous ink systems, plus dot matrix and POS printer ribbons.',
                'is_visible' => true,
                'sort_order' => 10,
            ]);
        } else {
            $inkRibbonsSubCat->update([
                'name' => 'Ink & Ribbons',
                'slug' => 'ink-ribbons',
                'parent_id' => $productsCat->id,
                'is_visible' => true,
            ]);
        }

        // 5. Ensure Product Specification Attributes for Ink & Ribbons
        $mainSpecTitles = [
            'Consumable Type',
            'Ink / Ribbon Formulation',
            'Volume / Cartridge Length',
            'Page / Character Yield',
            'Compatible Printer Series',
            'Shelf Life / Durability',
            'Nozzle / Cassette Mechanism',
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
                    'sub_category_id' => $inkRibbonsSubCat->id,
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
                        'sub_category_id' => $inkRibbonsSubCat->id,
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
            ['grn_number' => 'GRN-2026-INK001'],
            [
                'grn_type' => 'direct',
                'direct_po_number' => 'DIR-PO-2026-INK01',
                'supplier_id' => $supplier->id,
                'notes' => 'Direct GRN batch for Ink & Ribbon Consumables (EP-INK, Refill-Ink, Kiyo, Others)',
                'sub_total' => 0.00,
            ]
        );
        }

        // 7. Load Product Data JSON
        $dataPath = database_path('seeders/ink_ribbon_products_data.json');
        if (!File::exists($dataPath)) {
            $this->command->error("Data file not found: {$dataPath}");
            return;
        }

        $productsData = json_decode(File::get($dataPath), true);
        $totalGrnAmount = 0;

        $sourceBaseDir = base_path('ProductData/INK & RIBBONS');
        $targetStorageBase = storage_path('app/public/products/ink-ribbons');
        if (!File::isDirectory($targetStorageBase)) {
            File::makeDirectory($targetStorageBase, 0755, true, true);
        }

        foreach ($productsData as $data) {
            $brandSlug = $data['brand_slug'];
            $brandObj = $brands[$brandSlug] ?? $brands['others'];

            $price = (float) $data['price'];
            $salePrice = (float) $data['sale_price'];
            $costPrice = (float) $data['cost_price'];
            $hasVariants = !empty($data['has_variants']);

            $productFolder = $sourceBaseDir . '/' . $data['folder'];
            $destFolder = $targetStorageBase . '/' . $data['slug'];
            if (!File::isDirectory($destFolder)) {
                File::makeDirectory($destFolder, 0755, true, true);
            }

            // Copy Images
            $images = [];
            $variantImages = [];

            if (File::isDirectory($productFolder)) {
                $files = File::files($productFolder);
                foreach ($files as $file) {
                    $fileName = $file->getFilename();
                    $ext = $file->getExtension();
                    if (!in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp'])) {
                        continue;
                    }

                    $destPath = $destFolder . '/' . $fileName;
                    File::copy($file->getRealPath(), $destPath);
                    $images[] = 'products/ink-ribbons/' . $data['slug'] . '/' . $fileName;
                }

                // If variants are present, map them to their specific images
                if ($hasVariants && !empty($data['variants'])) {
                    foreach ($data['variants'] as $v) {
                        $vImgFile = $v['image_file'] ?? null;
                        if ($vImgFile && File::exists($productFolder . '/' . $vImgFile)) {
                            $vDestPath = $destFolder . '/' . $vImgFile;
                            if (!File::exists($vDestPath)) {
                                File::copy($productFolder . '/' . $vImgFile, $vDestPath);
                            }
                            $variantImages[$v['color_name']] = 'products/ink-ribbons/' . $data['slug'] . '/' . $vImgFile;
                        }
                    }
                }
            }

            // Fallback placeholder if no images found
            if (empty($images)) {
                $images = ['images/categories/ink-ribbons.webp'];
            }

            // Calculate total initial stock
            $initialStock = 0;
            if ($hasVariants) {
                foreach ($data['variants'] as $v) {
                    $initialStock += (int) ($v['stock'] ?? 10);
                }
            } else {
                $initialStock = (int) ($data['stock'] ?? 20);
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
                    'warranty_duration' => $data['warranty_duration'] ?? 6,
                    'warranty_unit' => $data['warranty_unit'] ?? 'Months',
                    'images' => $images,
                    'main_specifications' => $data['main_specs'] ?? [],
                    'technical_specifications' => $data['tech_specs'] ?? [],
                    'is_visible' => true,
                    'is_featured' => false,
                ]
            );

            // Sync Category: Products and Ink & Ribbons
            $product->categories()->sync([$productsCat->id, $inkRibbonsSubCat->id]);

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

                    // Extract variant specific pricing & cost values if provided in JSON, falling back to parent
                    $vOrigPrice = isset($vData['original_price']) ? (float) $vData['original_price'] : (isset($vData['price']) ? (float) $vData['price'] : $price);
                    $vSalePrice = isset($vData['latest_selling_price']) ? (float) $vData['latest_selling_price'] : (isset($vData['selling_price']) ? (float) $vData['selling_price'] : (isset($vData['sale_price']) ? (float) $vData['sale_price'] : $salePrice));
                    $vCostPrice = isset($vData['grn_unit_cost']) ? (float) $vData['grn_unit_cost'] : (isset($vData['cost_price']) ? (float) $vData['cost_price'] : (isset($vData['grn_price']) ? (float) $vData['grn_price'] : $costPrice));
                    $vDiscount = isset($vData['discount_percentage']) ? (int) $vData['discount_percentage'] : (($vOrigPrice > 0 && $vSalePrice < $vOrigPrice) ? (int) round((($vOrigPrice - $vSalePrice) / $vOrigPrice) * 100) : 0);

                    $variant = ProductVariant::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'color_name' => $vColor,
                        ],
                        [
                            'variant_name' => $vData['variant_name'],
                            'condition' => 'Brand New',
                            'price' => $vOrigPrice,
                            'sale_price' => $vSalePrice,
                            'grn_price' => $vCostPrice,
                            'discount_percentage' => $vDiscount,
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
                            'price_per_unit' => $vCostPrice,
                            'total' => $vCostPrice * $vStock,
                            'product_condition' => 'Brand New',
                        ]
                    );

                    $totalGrnAmount += ($vCostPrice * $vStock);
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

            $this->command->line("  ✓ Seeded: {$data['name']} [{$brandObj->name}] (" . ($hasVariants ? count($data['variants']) . ' variants' : 'Simple') . ")");
        }

        // Update GRN total
        if ($grn) { $grn->update(['sub_total' => $totalGrnAmount]); }

        // Sync Product Available Quantities across the system
        if (method_exists(Product::class, 'syncAllProductAvailableQuantities')) {
            Product::syncAllProductAvailableQuantities();
        }

        $this->command->info("All 12 Ink & Ribbon products successfully seeded with GRN total: LKR {$totalGrnAmount}");
    }
}
