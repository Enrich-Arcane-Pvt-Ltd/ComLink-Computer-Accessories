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

class PostProductsSeeder extends Seeder
{
    /**
     * Seed 58 products from smartpc_products_list.xlsx Sheet 1
     */
    public function run(): void
    {
        $this->command->info("Starting Post Products Seeder (58 Products)...");

        // 1. Ensure Brands
        $brandsDef = [
            'kingston' => ['name' => 'Kingston', 'description' => 'World leader in memory products and technology solutions.', 'logo' => 'images/brands/others.png'],
            'kstar' => ['name' => 'KSTAR', 'description' => 'Global leader in power electronics, UPS systems, and new energy.', 'logo' => 'images/brands/others.png'],
            'hiksemi' => ['name' => 'Hiksemi', 'description' => 'Hikvision storage division offering industrial and consumer flash memory.', 'logo' => 'images/brands/others.png'],
            'blackbox' => ['name' => 'Blackbox', 'description' => 'Value and gaming desktop display monitors with high refresh rates.', 'logo' => 'images/brands/others.png'],
            'hikvision' => ['name' => 'HIKVISION', 'description' => 'World leading provider of security products and networking solutions.', 'logo' => 'images/brands/hikvision.png'],
            'viewtech' => ['name' => 'ViewTech', 'description' => 'Reliable desktop power supplies and electronic accessories.', 'logo' => 'images/brands/others.png'],
            'canon' => ['name' => 'Canon', 'description' => 'World leader in professional and consumer imaging and optical products.', 'logo' => 'images/brands/others.png'],
            'others' => ['name' => 'Others', 'description' => 'High quality universal hardware and computer accessories.', 'logo' => 'images/brands/others.png'],
            'oscoo' => ['name' => 'Oscoo', 'description' => 'Specialized manufacturer of solid state drives and memory modules.', 'logo' => 'images/brands/others.png'],
            'verbatim' => ['name' => 'Verbatim', 'description' => 'Globally trusted technology company specializing in data storage media.', 'logo' => 'images/brands/others.png'],
            'memoryghost' => ['name' => 'MemoryGhost', 'description' => 'Durable metallic USB flash drives and memory card solutions.', 'logo' => 'images/brands/others.png'],
            'samsung' => ['name' => 'Samsung', 'description' => 'World-renowned electronics and display technology manufacturer.', 'logo' => 'images/brands/others.png'],
            'ultrahero' => ['name' => 'UltraHero', 'description' => 'High-performance lithium coin cell batteries and power cells.', 'logo' => 'images/brands/others.png'],
            'pantum' => ['name' => 'Pantum', 'description' => 'Global developer and manufacturer of laser printers and toner cartridges.', 'logo' => 'images/brands/others.png'],
            'okop' => ['name' => 'OKOP', 'description' => 'Trendy wireless Bluetooth audio speakers, soundbars, and headsets.', 'logo' => 'images/brands/others.png'],
            'monova' => ['name' => 'Monova', 'description' => 'Modern high-refresh desktop monitors for office and multimedia.', 'logo' => 'images/brands/others.png'],
            'htech-power' => ['name' => 'Htech Power', 'description' => 'Specialized manufacturer of outdoor weatherproof camera power supplies.', 'logo' => 'images/brands/others.png'],
            'hp' => ['name' => 'HP', 'description' => 'Leading multinational information technology company.', 'logo' => 'images/brands/others.png'],
            'dell' => ['name' => 'DELL', 'description' => 'Global technology provider of laptops, backpacks, and enterprise solutions.', 'logo' => 'images/brands/others.png'],
            'lenovo' => ['name' => 'Lenovo', 'description' => 'World-leading PC, laptop, and enterprise hardware manufacturer.', 'logo' => 'images/brands/others.png'],
            'gigabyte' => ['name' => 'Gigabyte', 'description' => 'World-renowned manufacturer of motherboards and graphics cards.', 'logo' => 'images/brands/others.png'],
            'smart-view' => ['name' => 'Smart View', 'description' => 'Economical LED monitors designed for surveillance and office use.', 'logo' => 'images/brands/others.png'],
            'v-tek' => ['name' => 'V-TEK', 'description' => 'Heavy-duty sealed lead-acid replacement batteries for UPS systems.', 'logo' => 'images/brands/others.png'],
            'warner' => ['name' => 'Warner', 'description' => 'High-performance solid state drives and flash storage solutions.', 'logo' => 'images/brands/others.png'],
        ];

        $brandModels = [];
        foreach ($brandsDef as $slug => $b) {
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
                    'email' => 'sales@comlink.lk',
                    'address' => 'Colombo 04, Sri Lanka',
                ]
            );
        }

        // 3. Ensure Parent Category: Products
        $parentCat = Category::where('slug', 'products')
            ->orWhere(function ($q) {
                $q->whereNull('parent_id')
                    ->whereRaw('LOWER(name) = ?', ['products']);
            })
            ->first();

        if (!$parentCat) {
            $parentCat = Category::create([
                'name' => 'Products',
                'slug' => 'products',
                'parent_id' => null,
                'image' => 'images/categories/default.webp',
                'description' => 'Electronic accessories, display mounts, office hardware, and peripherals.',
                'is_visible' => true,
                'sort_order' => 7,
            ]);
        }

        // 4. Ensure Subcategories under Products
        $subCatsDef = [
            'flash-drives' => [
                'name' => 'Flash Drives',
                'description' => 'High speed USB flash drives, thumb drives, and swivel metal pen drives.',
                'image' => 'images/categories/default.webp',
                'sort_order' => 1,
            ],
            'ups' => [
                'name' => 'UPS',
                'description' => 'Line interactive uninterruptible power supplies and surge protectors.',
                'image' => 'images/categories/default.webp',
                'sort_order' => 2,
            ],
            'monitors-products' => [
                'name' => 'Monitors',
                'description' => 'Full HD, gaming, frameless, and office desktop LED display monitors.',
                'image' => 'images/categories/default.webp',
                'sort_order' => 4,
            ],
            'range-extenders' => [
                'name' => 'Range Extenders',
                'description' => 'Wi-Fi signal boosters, range extenders, and long-range HDMI extenders.',
                'image' => 'images/categories/routers.webp',
                'sort_order' => 5,
            ],
            'power-supplies' => [
                'name' => 'Power Supplies',
                'description' => 'ATX desktop computer power supplies and outdoor weatherproof camera adapters.',
                'image' => 'images/categories/default.webp',
                'sort_order' => 6,
            ],
            'printers' => [
                'name' => 'Printers',
                'description' => 'Monochrome laser printers, wireless printers, and all-in-one ink tank color printers.',
                'image' => 'images/categories/default.webp',
                'sort_order' => 7,
            ],
            'routers-adapters' => [
                'name' => 'Routers & Adapters',
                'description' => '4G LTE SIM routers, wireless adapters, USB desktop hubs, and audio receivers.',
                'image' => 'images/categories/routers.webp',
                'sort_order' => 8,
            ],
            'ssd-storage' => [
                'name' => 'Solid State Drives (SSD)',
                'description' => 'High speed 2.5-inch SATA III SSDs and M.2 PCIe NVMe solid state drives.',
                'image' => 'images/categories/default.webp',
                'sort_order' => 9,
            ],
            'batteries' => [
                'name' => 'Batteries',
                'description' => 'Rechargeable 12V UPS lead acid batteries and 3V lithium coin cell batteries.',
                'image' => 'images/categories/default.webp',
                'sort_order' => 10,
            ],
            'bluetooth-speakers' => [
                'name' => 'Bluetooth Speakers',
                'description' => 'Wireless portable Bluetooth speakers, desktop party boomboxes, and soundbars.',
                'image' => 'images/categories/default.webp',
                'sort_order' => 11,
            ],
            'laptop-bags-backpacks' => [
                'name' => 'Laptop Bags & Backpacks',
                'description' => 'Water-resistant laptop backpacks, briefcases, and padded shoulder carrying bags.',
                'image' => 'images/categories/default.webp',
                'sort_order' => 12,
            ],
            'laptops-notebooks' => [
                'name' => 'Laptops & Notebooks',
                'description' => 'Brand new Intel Core i5 laptops, portable notebooks, and ultrabooks.',
                'image' => 'images/categories/default.webp',
                'sort_order' => 14,
            ],
        ];

        $subCategoryModels = [];
        foreach ($subCatsDef as $subSlug => $def) {
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

        // 5. Ensure Tailored Specification Definitions for each Category & Sub Category
        (new ProductSpecificationSeeder())->run();

        // 6. Direct GRN for Inventory (Only in local environment or if SEED_MOCK_GRNS is enabled)
        $seedGrns = env('SEED_MOCK_GRNS', app()->environment('local'));
        $grn = null;
        if ($seedGrns) {
            $grn = Grn::updateOrCreate(
                ['grn_number' => 'GRN-2026-POST01'],
                [
                    'grn_type' => 'direct',
                    'direct_po_number' => 'DIR-PO-2026-POST01',
                    'supplier_id' => $supplier->id,
                    'notes' => 'Direct batch GRN for 58 Smart PC flyer products (SSDs, Monitors, Motherboards, Printers, Laptops, Accessories)',
                    'sub_total' => 0.00,
                ]
            );
        }

        // 7. Load Product Data JSON
        $dataPath = database_path('seeders/post_products_data.json');
        if (!File::exists($dataPath)) {
            $this->command->error("Data file not found: {$dataPath}");
            return;
        }

        $productsData = json_decode(File::get($dataPath), true);
        $totalGrnAmount = 0;

        $sourceImagesDir = base_path('ProductData/Post product Images');
        $targetStorageBase = storage_path('app/public/products/post_products');
        if (!File::isDirectory($targetStorageBase)) {
            File::makeDirectory($targetStorageBase, 0755, true, true);
        }

        $seededCount = 0;

        foreach ($productsData as $data) {
            $subCatSlug = $data['sub_category_slug'];
            $subCatObj = $subCategoryModels[$subCatSlug] ?? null;
            if (!$subCatObj) {
                $targetCatSlug = $data['category_slug'] ?? 'products';
                $targetParent = Category::where('slug', $targetCatSlug)->first() ?? $parentCat;
                $subCatObj = Category::where('parent_id', $targetParent->id)
                    ->where(function ($q) use ($subCatSlug, $data) {
                        $q->where('slug', $subCatSlug)
                            ->orWhere('name', $data['sub_category_name'] ?? '');
                    })
                    ->first();
            }
            $brandObj = $brandModels[$data['brand_slug']] ?? $brandModels['others'];

            if (!$subCatObj) {
                $this->command->error("Subcategory not found for: {$data['name']}");
                continue;
            }

            $price = (float) $data['price'];
            $salePrice = (float) $data['sale_price'];
            $costPrice = (float) $data['cost_price'];
            $initialStock = (int) ($data['stock'] ?? 20);

            // Copy Image
            $imageFile = $data['image_file'] ?? null;
            $imageFiles = $data['image_files'] ?? [];
            $imageFolder = $data['image_folder'] ?? null;
            $images = [];

            if (!empty($imageFiles) && is_array($imageFiles)) {
                $destFolder = $targetStorageBase . DIRECTORY_SEPARATOR . $data['slug'];
                if (!File::isDirectory($destFolder)) {
                    File::makeDirectory($destFolder, 0755, true, true);
                }

                foreach ($imageFiles as $imgIdx => $imgName) {
                    $sourceFilePath = $sourceImagesDir . ($imageFolder ? DIRECTORY_SEPARATOR . $imageFolder : '') . DIRECTORY_SEPARATOR . $imgName;
                    if (File::exists($sourceFilePath)) {
                        $ext = strtolower(pathinfo($imgName, PATHINFO_EXTENSION));
                        $cleanName = 'image_' . ($imgIdx + 1) . '.' . $ext;
                        $destPath = $destFolder . DIRECTORY_SEPARATOR . $cleanName;
                        File::copy($sourceFilePath, $destPath);
                        $images[] = 'products/post_products/' . $data['slug'] . '/' . $cleanName;
                    }
                }
            } elseif ($imageFile) {
                $sourceFilePath = $sourceImagesDir . DIRECTORY_SEPARATOR . $imageFile;
                if (File::exists($sourceFilePath)) {
                    $destFolder = $targetStorageBase . DIRECTORY_SEPARATOR . $data['slug'];
                    if (!File::isDirectory($destFolder)) {
                        File::makeDirectory($destFolder, 0755, true, true);
                    }

                    $ext = strtolower(pathinfo($imageFile, PATHINFO_EXTENSION));
                    $cleanName = 'image_1.' . $ext;
                    $destPath = $destFolder . DIRECTORY_SEPARATOR . $cleanName;
                    File::copy($sourceFilePath, $destPath);
                    $images[] = 'products/post_products/' . $data['slug'] . '/' . $cleanName;
                }
            }

            if (empty($images)) {
                $images = ['images/categories/default.webp'];
            }

            $stockQty = $seedGrns ? $initialStock : 0;

            // Create / Update Product
            $product = Product::where('slug', $data['slug'])
                ->orWhere('product_code', $data['product_code'])
                ->first();

            if (!$product) {
                $product = new Product();
            }

            $product->fill([
                'product_code' => $data['product_code'],
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
                'quantity' => $stockQty,
                'inventory_stock' => $stockQty,
                'show_quantity' => $stockQty,
                'stock_added_count' => $stockQty,
                'stock_added_date' => now(),
                'security_stock' => 5,
                'warranty_duration' => $data['warranty_duration'] ?? 1,
                'warranty_unit' => $data['warranty_unit'] ?? 'Years',
                'images' => $images,
                'main_specifications' => $data['main_specs'] ?? [],
                'technical_specifications' => $data['tech_specs'] ?? [],
                'is_visible' => true,
                'is_featured' => false,
            ]);
            $product->save();

            // Sync Categories: Parent and Subcategory
            $resolvedParentId = $subCatObj->parent_id ?? $parentCat->id;
            $product->categories()->sync([$resolvedParentId, $subCatObj->id]);

            // Sync InventoryProduct
            $invProduct = InventoryProduct::where('product_code', $data['product_code'])
                ->orWhere('product_name', $data['name'])
                ->first();

            if (!$invProduct) {
                $invProduct = new InventoryProduct();
            }

            $invProduct->fill([
                'product_code' => $data['product_code'],
                'product_name' => $data['name'],
                'product_image' => $images[0],
                'supplier_id' => $supplier->id,
            ]);
            $invProduct->save();

            // Clean up variants if any
            ProductVariant::where('product_id', $product->id)->delete();

            // Create or Update GrnItem (Only in local environment or if SEED_MOCK_GRNS is enabled)
            if ($seedGrns && $grn) {
                $grnItem = GrnItem::where('grn_id', $grn->id)
                    ->where(function ($q) use ($data, $invProduct) {
                        $q->where('product_code', $data['product_code'])
                            ->orWhere('inventory_product_id', $invProduct->id);
                    })->first();

                if (!$grnItem) {
                    $grnItem = new GrnItem();
                    $grnItem->grn_id = $grn->id;
                }

                $grnItem->fill([
                    'inventory_product_id' => $invProduct->id,
                    'product_code' => $data['product_code'],
                    'product_variant_id' => null,
                    'product_name' => $data['name'],
                    'color_name' => null,
                    'product_quantity' => $initialStock,
                    'price_per_unit' => $costPrice,
                    'total' => $costPrice * $initialStock,
                    'product_condition' => 'Brand New',
                ]);
                $grnItem->save();

                $totalGrnAmount += ($costPrice * $initialStock);
            }
            $seededCount++;

            $priceText = $salePrice > 0 ? "LKR " . number_format($salePrice, 2) : "LKR 0.00";
            $this->command->line("  [OK] Seeded: {$data['name']} [{$parentCat->name} > {$subCatObj->name}] (Price: {$priceText} | Stock: {$stockQty} | Img: {$images[0]})");
        }

        // Update GRN total
        if ($grn) {
            $grn->update(['sub_total' => $totalGrnAmount]);
        }

        // Sync Product Available Quantities across the system
        if (method_exists(Product::class, 'syncAllProductAvailableQuantities')) {
            Product::syncAllProductAvailableQuantities();
        }

        $this->command->info("Seeding complete! Successfully seeded {$seededCount} products. GRN Total: LKR " . number_format($totalGrnAmount, 2));
    }
}

