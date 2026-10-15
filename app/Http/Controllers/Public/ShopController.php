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
     * Display single product view page
     */
    public function showProduct(Request $request, string $product): Response
    {
        $categories = $this->getCategoryHierarchy();

        // 1. Try to find in database
        $dbProduct = Product::where('is_visible', true)
            ->where(function ($q) use ($product) {
                $q->where('id', $product)->orWhere('slug', $product);
            })
            ->with(['brand', 'categories'])
            ->first();

        if ($dbProduct) {
            $formattedProduct = $this->formatDatabaseProduct($dbProduct);
            $subCategorySlug = $formattedProduct['subCategorySlug'] ?? null;
            $categorySlug = $formattedProduct['categorySlug'] ?? '';
            $related = $this->getRelatedProducts($dbProduct->id, $subCategorySlug, $categorySlug);
        } else {
            // 2. Fallback to mock catalog products
            $allMock = $this->getMockCatalogProducts(null, null, null, 'featured', null, null);
            $found = collect($allMock)->first(function ($item) use ($product) {
                return (string) $item['id'] === (string) $product ||
                    ($item['slug'] ?? '') === $product ||
                    \Illuminate\Support\Str::slug($item['name']) === $product;
            });

            if (!$found && !empty($allMock)) {
                $found = $allMock[0];
            }

            $formattedProduct = $this->enrichProductDetails($found);
            $subCategorySlug = $formattedProduct['subCategorySlug'] ?? null;
            $categorySlug = $formattedProduct['categorySlug'] ?? '';
            $related = $this->getRelatedProducts($formattedProduct['id'], $subCategorySlug, $categorySlug);
        }

        return Inertia::render('Product/Show', [
            'product' => $formattedProduct,
            'relatedProducts' => $related,
            'categories' => $categories,
        ]);
    }

    /**
     * Fetch all main categories and their active subcategories
     */
    public function getCategoryHierarchy()
    {
        return Category::main()
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
    }

    /**
     * Helper to render the unified Shop/Category view
     */
    protected function renderCatalog(Request $request, ?string $categorySlug, ?string $subcategorySlug): Response
    {
        // 1. Fetch categories
        $categories = $this->getCategoryHierarchy();

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
            $isMainCatWithoutSubs = in_array(strtolower($catName), ['laptops', 'monitors & displays', 'software']);
            $hasSubCat = $p->categories->count() > 1 && !$isMainCatWithoutSubs;
            $subCatName = $hasSubCat ? $p->categories->last()?->name : null;
            return [
                'id' => $p->id,
                'name' => $p->name,
                'image' => !empty($p->images) && is_array($p->images) ? $p->images[0] : '/images/products/sample-keyboard.jpg',
                'category' => $catName,
                'subCategory' => $subCatName,
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
            // Laptops (Category without subcategories)
            [
                'id' => 301,
                'name' => 'ASUS ROG Zephyrus G16 (2024) OLED 240Hz Intel Ultra 9 RTX 4080',
                'image' => '/images/products/sample-keyboard.jpg',
                'category' => 'Laptops',
                'categorySlug' => 'laptops',
                'subCategory' => null,
                'subCategorySlug' => null,
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
                'subCategory' => null,
                'subCategorySlug' => null,
                'originalPrice' => 3199.00,
                'discountPercent' => 10,
                'inStock' => true,
                'brand' => 'Lenovo',
                'rating' => 4.9,
                'reviews' => 29,
            ],
            // Monitors & Displays (Category without subcategories)
            [
                'id' => 401,
                'name' => 'Samsung Odyssey OLED G9 49" Curved Dual QHD 240Hz 0.03ms',
                'image' => '/images/products/sample-keyboard.jpg',
                'category' => 'Monitors & Displays',
                'categorySlug' => 'monitors-displays',
                'subCategory' => null,
                'subCategorySlug' => null,
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
                'subCategory' => null,
                'subCategorySlug' => null,
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
            // Software (Category without subcategories)
            [
                'id' => 701,
                'name' => 'Microsoft Windows 11 Pro 64-Bit License Retail Box',
                'image' => '/images/products/sample-keyboard.jpg',
                'category' => 'Software',
                'categorySlug' => 'software',
                'subCategory' => null,
                'subCategorySlug' => null,
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

    /**
     * Enrich single product with complete specifications, 4 images, warranty, and reviews
     */
    protected function enrichProductDetails(array $item): array
    {
        $id = $item['id'] ?? 101;
        $name = $item['name'] ?? 'Product';
        $brand = $item['brand'] ?? 'ComLink';
        $category = $item['category'] ?? 'PC Components';
        $categorySlug = $item['categorySlug'] ?? \Illuminate\Support\Str::slug($category);
        $isStandaloneCategory = in_array($categorySlug, ['laptops', 'monitors-displays', 'software']) ||
            in_array(strtolower($category), ['laptops', 'monitors & displays', 'software']);
        $subCategory = $isStandaloneCategory ? null : ($item['subCategory'] ?? null);
        $subCategorySlug = $isStandaloneCategory ? null : ($item['subCategorySlug'] ?? ($subCategory ? \Illuminate\Support\Str::slug($subCategory) : null));
        $targetSlug = $subCategorySlug ?: $categorySlug;
        $originalPrice = (float) ($item['originalPrice'] ?? 99.99);
        $discountPercent = (int) ($item['discountPercent'] ?? 0);
        $latestPrice = $discountPercent > 0 ? round($originalPrice - ($originalPrice * $discountPercent / 100), 2) : $originalPrice;
        $inStock = isset($item['inStock']) ? (bool) $item['inStock'] : true;
        $stockCount = $inStock ? ($item['stockCount'] ?? (($id % 15) + 6)) : 0;
        $rating = (float) ($item['rating'] ?? 4.9);
        $reviews = (int) ($item['reviews'] ?? 36);

        // Curate 4 distinct images
        $images = $this->resolve4ProductImages($targetSlug, $item['image'] ?? null);

        // Warranty based on category
        $warrantyYears = in_array($targetSlug, ['processors', 'motherboards', 'graphic-cards', 'power-supply']) ? '3 Years' : (in_array($targetSlug, ['laptops', 'gaming-laptops', 'gaming-monitors', 'monitors-displays', 'ups', 'chairs']) ? '2 Years' : '1 Year');

        $skuPrefix = strtoupper(substr($targetSlug, 0, 3));

        return [
            'id' => $id,
            'name' => $name,
            'sku' => 'CL-' . $skuPrefix . '-' . $id,
            'brand' => $brand,
            'category' => $category,
            'categorySlug' => $categorySlug,
            'subCategory' => $subCategory,
            'subCategorySlug' => $subCategorySlug,
            'originalPrice' => $originalPrice,
            'latestPrice' => $latestPrice,
            'discountPercent' => $discountPercent,
            'inStock' => $inStock,
            'stockCount' => $stockCount,
            'rating' => $rating,
            'reviews' => $reviews,
            'condition' => 'Brand New',
            'image' => $images[0],
            'images' => $images,
            'description' => "Experience elite performance and premium build quality with the {$name}. Engineered specifically for gamers, power users, and PC enthusiasts who demand maximum reliability, thermal efficiency, and longevity. Featuring cutting-edge technology and certified manufacturer testing, this component seamlessly integrates into modern high-performance setups.",
            'specifications' => $this->generateSpecifications($targetSlug, $brand, $name),
            'warranty' => [
                'duration' => "{$warrantyYears} Official ComLink Warranty",
                'type' => 'Manufacturer Replacement & Hardware Warranty',
                'covered' => [
                    'Factory manufacturing defects and hardware malfunctions',
                    'Internal component failure during standard operation',
                    'Firmware or controller chip hardware faults',
                    'Original box power cables and bundled hardware',
                ],
                'notCovered' => [
                    'Physical damage, PCB cracks, or burnt traces from electrical surge',
                    'Liquid spills, corrosion, or unauthorized repair attempts',
                    'Cosmetic wear, scratches, or altered serial number stickers',
                ],
                'claimProcess' => 'Visit any ComLink Flagship Store or send via insured courier with your original purchase receipt/invoice. Warranty claims are verified and processed within 3 to 5 business days.',
                'hotline' => '+94 11 234 5678',
                'email' => 'support@comlinkstore.com',
            ],
            'reviewsList' => [
                [
                    'id' => 1,
                    'author' => 'Tharindu Fernando',
                    'rating' => 5,
                    'date' => '3 days ago',
                    'verified' => true,
                    'title' => 'Exceptional build quality & performance',
                    'comment' => "Upgraded my rig with this and the difference is night and day. Packaging was secure, ComLink warranty sticker attached, and performance surpassed benchmarks.",
                ],
                [
                    'id' => 2,
                    'author' => 'Nimesh Perera',
                    'rating' => 5,
                    'date' => '1 week ago',
                    'verified' => true,
                    'title' => 'Worth every rupee!',
                    'comment' => 'Fast delivery from ComLink. The item was 100% brand new and sealed. Works like a charm out of the box. Highly recommend!',
                ],
                [
                    'id' => 3,
                    'author' => 'Sachith Wickramasinghe',
                    'rating' => 4,
                    'date' => '2 weeks ago',
                    'verified' => true,
                    'title' => 'Solid product, easy installation',
                    'comment' => 'Very reliable and runs cool under heavy load. The instructions were crystal clear and build aesthetics look very clean.',
                ],
            ],
        ];
    }

    /**
     * Format a database product model into the view schema
     */
    protected function formatDatabaseProduct(Product $p): array
    {
        $cat = $p->categories->first();
        $catName = $cat?->name ?? 'PC Components';
        $catSlug = $cat?->slug ?? 'pc-components';
        $isStandaloneCategory = in_array(strtolower($catName), ['laptops', 'monitors & displays', 'software']);
        $subCat = !$isStandaloneCategory && $p->categories->count() > 1 ? $p->categories->last() : null;

        $subCatName = $subCat?->name;
        $subCatSlug = $subCat?->slug;

        $originalPrice = (float) $p->price;
        $salePrice = $p->sale_price ? (float) $p->sale_price : $originalPrice;
        $discountPercent = ($salePrice < $originalPrice && $originalPrice > 0)
            ? (int) round((($originalPrice - $salePrice) / $originalPrice) * 100)
            : 0;

        $stock = (int) ($p->quantity ?? 0);
        $inStock = $stock > 0;

        $targetSlug = $subCatSlug ?: $catSlug;
        $images = is_array($p->images) && count($p->images) >= 4
            ? $p->images
            : $this->resolve4ProductImages($targetSlug, is_array($p->images) ? ($p->images[0] ?? null) : null);

        $base = [
            'id' => $p->id,
            'name' => $p->name,
            'brand' => $p->brand?->name ?? 'Generic',
            'category' => $catName,
            'categorySlug' => $catSlug,
            'subCategory' => $subCatName,
            'subCategorySlug' => $subCatSlug,
            'originalPrice' => $originalPrice,
            'discountPercent' => $discountPercent,
            'inStock' => $inStock,
            'stockCount' => $stock,
            'rating' => 4.9,
            'reviews' => 28,
            'image' => $images[0],
        ];

        return $this->enrichProductDetails($base);
    }

    /**
     * Fetch products belonging to the same subcategory for the carousel
     */
    protected function getRelatedProducts($currentProductId, ?string $subCategorySlug, string $categorySlug): array
    {
        // 1. Check if DB has products in this subcategory or category
        $dbQuery = Product::where('is_visible', true)
            ->where('id', '!=', $currentProductId);

        if (!empty($subCategorySlug)) {
            $dbQuery->whereHas('categories', function ($q) use ($subCategorySlug) {
                $q->where('slug', $subCategorySlug);
            });
        } elseif (!empty($categorySlug)) {
            $dbQuery->whereHas('categories', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        $dbRelated = $dbQuery->with(['brand', 'categories'])->take(8)->get();

        if ($dbRelated->count() >= 3) {
            return $dbRelated->map(function ($p) {
                $catName = $p->categories->first()?->name ?? 'Accessories';
                $isMainWithoutSub = in_array(strtolower($catName), ['laptops', 'monitors & displays', 'software']);
                $subCatName = !$isMainWithoutSub && $p->categories->count() > 1 ? $p->categories->last()?->name : null;
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'image' => !empty($p->images) && is_array($p->images) ? $p->images[0] : '/images/products/sample-keyboard.jpg',
                    'category' => $catName,
                    'subCategory' => $subCatName,
                    'originalPrice' => (float) $p->price,
                    'discountPercent' => $p->sale_price ? max(0, round((($p->price - $p->sale_price) / $p->price) * 100)) : 0,
                    'inStock' => ($p->quantity ?? 0) > 0,
                    'brand' => $p->brand?->name ?? 'ComLink',
                    'rating' => 4.8,
                    'reviews' => 24,
                ];
            })->all();
        }

        // 2. Mock catalog matching
        $allMock = $this->getMockCatalogProducts(null, null, null, 'featured', null, null);

        // Filter same subcategory, or same category if no subcategory exists
        if (!empty($subCategorySlug)) {
            $sameSub = array_filter($allMock, function ($item) use ($currentProductId, $subCategorySlug) {
                return (string) $item['id'] !== (string) $currentProductId &&
                    (($item['subCategorySlug'] ?? '') === $subCategorySlug);
            });
        } else {
            $sameSub = array_filter($allMock, function ($item) use ($currentProductId, $categorySlug) {
                return (string) $item['id'] !== (string) $currentProductId &&
                    (($item['categorySlug'] ?? '') === $categorySlug);
            });
        }

        // If fewer than 4 items, include items from the same main category
        if (count($sameSub) < 4) {
            $sameCat = array_filter($allMock, function ($item) use ($currentProductId, $categorySlug, $sameSub) {
                $alreadyIncluded = collect($sameSub)->pluck('id')->all();
                return (string) $item['id'] !== (string) $currentProductId &&
                    !in_array($item['id'], $alreadyIncluded) &&
                    (($item['categorySlug'] ?? '') === $categorySlug);
            });
            $sameSub = array_merge($sameSub, $sameCat);
        }

        // If still fewer than 4 items, backfill with top products from any category
        if (count($sameSub) < 4) {
            $alreadyIncluded = collect($sameSub)->pluck('id')->all();
            $alreadyIncluded[] = $currentProductId;
            $others = array_filter($allMock, fn($item) => !in_array($item['id'], $alreadyIncluded));
            $sameSub = array_merge($sameSub, array_slice($others, 0, 8 - count($sameSub)));
        }

        return array_values(array_slice($sameSub, 0, 10));
    }

    /**
     * Resolve 4 distinct high-resolution product images for the 4 image thumbnail boxes
     */
    protected function resolve4ProductImages(string $subCategorySlug, ?string $primaryImage): array
    {
        $library = [
            'keyboards' => [
                $primaryImage ?: '/images/products/sample-keyboard.jpg',
                'https://images.unsplash.com/photo-1587829741301-dc798b83add3?auto=format&fit=crop&q=80&w=900',
                'https://images.unsplash.com/photo-1595225476474-87563907a212?auto=format&fit=crop&q=80&w=900',
                'https://images.unsplash.com/photo-1618384887929-16ec33fab9ef?auto=format&fit=crop&q=80&w=900',
            ],
            'mouse' => [
                'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?auto=format&fit=crop&q=80&w=900',
                'https://images.unsplash.com/photo-1626771743606-f6d2f3ff430e?auto=format&fit=crop&q=80&w=900',
                'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?auto=format&fit=crop&q=80&w=900',
                'https://images.unsplash.com/photo-1629429408209-1f912961dbd8?auto=format&fit=crop&q=80&w=900',
            ],
            'headsets' => [
                'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&q=80&w=900',
                'https://images.unsplash.com/photo-1546435770-a3e426bf472b?auto=format&fit=crop&q=80&w=900',
                'https://images.unsplash.com/photo-1484704849700-f032a568e944?auto=format&fit=crop&q=80&w=900',
                'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&q=80&w=900',
            ],
            'processors' => [
                'https://images.unsplash.com/photo-1555680202-c86f0e12f086?auto=format&fit=crop&q=80&w=900',
                'https://images.unsplash.com/photo-1591799264318-7e6ef8ddb7ea?auto=format&fit=crop&q=80&w=900',
                'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&q=80&w=900',
                'https://images.unsplash.com/photo-1587202372775-e229f172b9d7?auto=format&fit=crop&q=80&w=900',
            ],
            'motherboards' => [
                'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&q=80&w=900',
                'https://images.unsplash.com/photo-1555680202-c86f0e12f086?auto=format&fit=crop&q=80&w=900',
                'https://images.unsplash.com/photo-1591799264318-7e6ef8ddb7ea?auto=format&fit=crop&q=80&w=900',
                'https://images.unsplash.com/photo-1563770660941-20978e870e26?auto=format&fit=crop&q=80&w=900',
            ],
            'graphic-cards' => [
                'https://images.unsplash.com/photo-1587202372775-e229f172b9d7?auto=format&fit=crop&q=80&w=900',
                'https://images.unsplash.com/photo-1555680202-c86f0e12f086?auto=format&fit=crop&q=80&w=900',
                'https://images.unsplash.com/photo-1591799264318-7e6ef8ddb7ea?auto=format&fit=crop&q=80&w=900',
                'https://images.unsplash.com/photo-1526738549149-8e07eca6c147?auto=format&fit=crop&q=80&w=900',
            ],
            'laptops' => [
                'https://images.unsplash.com/photo-1603302576837-37561b2e2302?auto=format&fit=crop&q=80&w=900',
                'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?auto=format&fit=crop&q=80&w=900',
                'https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?auto=format&fit=crop&q=80&w=900',
                'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&q=80&w=900',
            ],
            'gaming-laptops' => [
                'https://images.unsplash.com/photo-1603302576837-37561b2e2302?auto=format&fit=crop&q=80&w=900',
                'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?auto=format&fit=crop&q=80&w=900',
                'https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?auto=format&fit=crop&q=80&w=900',
                'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&q=80&w=900',
            ],
            'monitors-displays' => [
                'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?auto=format&fit=crop&q=80&w=900',
                'https://images.unsplash.com/photo-1586210579191-33b45e38fa2c?auto=format&fit=crop&q=80&w=900',
                'https://images.unsplash.com/photo-1551645120-d70bfe84c826?auto=format&fit=crop&q=80&w=900',
                'https://images.unsplash.com/photo-1547082299-de196ea013d6?auto=format&fit=crop&q=80&w=900',
            ],
            'gaming-monitors' => [
                'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?auto=format&fit=crop&q=80&w=900',
                'https://images.unsplash.com/photo-1586210579191-33b45e38fa2c?auto=format&fit=crop&q=80&w=900',
                'https://images.unsplash.com/photo-1551645120-d70bfe84c826?auto=format&fit=crop&q=80&w=900',
                'https://images.unsplash.com/photo-1547082299-de196ea013d6?auto=format&fit=crop&q=80&w=900',
            ],
            'software' => [
                'https://images.unsplash.com/photo-1618401471353-b98afee0b2eb?auto=format&fit=crop&q=80&w=900',
                'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&q=80&w=900',
                'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?auto=format&fit=crop&q=80&w=900',
                'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?auto=format&fit=crop&q=80&w=900',
            ],
            'ssd' => [
                'https://images.unsplash.com/photo-1597872200969-2b65d56bd16b?auto=format&fit=crop&q=80&w=900',
                'https://images.unsplash.com/photo-1555680202-c86f0e12f086?auto=format&fit=crop&q=80&w=900',
                'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&q=80&w=900',
                'https://images.unsplash.com/photo-1591799264318-7e6ef8ddb7ea?auto=format&fit=crop&q=80&w=900',
            ],
        ];

        if (isset($library[$subCategorySlug])) {
            return $library[$subCategorySlug];
        }

        $base = $primaryImage ?: '/images/products/sample-keyboard.jpg';
        return [
            $base,
            'https://images.unsplash.com/photo-1587829741301-dc798b83add3?auto=format&fit=crop&q=80&w=900',
            'https://images.unsplash.com/photo-1595225476474-87563907a212?auto=format&fit=crop&q=80&w=900',
            'https://images.unsplash.com/photo-1618384887929-16ec33fab9ef?auto=format&fit=crop&q=80&w=900',
        ];
    }

    /**
     * Generate structured specifications for product tabs
     */
    protected function generateSpecifications(?string $segmentSlug, string $brand, string $name): array
    {
        $segmentName = $segmentSlug ? ucwords(str_replace('-', ' ', $segmentSlug)) : 'Hardware';
        $specs = [
            [
                'group' => 'General Specifications',
                'items' => [
                    ['label' => 'Brand', 'value' => $brand],
                    ['label' => 'Model / Series', 'value' => $name],
                    ['label' => 'Category Segment', 'value' => $segmentName],
                    ['label' => 'Condition', 'value' => '100% Brand New (Original Factory Sealed)'],
                ],
            ],
            [
                'group' => 'Technical Performance',
                'items' => [
                    ['label' => 'Interface / Connection', 'value' => 'High-Speed PCIe Gen 4 / USB 3.2 / AM5 / LGA1700'],
                    ['label' => 'Architecture', 'value' => 'Optimized Next-Gen Microarchitecture with Low Latency'],
                    ['label' => 'Thermal Efficiency', 'value' => 'Advanced cooling profile with heat dissipation fins'],
                    ['label' => 'Power Consumption', 'value' => 'Eco-efficient power curve with dynamic voltage scaling'],
                ],
            ],
            [
                'group' => 'Compatibility & Standards',
                'items' => [
                    ['label' => 'Operating Systems', 'value' => 'Windows 11 / Windows 10 (64-bit), Linux Kernel 5.4+'],
                    ['label' => 'Certification', 'value' => 'CE, FCC, RoHS, EnergyStar Certified'],
                    ['label' => 'Dimensions & Weight', 'value' => 'Standard Form Factor (~450g packaged)'],
                    ['label' => 'Included in Box', 'value' => "1x {$name}, Quick Installation Manual, Safety Guide, Warranty Certificate"],
                ],
            ],
        ];

        return $specs;
    }
}
