<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShopController extends Controller
{
    /**
     * Display shop / category catalog
     */
    public function index(Request $request): Response
    {
        $categorySlug = $request->query('category');
        $subcategorySlug = $request->query('subcategory');

        return $this->renderCatalog($request, $categorySlug, $subcategorySlug);
    }

    /**
     * Display category by route slug
     */
    public function category(Request $request, string $category): Response
    {
        return $this->renderCatalog($request, $category, $request->query('subcategory'));
    }

    /**
     * Display subcategory by route slug
     */
    public function subcategory(Request $request, string $category, string $subcategory): Response
    {
        return $this->renderCatalog($request, $category, $subcategory);
    }

    /**
     * Helper to render the unified Shop/Category view
     */
    protected function renderCatalog(Request $request, ?string $categorySlug, ?string $subcategorySlug): Response
    {
        // 1. Fetch all main categories and their active subcategories
        $categories = Category::main()
            ->visible()
            ->orderBy('sort_order', 'asc')
            ->with(['children' => function ($query) {
                $query->visible()->orderBy('sort_order', 'asc');
            }])
            ->get()
            ->map(function ($cat) {
                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                    'description' => $cat->description,
                    'image' => $cat->image_url,
                    'count' => $cat->products()->count(),
                    'subcategories' => $cat->children->map(function ($child) {
                        return [
                            'id' => $child->id,
                            'name' => $child->name,
                            'slug' => $child->slug,
                            'description' => $child->description,
                            'image' => $child->image_url,
                            'count' => $child->products()->count(),
                        ];
                    })->values()->all(),
                ];
            });

        // 2. Identify active category and active subcategory if requested
        $activeCategory = null;
        $activeSubCategory = null;

        if ($categorySlug) {
            // Check if slug matches a main category
            $matchedMain = $categories->firstWhere('slug', $categorySlug);

            if ($matchedMain) {
                $activeCategory = $matchedMain;
            } else {
                // If not found in main, check if the slug is actually a subcategory
                foreach ($categories as $main) {
                    $matchedSub = collect($main['subcategories'])->firstWhere('slug', $categorySlug);
                    if ($matchedSub) {
                        $activeCategory = $main;
                        $activeSubCategory = $matchedSub;
                        break;
                    }
                }
            }
        }

        // Check if secondary subcategory parameter was provided
        if ($subcategorySlug && $activeCategory) {
            $matchedSub = collect($activeCategory['subcategories'])->firstWhere('slug', $subcategorySlug);
            if ($matchedSub) {
                $activeSubCategory = $matchedSub;
            }
        }

        // 3. Query database products
        $dbProductsQuery = Product::where('is_visible', true)
            ->with(['brand', 'categories']);

        if ($activeSubCategory) {
            $subId = $activeSubCategory['id'];
            $dbProductsQuery->whereHas('categories', function ($q) use ($subId) {
                $q->where('categories.id', $subId);
            });
        } elseif ($activeCategory) {
            $catId = $activeCategory['id'];
            $subIds = collect($activeCategory['subcategories'])->pluck('id')->all();
            $targetCategoryIds = array_merge([$catId], $subIds);

            $dbProductsQuery->whereHas('categories', function ($q) use ($targetCategoryIds) {
                $q->whereIn('categories.id', $targetCategoryIds);
            });
        }

        $search = $request->query('q');
        if (!empty($search)) {
            $dbProductsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');
        if ($minPrice !== null && is_numeric($minPrice)) {
            $dbProductsQuery->where('price', '>=', (float) $minPrice);
        }
        if ($maxPrice !== null && is_numeric($maxPrice)) {
            $dbProductsQuery->where('price', '<=', (float) $maxPrice);
        }

        $sort = $request->query('sort', 'featured');
        switch ($sort) {
            case 'price_asc':
                $dbProductsQuery->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $dbProductsQuery->orderBy('price', 'desc');
                break;
            case 'newest':
                $dbProductsQuery->orderBy('created_at', 'desc');
                break;
            default:
                $dbProductsQuery->orderBy('is_featured', 'desc')->orderBy('created_at', 'desc');
                break;
        }

        $dbProducts = $dbProductsQuery->get()->map(function ($p) {
            $catName = $p->categories->first()?->name ?? 'Accessories';
            return [
                'id' => $p->id,
                'name' => $p->name,
                'image' => !empty($p->images) && is_array($p->images) ? $p->images[0] : '/images/products/sample-keyboard.jpg',
                'category' => $catName,
                'subCategory' => $p->categories->count() > 1 ? $p->categories->last()?->name : $catName,
                'originalPrice' => (float) $p->price,
                'discountPercent' => $p->sale_price ? max(0, round((($p->price - $p->sale_price) / $p->price) * 100)) : 0,
                'inStock' => ($p->quantity ?? 0) > 0,
                'brand' => $p->brand?->name ?? 'Generic',
                'rating' => 4.8,
                'reviews' => 24,
            ];
        });

        // If no products in database yet, load high quality mock catalog items
        $condition = $request->query('condition', 'all');
        $brand = $request->query('brand');
        $allQueryParams = $request->all();

        $products = $dbProducts->isNotEmpty()
            ? $dbProducts->values()->all()
            : $this->getMockCatalogProducts($activeCategory, $activeSubCategory, $search, $sort, $minPrice, $maxPrice, $condition, $brand);

        return Inertia::render('Shop/Index', [
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'activeSubCategory' => $activeSubCategory,
            'products' => $products,
            'filters' => array_merge([
                'category' => $activeCategory['slug'] ?? null,
                'subcategory' => $activeSubCategory['slug'] ?? null,
                'q' => $search ?? '',
                'sort' => $sort,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'in_stock' => $request->boolean('in_stock', false),
                'condition' => $condition,
            ], $allQueryParams),
        ]);
    }

    /**
     * Provide rich default products matching requested categories
     */
    protected function getMockCatalogProducts(?array $activeCat, ?array $activeSub, ?string $search, string $sort, $minPrice, $maxPrice, string $condition = 'all', ?string $brand = null): array
    {
        $all = [
            // PC Components - Processors
            [
                'id' => 101,
                'name' => 'Intel Core i9-14900K 24-Core 6.0GHz LGA1700 Desktop Processor',
                'image' => '/images/products/sample-keyboard.jpg',
                'category' => 'PC Components',
                'categorySlug' => 'pc-components',
                'subCategory' => 'Processors',
                'subCategorySlug' => 'processors',
                'originalPrice' => 589.99,
                'discountPercent' => 8,
                'inStock' => true,
                'brand' => 'Intel',
                'rating' => 4.9,
                'reviews' => 42,
            ],
            [
                'id' => 102,
                'name' => 'AMD Ryzen 7 7800X3D 8-Core 16-Thread Gaming Processor',
                'image' => '/images/products/sample-keyboard.jpg',
                'category' => 'PC Components',
                'categorySlug' => 'pc-components',
                'subCategory' => 'Processors',
                'subCategorySlug' => 'processors',
                'originalPrice' => 449.00,
                'discountPercent' => 10,
                'inStock' => true,
                'brand' => 'AMD',
                'rating' => 5.0,
                'reviews' => 88,
            ],
            // PC Components - Motherboards
            [
                'id' => 103,
                'name' => 'MSI MAG B650 TOMAHAWK WIFI AM5 ATX Gaming Motherboard',
                'image' => '/images/products/sample-keyboard.jpg',
                'category' => 'PC Components',
                'categorySlug' => 'pc-components',
                'subCategory' => 'Motherboards',
                'subCategorySlug' => 'motherboards',
                'originalPrice' => 219.99,
                'discountPercent' => 12,
                'inStock' => true,
                'brand' => 'MSI',
                'rating' => 4.7,
                'reviews' => 31,
            ],
            [
                'id' => 104,
                'name' => 'ASUS ROG Strix Z790-E Gaming WiFi II LGA 1700 Motherboard',
                'image' => '/images/products/sample-keyboard.jpg',
                'category' => 'PC Components',
                'categorySlug' => 'pc-components',
                'subCategory' => 'Motherboards',
                'subCategorySlug' => 'motherboards',
                'originalPrice' => 479.99,
                'discountPercent' => 0,
                'inStock' => true,
                'brand' => 'ASUS',
                'rating' => 4.8,
                'reviews' => 19,
            ],
            // PC Components - Graphic Cards
            [
                'id' => 105,
                'name' => 'NVIDIA GeForce RTX 4080 Super 16GB GDDR6X Graphics Card',
                'image' => '/images/products/sample-keyboard.jpg',
                'category' => 'PC Components',
                'categorySlug' => 'pc-components',
                'subCategory' => 'Graphic Cards',
                'subCategorySlug' => 'graphic-cards',
                'originalPrice' => 999.99,
                'discountPercent' => 5,
                'inStock' => true,
                'brand' => 'NVIDIA',
                'rating' => 4.9,
                'reviews' => 56,
            ],
            [
                'id' => 106,
                'name' => 'ASUS TUF Gaming Radeon RX 7900 XTX OC Edition 24GB GDDR6',
                'image' => '/images/products/sample-keyboard.jpg',
                'category' => 'PC Components',
                'categorySlug' => 'pc-components',
                'subCategory' => 'Graphic Cards',
                'subCategorySlug' => 'graphic-cards',
                'originalPrice' => 979.00,
                'discountPercent' => 0,
                'inStock' => false,
                'brand' => 'ASUS',
                'rating' => 4.8,
                'reviews' => 22,
            ],
            // PC Components - Coolers
            [
                'id' => 107,
                'name' => 'NZXT Kraken Elite 360 RGB LCD All-In-One Liquid CPU Cooler',
                'image' => '/images/products/sample-keyboard.jpg',
                'category' => 'PC Components',
                'categorySlug' => 'pc-components',
                'subCategory' => 'Coolers',
                'subCategorySlug' => 'coolers',
                'originalPrice' => 279.99,
                'discountPercent' => 15,
                'inStock' => true,
                'brand' => 'NZXT',
                'rating' => 4.9,
                'reviews' => 64,
            ],
            // PC Components - SSD & Storage
            [
                'id' => 108,
                'name' => 'Samsung 990 PRO 2TB PCIe Gen4 NVMe M.2 Internal SSD',
                'image' => '/images/products/sample-keyboard.jpg',
                'category' => 'PC Components',
                'categorySlug' => 'pc-components',
                'subCategory' => 'SSD',
                'subCategorySlug' => 'ssd',
                'originalPrice' => 179.99,
                'discountPercent' => 18,
                'inStock' => true,
                'brand' => 'Samsung',
                'rating' => 5.0,
                'reviews' => 110,
            ],
            [
                'id' => 109,
                'name' => 'Corsair Vengeance RGB DDR5 32GB (2x16GB) 6000MHz CL30 RAM',
                'image' => '/images/products/sample-keyboard.jpg',
                'category' => 'PC Components',
                'categorySlug' => 'pc-components',
                'subCategory' => 'Memory Cards',
                'subCategorySlug' => 'memory',
                'originalPrice' => 124.99,
                'discountPercent' => 10,
                'inStock' => true,
                'brand' => 'Corsair',
                'rating' => 4.8,
                'reviews' => 73,
            ],
            [
                'id' => 110,
                'name' => 'Corsair RM850x 850W 80 PLUS Gold Fully Modular Power Supply',
                'image' => '/images/products/sample-keyboard.jpg',
                'category' => 'PC Components',
                'categorySlug' => 'pc-components',
                'subCategory' => 'Power Supply',
                'subCategorySlug' => 'power-supply',
                'originalPrice' => 149.99,
                'discountPercent' => 0,
                'inStock' => true,
                'brand' => 'Corsair',
                'rating' => 4.9,
                'reviews' => 45,
            ],
            [
                'id' => 111,
                'name' => 'Arctic MX-6 High-Performance Carbon Thermal Paste 4g',
                'image' => '/images/products/sample-keyboard.jpg',
                'category' => 'PC Components',
                'categorySlug' => 'pc-components',
                'subCategory' => 'Thermal Paste',
                'subCategorySlug' => 'thermal-paste',
                'originalPrice' => 12.99,
                'discountPercent' => 0,
                'inStock' => true,
                'brand' => 'Arctic',
                'rating' => 4.9,
                'reviews' => 140,
            ],
            // Peripherals
            [
                'id' => 201,
                'name' => 'Redragon K552 Kumara RGB Mechanical Gaming Keyboard',
                'image' => '/images/products/sample-keyboard.jpg',
                'category' => 'Peripherals',
                'categorySlug' => 'peripherals',
                'subCategory' => 'Keyboards',
                'subCategorySlug' => 'keyboards',
                'originalPrice' => 89.99,
                'discountPercent' => 15,
                'inStock' => true,
                'brand' => 'Redragon',
                'rating' => 4.7,
                'reviews' => 95,
            ],
            [
                'id' => 202,
                'name' => 'Logitech G502 HERO High-Performance RGB Gaming Mouse',
                'image' => '/images/products/sample-keyboard.jpg',
                'category' => 'Peripherals',
                'categorySlug' => 'peripherals',
                'subCategory' => 'Mouse',
                'subCategorySlug' => 'mouse',
                'originalPrice' => 49.99,
                'discountPercent' => 20,
                'inStock' => true,
                'brand' => 'Logitech',
                'rating' => 4.9,
                'reviews' => 210,
            ],
            [
                'id' => 203,
                'name' => 'SteelSeries Arctis Nova Pro Wireless Multi-System Headset',
                'image' => '/images/products/sample-keyboard.jpg',
                'category' => 'Peripherals',
                'categorySlug' => 'peripherals',
                'subCategory' => 'Headsets',
                'subCategorySlug' => 'headsets',
                'originalPrice' => 349.99,
                'discountPercent' => 10,
                'inStock' => true,
                'brand' => 'SteelSeries',
                'rating' => 4.8,
                'reviews' => 54,
            ],
            [
                'id' => 204,
                'name' => 'Razer Gigantus V2 XXL Thick Cloth Gaming Mouse Pad',
                'image' => '/images/products/sample-keyboard.jpg',
                'category' => 'Peripherals',
                'categorySlug' => 'peripherals',
                'subCategory' => 'Mouse Pad',
                'subCategorySlug' => 'mouse-pad',
                'originalPrice' => 34.99,
                'discountPercent' => 0,
                'inStock' => true,
                'brand' => 'Razer',
                'rating' => 4.6,
                'reviews' => 38,
            ],
            [
                'id' => 205,
                'name' => 'Elgato Stream Deck MK.2 15 LCD Keys Studio Controller',
                'image' => '/images/products/sample-keyboard.jpg',
                'category' => 'Peripherals',
                'categorySlug' => 'peripherals',
                'subCategory' => 'Video & Creator Equipments',
                'subCategorySlug' => 'video-creator-equipments',
                'originalPrice' => 149.99,
                'discountPercent' => 5,
                'inStock' => true,
                'brand' => 'Elgato',
                'rating' => 4.9,
                'reviews' => 80,
            ],
            [
                'id' => 206,
                'name' => 'Logitech Brio 4K Ultra HD Webcam with HDR and RightLight 3',
                'image' => '/images/products/sample-keyboard.jpg',
                'category' => 'Peripherals',
                'categorySlug' => 'peripherals',
                'subCategory' => 'Webcam',
                'subCategorySlug' => 'webcam',
                'originalPrice' => 199.99,
                'discountPercent' => 15,
                'inStock' => true,
                'brand' => 'Logitech',
                'rating' => 4.7,
                'reviews' => 61,
            ],
            // Laptops
            [
                'id' => 301,
                'name' => 'ASUS ROG Zephyrus G16 (2024) OLED 240Hz Intel Ultra 9 RTX 4080',
                'image' => '/images/products/sample-keyboard.jpg',
                'category' => 'Laptops',
                'categorySlug' => 'laptops',
                'subCategory' => 'Gaming Laptops',
                'subCategorySlug' => 'gaming-laptops',
                'originalPrice' => 2699.99,
                'discountPercent' => 7,
                'inStock' => true,
                'brand' => 'ASUS',
                'rating' => 4.9,
                'reviews' => 37,
            ],
            [
                'id' => 302,
                'name' => 'Lenovo Legion Pro 7i Gen 9 Intel i9 14th Gen RTX 4090 32GB',
                'image' => '/images/products/sample-keyboard.jpg',
                'category' => 'Laptops',
                'categorySlug' => 'laptops',
                'subCategory' => 'Gaming Laptops',
                'subCategorySlug' => 'gaming-laptops',
                'originalPrice' => 3199.00,
                'discountPercent' => 10,
                'inStock' => true,
                'brand' => 'Lenovo',
                'rating' => 4.9,
                'reviews' => 29,
            ],
            // Monitors & Displays
            [
                'id' => 401,
                'name' => 'Samsung Odyssey OLED G9 49" Curved Dual QHD 240Hz 0.03ms',
                'image' => '/images/products/sample-keyboard.jpg',
                'category' => 'Monitors & Displays',
                'categorySlug' => 'monitors-displays',
                'subCategory' => 'Gaming Monitors',
                'subCategorySlug' => 'gaming-monitors',
                'originalPrice' => 1799.99,
                'discountPercent' => 22,
                'inStock' => true,
                'brand' => 'Samsung',
                'rating' => 4.8,
                'reviews' => 49,
            ],
            [
                'id' => 402,
                'name' => 'LG UltraGear 27" QHD Nano IPS 1ms 180Hz Gaming Monitor',
                'image' => '/images/products/sample-keyboard.jpg',
                'category' => 'Monitors & Displays',
                'categorySlug' => 'monitors-displays',
                'subCategory' => 'Gaming Monitors',
                'subCategorySlug' => 'gaming-monitors',
                'originalPrice' => 299.99,
                'discountPercent' => 15,
                'inStock' => true,
                'brand' => 'LG',
                'rating' => 4.8,
                'reviews' => 93,
            ],
            // Gaming
            [
                'id' => 501,
                'name' => 'Sony PlayStation 5 Slim Digital Console 1TB SSD',
                'image' => '/images/products/sample-keyboard.jpg',
                'category' => 'Gaming',
                'categorySlug' => 'gaming',
                'subCategory' => 'Consoles',
                'subCategorySlug' => 'consoles',
                'originalPrice' => 449.99,
                'discountPercent' => 0,
                'inStock' => true,
                'brand' => 'Sony',
                'rating' => 4.9,
                'reviews' => 175,
            ],
            [
                'id' => 502,
                'name' => 'Xbox Wireless Controller Robot White with Bluetooth',
                'image' => '/images/products/sample-keyboard.jpg',
                'category' => 'Gaming',
                'categorySlug' => 'gaming',
                'subCategory' => 'Controllers',
                'subCategorySlug' => 'controllers',
                'originalPrice' => 59.99,
                'discountPercent' => 10,
                'inStock' => true,
                'brand' => 'Microsoft',
                'rating' => 4.7,
                'reviews' => 112,
            ],
            // Products
            [
                'id' => 601,
                'name' => 'APC Back-UPS Pro BR 1500VA Battery Backup & Surge Protector',
                'image' => '/images/products/sample-keyboard.jpg',
                'category' => 'Products',
                'categorySlug' => 'products',
                'subCategory' => 'UPS',
                'subCategorySlug' => 'ups',
                'originalPrice' => 229.99,
                'discountPercent' => 5,
                'inStock' => true,
                'brand' => 'APC',
                'rating' => 4.8,
                'reviews' => 34,
            ],
            [
                'id' => 602,
                'name' => 'Secretlab TITAN Evo Ergonomic Premium Gaming Chair Stealth',
                'image' => '/images/products/sample-keyboard.jpg',
                'category' => 'Products',
                'categorySlug' => 'products',
                'subCategory' => 'Chairs',
                'subCategorySlug' => 'chairs',
                'originalPrice' => 549.00,
                'discountPercent' => 0,
                'inStock' => true,
                'brand' => 'Secretlab',
                'rating' => 4.9,
                'reviews' => 77,
            ],
            [
                'id' => 603,
                'name' => 'Epson EcoTank L3250 Wi-Fi All-in-One Ink Tank Printer',
                'image' => '/images/products/sample-keyboard.jpg',
                'category' => 'Products',
                'categorySlug' => 'products',
                'subCategory' => 'Printers',
                'subCategorySlug' => 'printers',
                'originalPrice' => 199.99,
                'discountPercent' => 8,
                'inStock' => true,
                'brand' => 'Epson',
                'rating' => 4.6,
                'reviews' => 43,
            ],
            // Software
            [
                'id' => 701,
                'name' => 'Microsoft Windows 11 Pro 64-Bit License Retail Box',
                'image' => '/images/products/sample-keyboard.jpg',
                'category' => 'Software',
                'categorySlug' => 'software',
                'subCategory' => 'Operating Systems',
                'subCategorySlug' => 'os',
                'originalPrice' => 199.99,
                'discountPercent' => 25,
                'inStock' => true,
                'brand' => 'Microsoft',
                'rating' => 4.9,
                'reviews' => 82,
            ],
        ];

        // Filter by category
        if ($activeCat) {
            $catSlug = $activeCat['slug'];
            $all = array_filter($all, function ($item) use ($catSlug) {
                return ($item['categorySlug'] ?? '') === $catSlug;
            });
        }

        // Filter by subcategory
        if ($activeSub) {
            $subSlug = $activeSub['slug'];
            $all = array_filter($all, function ($item) use ($subSlug) {
                return ($item['subCategorySlug'] ?? '') === $subSlug;
            });
        }

        // Filter by search query
        if (!empty($search)) {
            $s = strtolower($search);
            $all = array_filter($all, function ($item) use ($s) {
                return str_contains(strtolower($item['name']), $s) ||
                       str_contains(strtolower($item['category']), $s) ||
                       str_contains(strtolower($item['subCategory'] ?? ''), $s);
            });
        }

        // Filter by price range
        if ($minPrice !== null && is_numeric($minPrice)) {
            $all = array_filter($all, fn($item) => $item['originalPrice'] >= (float)$minPrice);
        }
        if ($maxPrice !== null && is_numeric($maxPrice)) {
            $all = array_filter($all, fn($item) => $item['originalPrice'] <= (float)$maxPrice);
        }

        // Sorting
        usort($all, function ($a, $b) use ($sort) {
            if ($sort === 'price_asc') {
                return $a['originalPrice'] <=> $b['originalPrice'];
            }
            if ($sort === 'price_desc') {
                return $b['originalPrice'] <=> $a['originalPrice'];
            }
            if ($sort === 'newest') {
                return $b['id'] <=> $a['id'];
            }
            return ($b['discountPercent'] ?? 0) <=> ($a['discountPercent'] ?? 0);
        });

        return array_values($all);
    }
}
