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
     * Live search API for SearchModal
     */
    public function searchApi(Request $request)
    {
        $query = trim((string) ($request->query('q') ?: $request->query('search', '')));
        $categorySlug = trim((string) $request->query('category', ''));
        $limit = min(20, max(1, (int) $request->query('limit', 8)));

        $productsQuery = Product::where('is_visible', true)
            ->with(['brand', 'categories.parent']);

        if (! empty($query)) {
            $productsQuery->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('product_code', 'like', "%{$query}%")
                    ->orWhere('sku', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhereHas('brand', fn ($b) => $b->where('name', 'like', "%{$query}%"))
                    ->orWhereHas('categories', fn ($c) => $c->where('name', 'like', "%{$query}%"));
            });
        }

        if (! empty($categorySlug) && $categorySlug !== 'all') {
            $productsQuery->whereHas('categories', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug)
                    ->orWhereHas('parent', fn ($p) => $p->where('slug', $categorySlug));
            });
        }

        $totalCount = (clone $productsQuery)->count();

        $results = $productsQuery->latest('id')
            ->take($limit)
            ->get()
            ->map(function ($product) {
                $images = is_array($product->images) ? $product->images : [];
                $first = $images[0] ?? null;
                $imageUrl = $first
                    ? (str_starts_with($first, 'http') || str_starts_with($first, '/') ? $first : asset('storage/' . $first))
                    : asset('images/placeholder.png');

                $mainCat = $product->categories->firstWhere('parent_id', null);
                $subCat = $product->categories->firstWhere('parent_id', '!=', null);

                $price = (float) ($product->sale_price ?: $product->price ?: 0);
                $originalPrice = (float) ($product->sale_price && $product->sale_price < $product->price ? $product->price : 0);
                $qty = (int) ($product->quantity ?? $product->inventory_stock ?? 0);

                return [
                    'id' => (string) $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug ?: (string) $product->id,
                    'product_code' => $product->product_code,
                    'price' => $price,
                    'originalPrice' => $originalPrice > 0 ? $originalPrice : null,
                    'category' => $mainCat?->name ?? ($product->categories->first()?->name ?? 'Accessories'),
                    'subCategory' => $subCat?->name ?? null,
                    'brand' => $product->brand?->name ?? null,
                    'condition' => $product->condition ?? 'Brand New',
                    'images' => [$imageUrl],
                    'inStock' => $qty > 0,
                    'quantity' => $qty,
                ];
            });

        return response()->json([
            'products' => $results,
            'total' => $totalCount,
        ]);
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
                if (is_numeric($product)) {
                    $q->where('id', (int) $product)->orWhere('slug', $product);
                } else {
                    $q->where('slug', $product);
                }
            })
            ->with(['brand', 'categories', 'variants'])
            ->first();

        if ($dbProduct) {
            $formattedProduct = $this->formatDatabaseProduct($dbProduct);
            $subCategorySlug = $formattedProduct['subCategorySlug'] ?? null;
            $categorySlug = $formattedProduct['categorySlug'] ?? '';
            $related = $this->getRelatedProducts($dbProduct->id, $subCategorySlug, $categorySlug);
        } else {
            abort(404, 'Product not found');
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

        // 3. Filter by search query
        $search = trim((string) ($request->query('q') ?: $request->query('search', '')));
        if (! empty($search)) {
            $dbProductsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('brand', fn ($b) => $b->where('name', 'like', "%{$search}%"));
            });
        }

        // Calculate the maximum Latest Price among all products in this page scope (before price filtering)
        $scopeProducts = (clone $dbProductsQuery)->get(['price', 'sale_price']);
        $pageMaxPrice = (float) ($scopeProducts->map(function ($p) {
            return ($p->sale_price !== null && (float) $p->sale_price > 0)
                ? (float) $p->sale_price
                : (float) $p->price;
        })->max() ?? 0);
        $pageMaxPrice = ceil($pageMaxPrice);
        if ($pageMaxPrice <= 0) {
            $fallbackMax = (float) (Product::where('is_visible', true)->get(['price', 'sale_price'])->map(function ($p) {
                return ($p->sale_price !== null && (float) $p->sale_price > 0)
                    ? (float) $p->sale_price
                    : (float) $p->price;
            })->max() ?? 50000);
            $pageMaxPrice = ceil(max($fallbackMax, 50000));
        }

        // 4. Filter by price (using Latest Selling Price)
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');
        $priceSql = 'CASE WHEN sale_price IS NOT NULL AND sale_price > 0 THEN sale_price ELSE price END';

        if ($minPrice !== null && is_numeric($minPrice) && (float) $minPrice > 0) {
            $dbProductsQuery->whereRaw("({$priceSql}) >= ?", [(float) $minPrice]);
        }
        if ($maxPrice !== null && is_numeric($maxPrice)) {
            $dbProductsQuery->whereRaw("({$priceSql}) <= ?", [(float) $maxPrice]);
        }

        // 5. Filter by condition (All / Brandnew / Used)
        $condition = $request->query('condition', 'all');
        if ($condition && $condition !== 'all') {
            if (in_array(strtolower($condition), ['brandnew', 'brand new', 'brand_new'])) {
                $dbProductsQuery->where(function ($q) {
                    $q->where('condition', 'like', '%Brand%')
                        ->orWhere('condition', 'like', '%New%');
                });
            } elseif (strtolower($condition) === 'used') {
                $dbProductsQuery->where('condition', 'like', '%Used%');
            }
        }

        // 6. Filter by availability (In Stock / Out of Stock)
        if ($request->boolean('in_stock')) {
            $dbProductsQuery->where('quantity', '>', 0);
        }
        if ($request->boolean('out_of_stock')) {
            $dbProductsQuery->where('quantity', '<=', 0);
        }

        // 6. Filter by Brand (from query parameter 'brand')
        $brandParam = $request->query('brand');
        if ($brandParam) {
            $selectedBrands = is_array($brandParam) ? $brandParam : explode(',', (string) $brandParam);
            $selectedBrands = array_filter(array_map('trim', $selectedBrands));

            if (! empty($selectedBrands)) {
                $dbProductsQuery->whereHas('brand', function ($bq) use ($selectedBrands) {
                    $bq->whereIn('name', $selectedBrands)
                        ->orWhereIn('slug', $selectedBrands);
                });
            }
        }

        // 7. Dynamic Specification Attributes & Facets for Category/Subcategory
        // ONLY shown if on a Sub Category page, OR if category has NO subcategories
        $dynamicFacets = [];
        $hasActiveSubCategory = ! empty($activeSubCategory);
        $categoryHasNoSubcategories = ! empty($activeCategory) && empty($activeCategory['subcategories']);

        if ($hasActiveSubCategory || $categoryHasNoSubcategories) {
            $catId = $activeCategory['id'];
            $subId = $activeSubCategory['id'] ?? null;

            $specQuery = \App\Models\ProductSpecification::where('category_id', $catId)
                ->where('specification_type', 'main');

            if ($subId) {
                $specQuery->where(function ($q) use ($subId) {
                    $q->where('sub_category_id', $subId)
                        ->orWhereNull('sub_category_id');
                });
            } else {
                $specQuery->whereNull('sub_category_id');
            }

            $specAttributes = $specQuery->orderBy('sort_order')->orderBy('id')->get();

            // Fetch products in this category/subcategory to extract actual values
            $baseCategoryProducts = Product::where('is_visible', true)
                ->whereHas('categories', function ($q) use ($subId, $catId) {
                    if ($subId) {
                        $q->where('categories.id', $subId);
                    } else {
                        $q->where('categories.id', $catId);
                    }
                })
                ->get();

            foreach ($specAttributes as $spec) {
                // Ignore Brand title because Brand is already added previously as its own section
                if (strtolower(trim($spec->title)) === 'brand') {
                    continue;
                }

                $key = \Illuminate\Support\Str::slug($spec->title, '_');
                $title = trim($spec->title);
                $values = [];

                foreach ($baseCategoryProducts as $bp) {
                    $specs = $bp->main_specifications ?? [];
                    $tech = $bp->technical_specifications ?? [];

                    // Resilient lookup: exact title, slug key, or case-insensitive matching
                    $rawVal = $specs[$title] ?? $specs[$key] ?? $tech[$title] ?? $tech[$key] ?? null;

                    if ($rawVal === null) {
                        foreach ($specs as $k => $v) {
                            if (strcasecmp((string) $k, $title) === 0 || strcasecmp(\Illuminate\Support\Str::slug((string) $k, '_'), $key) === 0) {
                                $rawVal = $v;
                                break;
                            }
                        }
                    }

                    if ($rawVal === null) {
                        foreach ($tech as $k => $v) {
                            if (strcasecmp((string) $k, $title) === 0 || strcasecmp(\Illuminate\Support\Str::slug((string) $k, '_'), $key) === 0) {
                                $rawVal = $v;
                                break;
                            }
                        }
                    }

                    if ($rawVal !== null && trim((string) $rawVal) !== '') {
                        $values[] = trim((string) $rawVal);
                    }
                }

                $values = array_values(array_unique(array_filter($values)));

                if (! empty($values)) {
                    $dynamicFacets[] = [
                        'id' => $key,
                        'label' => $spec->title,
                        'options' => $values,
                    ];
                }

                // Check and apply filter from query params
                $filterVal = $request->query($key) ?? $request->input("specs.{$key}");
                if ($filterVal) {
                    $selectedOptions = is_array($filterVal) ? $filterVal : explode(',', (string) $filterVal);
                    $selectedOptions = array_filter(array_map('trim', $selectedOptions));

                    if (! empty($selectedOptions)) {
                        $dbProductsQuery->where(function ($sq) use ($key, $title, $selectedOptions) {
                            foreach ($selectedOptions as $opt) {
                                $sq->orWhere("main_specifications->{$key}", $opt)
                                    ->orWhere("main_specifications->{$key}", 'like', "%{$opt}%")
                                    ->orWhere("main_specifications->{$title}", $opt)
                                    ->orWhere("main_specifications->{$title}", 'like', "%{$opt}%")
                                    ->orWhere("technical_specifications->{$key}", $opt)
                                    ->orWhere("technical_specifications->{$key}", 'like', "%{$opt}%")
                                    ->orWhere("technical_specifications->{$title}", $opt)
                                    ->orWhere("technical_specifications->{$title}", 'like', "%{$opt}%");
                            }
                        });
                    }
                }
            }
        }

        // 8. Sorting
        $sort = $request->query('sort', 'featured');
        switch ($sort) {
            case 'price_asc':
                $dbProductsQuery->orderByRaw("({$priceSql}) asc");
                break;
            case 'price_desc':
                $dbProductsQuery->orderByRaw("({$priceSql}) desc");
                break;
            case 'newest':
                $dbProductsQuery->orderBy('created_at', 'desc');
                break;
            default:
                $dbProductsQuery->orderBy('is_featured', 'desc')->orderBy('created_at', 'desc');
                break;
        }

        $perPage = 12;
        $paginated = $dbProductsQuery->paginate($perPage)->withQueryString();

        $products = $paginated->getCollection()->map(function ($p) {
            $cat = $p->categories->firstWhere('parent_id', null) ?? $p->categories->first();
            $catName = $cat?->name ?? 'Accessories';
            $isMainCatWithoutSubs = in_array(strtolower($catName), ['laptops', 'software']);
            $hasSubCat = $p->categories->count() > 1 && ! $isMainCatWithoutSubs;
            $subCat = $hasSubCat ? $p->categories->last() : null;
            $subCatName = $subCat?->name;

            $rawImg = ! empty($p->images) && is_array($p->images) ? $p->images[0] : null;
            $image = $rawImg ? ((str_starts_with($rawImg, 'http') || str_starts_with($rawImg, '/')) ? $rawImg : asset('storage/'.$rawImg)) : '/images/placeholder.png';

            return [
                'id' => $p->id,
                'name' => $p->name,
                'image' => $image,
                'category' => $catName,
                'categorySlug' => $cat?->slug ?? '',
                'subCategory' => $subCatName,
                'subCategorySlug' => $subCat?->slug ?? '',
                'originalPrice' => (float) $p->price,
                'latestPrice' => $p->sale_price !== null ? (float) $p->sale_price : (float) $p->price,
                'discountPercent' => ($p->sale_price && $p->price > 0) ? max(0, round((($p->price - $p->sale_price) / $p->price) * 100)) : 0,
                'inStock' => ($p->quantity ?? 0) > 0,
                'condition' => $p->condition ?? 'Brand New',
                'brand' => $p->brand?->name ?? 'Generic',
                'rating' => 4.8,
                'reviews' => 24,
                'main_specifications' => $p->main_specifications ?? [],
                'technical_specifications' => $p->technical_specifications ?? [],
            ];
        })->values()->all();

        $pagination = [
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'per_page' => $paginated->perPage(),
            'total' => $paginated->total(),
            'from' => $paginated->firstItem(),
            'to' => $paginated->lastItem(),
            'prev_page_url' => $paginated->previousPageUrl(),
            'next_page_url' => $paginated->nextPageUrl(),
            'links' => $paginated->linkCollection()->map(function ($link) {
                return [
                    'url' => $link['url'],
                    'label' => $link['label'],
                    'active' => (bool) $link['active'],
                ];
            })->values()->all(),
        ];

        // Real brands added by admin in database (scoped to active category/subcategory if present)
        $brandsQuery = \App\Models\Brand::where('is_visible', true);

        if ($activeSubCategory) {
            $subId = $activeSubCategory['id'];
            $brandsQuery->whereHas('products', function ($pq) use ($subId) {
                $pq->where('is_visible', true)
                    ->whereHas('categories', fn ($cq) => $cq->where('categories.id', $subId));
            });
        } elseif ($activeCategory) {
            $catId = $activeCategory['id'];
            $subIds = collect($activeCategory['subcategories'])->pluck('id')->all();
            $targetCategoryIds = array_merge([$catId], $subIds);
            $brandsQuery->whereHas('products', function ($pq) use ($targetCategoryIds) {
                $pq->where('is_visible', true)
                    ->whereHas('categories', fn ($cq) => $cq->whereIn('categories.id', $targetCategoryIds));
            });
        }

        $brands = $brandsQuery
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'slug'])
            ->map(fn ($b) => [
                'id' => $b->id,
                'name' => $b->name,
                'slug' => $b->slug,
            ])
            ->values()
            ->all();

        // If scoping yielded no brand rows, fallback to all visible brands so filter is never empty
        if (empty($brands)) {
            $brands = \App\Models\Brand::where('is_visible', true)
                ->orderBy('sort_order', 'asc')
                ->orderBy('name', 'asc')
                ->get(['id', 'name', 'slug'])
                ->map(fn ($b) => [
                    'id' => $b->id,
                    'name' => $b->name,
                    'slug' => $b->slug,
                ])
                ->values()
                ->all();
        }

        $allQueryParams = $request->all();

        return Inertia::render('Shop/Index', [
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'activeSubCategory' => $activeSubCategory,
            'products' => $products,
            'pagination' => $pagination,
            'brands' => $brands,
            'dynamicFacets' => $dynamicFacets,
            'pageMaxPrice' => $pageMaxPrice,
            'filters' => array_merge([
                'category' => $activeCategory['slug'] ?? null,
                'subcategory' => $activeSubCategory['slug'] ?? null,
                'q' => $search ?? '',
                'sort' => $sort,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'in_stock' => $request->boolean('in_stock', false),
                'out_of_stock' => $request->boolean('out_of_stock', false),
                'condition' => $condition,
                'brand' => $brandParam,
            ], $allQueryParams),
        ]);
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
        $isStandaloneCategory = in_array($categorySlug, ['laptops', 'software']) ||
            in_array(strtolower($category), ['laptops', 'software']);
        $subCategory = $isStandaloneCategory ? null : ($item['subCategory'] ?? null);
        $subCategorySlug = $isStandaloneCategory ? null : ($item['subCategorySlug'] ?? ($subCategory ? \Illuminate\Support\Str::slug($subCategory) : null));
        $targetSlug = $subCategorySlug ?: $categorySlug;
        $originalPrice = (float) ($item['originalPrice'] ?? 99.99);
        $discountPercent = (int) ($item['discountPercent'] ?? 0);
        $latestPrice = isset($item['latestPrice'])
            ? (float) $item['latestPrice']
            : ($discountPercent > 0 ? round($originalPrice - ($originalPrice * $discountPercent / 100), 2) : $originalPrice);
        $inStock = isset($item['inStock']) ? (bool) $item['inStock'] : true;
        $stockCount = $inStock ? ($item['stockCount'] ?? (($id % 15) + 6)) : 0;
        $rating = (float) ($item['rating'] ?? 4.9);
        $reviews = (int) ($item['reviews'] ?? 36);

        // Image resolution: preserve real uploaded product images or variant images
        $images = ! empty($item['images']) && is_array($item['images'])
            ? $item['images']
            : (! empty($item['image']) ? [$item['image']] : ['/images/placeholder.png']);

        // Warranty duration and period unit
        $warrantyDuration = ! empty($item['warranty_duration']) ? (int) $item['warranty_duration'] : null;
        $warrantyUnit = ! empty($item['warranty_unit']) ? (string) $item['warranty_unit'] : 'Years';

        if ($warrantyDuration !== null && $warrantyDuration > 0) {
            $unitFormatted = $warrantyDuration === 1 ? rtrim($warrantyUnit, 's') : (rtrim($warrantyUnit, 's').'s');
            $warrantyText = "{$warrantyDuration} ".strtoupper($unitFormatted).' OFFICIAL COMLINK WARRANTY';
        } else {
            $warrantyYears = in_array($targetSlug, ['processors', 'motherboards', 'graphic-cards', 'power-supply']) ? '3 Years' : (in_array($targetSlug, ['laptops', 'gaming-laptops', 'gaming-monitors', 'monitors-displays', 'ups', 'chairs']) ? '2 Years' : '1 Year');
            $warrantyText = strtoupper("{$warrantyYears} Official ComLink Warranty");
        }

        $skuPrefix = strtoupper(substr($targetSlug, 0, 3));
        $condition = $item['condition'] ?? 'Brand New';
        $description = ! empty($item['description'])
            ? $item['description']
            : "Genuine {$name} available at ComLink Computer Accessories with official warranty.";
        $specifications = ! empty($item['specifications'])
            ? $item['specifications']
            : [];

        return [
            'id' => $id,
            'name' => $name,
            'sku' => $item['sku'] ?? ('CL-'.$skuPrefix.'-'.$id),
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
            'condition' => $condition,
            'warranty_duration' => $warrantyDuration,
            'warranty_unit' => $warrantyUnit,
            'image' => $images[0],
            'images' => $images,
            'description' => $description,
            'specifications' => $specifications,
            'main_specifications' => $item['main_specifications'] ?? null,
            'technical_specifications' => $item['technical_specifications'] ?? null,
            'warranty' => [
                'duration' => $warrantyText,
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
                    'comment' => 'Upgraded my rig with this and the difference is night and day. Packaging was secure, ComLink warranty sticker attached, and performance surpassed benchmarks.',
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
            'hasColorVariants' => $item['hasColorVariants'] ?? false,
            'variants' => $item['variants'] ?? [],
        ];
    }

    /**
     * Format a database product model into the view schema
     */
    protected function formatDatabaseProduct(Product $p): array
    {
        $cat = $p->categories->firstWhere('parent_id', null) ?? $p->categories->first();
        $catName = $cat?->name ?? 'PC Components';
        $catSlug = $cat?->slug ?? 'pc-components';
        $isStandaloneCategory = in_array(strtolower($catName), ['laptops', 'software']);
        $subCat = ! $isStandaloneCategory ? ($p->categories->firstWhere('parent_id', '!=', null) ?? ($p->categories->count() > 1 ? $p->categories->last() : null)) : null;

        $subCatName = $subCat?->name;
        $subCatSlug = $subCat?->slug;

        $originalPrice = (float) $p->price;
        $salePrice = $p->sale_price !== null ? (float) $p->sale_price : $originalPrice;
        $discountPercent = ($salePrice < $originalPrice && $originalPrice > 0)
            ? (int) round((($originalPrice - $salePrice) / $originalPrice) * 100)
            : 0;

        $stock = (int) ($p->quantity ?? 0);
        $inStock = $stock > 0;

        $targetSlug = $subCatSlug ?: $catSlug;

        // Process images: if product has color variants, prioritize images from the variant sections
        $hasColorVariants = $p->hasColorVariants() && $p->variants->isNotEmpty();
        $images = [];

        if ($hasColorVariants) {
            foreach ($p->variants as $v) {
                if (! empty($v->image)) {
                    $images[] = (str_starts_with($v->image, 'http') || str_starts_with($v->image, '/'))
                        ? $v->image
                        : asset('storage/'.$v->image);
                }
            }
        }

        $rawImages = is_array($p->images) ? array_values(array_filter($p->images)) : [];
        foreach ($rawImages as $img) {
            if (empty($img)) {
                continue;
            }
            $imgUrl = (str_starts_with($img, 'http') || str_starts_with($img, '/')) ? $img : asset('storage/'.$img);
            if (! in_array($imgUrl, $images, true)) {
                $images[] = $imgUrl;
            }
        }

        if (empty($images)) {
            $images = ['/images/placeholder.png'];
        }

        // Build specifications from database technical_specifications if available
        $rawTech = is_array($p->technical_specifications) ? $p->technical_specifications : [];
        $flattenTech = function ($arr, $prefix = '') use (&$flattenTech) {
            $flat = [];
            foreach ($arr as $k => $v) {
                if (is_array($v)) {
                    $flat = array_merge($flat, $flattenTech($v, ''));
                } elseif (! is_null($v) && trim((string) $v) !== '') {
                    $key = \Illuminate\Support\Str::slug((string) $k, '_');
                    $flat[$key] = trim((string) $v);
                }
            }
            return $flat;
        };
        $tech = $flattenTech($rawTech);

        if (! empty($tech)) {
            $catId = $cat?->id;
            $dbSpecs = \App\Models\ProductSpecification::where('specification_type', 'technical')
                ->when($catId, fn ($q) => $q->where('category_id', $catId))
                ->get()
                ->keyBy(fn ($s) => \Illuminate\Support\Str::slug($s->title, '_'));

            $groupedItems = [
                'General Specifications' => [],
                'Technical Performance' => [],
                'Compatibility & Standards' => [],
            ];

            foreach ($tech as $key => $value) {
                if (is_null($value) || trim((string) $value) === '') {
                    continue;
                }

                $matchedSpec = $dbSpecs->get($key);
                $title = $matchedSpec ? $matchedSpec->title : ucwords(str_replace('_', ' ', $key));
                $groupName = match ($matchedSpec?->sub_section) {
                    'general' => 'General Specifications',
                    'performance' => 'Technical Performance',
                    'compatibility' => 'Compatibility & Standards',
                    default => match (true) {
                        in_array($key, ['interface_connection', 'architecture', 'thermal_efficiency', 'power_consumption']) => 'Technical Performance',
                        in_array($key, ['operating_systems', 'certification', 'dimensions_weight', 'included_in_box']) => 'Compatibility & Standards',
                        default => 'General Specifications',
                    },
                };

                $groupedItems[$groupName][] = [
                    'label' => $title,
                    'value' => (string) $value,
                ];
            }

            $specifications = [];
            foreach ($groupedItems as $groupName => $items) {
                if (! empty($items)) {
                    $specifications[] = [
                        'group' => $groupName,
                        'items' => $items,
                    ];
                }
            }
        } else {
            $specifications = [];
        }

        $base = [
            'id' => $p->id,
            'name' => $p->name,
            'sku' => $p->sku,
            'brand' => $p->brand?->name ?? 'Generic',
            'category' => $catName,
            'categorySlug' => $catSlug,
            'subCategory' => $subCatName,
            'subCategorySlug' => $subCatSlug,
            'originalPrice' => $originalPrice,
            'latestPrice' => $salePrice,
            'discountPercent' => $discountPercent,
            'inStock' => $inStock,
            'stockCount' => $stock,
            'condition' => $p->condition ?? 'Brand New',
            'rating' => 4.9,
            'reviews' => 28,
            'image' => $images[0],
            'images' => $images,
            'description' => $p->description,
            'specifications' => $specifications,
            'main_specifications' => $p->main_specifications,
            'technical_specifications' => $p->technical_specifications,
            'warranty_duration' => $p->warranty_duration,
            'warranty_unit' => $p->warranty_unit,
            'hasColorVariants' => $p->hasColorVariants(),
            'variants' => $p->variants->map(function ($v) {
                $img = $v->image;
                $imgUrl = $img ? ((str_starts_with($img, 'http') || str_starts_with($img, '/')) ? $img : asset('storage/'.$img)) : null;
                $orig = (float) $v->price;
                $sale = $v->sale_price !== null ? (float) $v->sale_price : $orig;
                $disc = ($sale < $orig && $orig > 0) ? (int) round((($orig - $sale) / $orig) * 100) : (int) ($v->discount_percentage ?? 0);
                $stock = (int) ($v->stock ?? $v->show_quantity ?? 0);

                return [
                    'id' => $v->id,
                    'color' => $v->color_name,
                    'name' => $v->variant_name ?? ($v->color_name ? "{$v->product?->name} - {$v->color_name}" : $v->product?->name),
                    'condition' => $v->condition ?? 'Brand New',
                    'originalPrice' => $orig,
                    'latestPrice' => $sale,
                    'discountPercent' => $disc,
                    'inStock' => $stock > 0,
                    'stockCount' => $stock,
                    'image' => $imgUrl,
                ];
            })->values()->all(),
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

        if (! empty($subCategorySlug)) {
            $dbQuery->whereHas('categories', function ($q) use ($subCategorySlug) {
                $q->where('slug', $subCategorySlug);
            });
        } elseif (! empty($categorySlug)) {
            $dbQuery->whereHas('categories', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        $dbRelated = $dbQuery->with(['brand', 'categories'])->take(8)->get();

        return $dbRelated->map(function ($p) {
            $cat = $p->categories->firstWhere('parent_id', null) ?? $p->categories->first();
            $catName = $cat?->name ?? 'Accessories';
            $isMainWithoutSub = in_array(strtolower($catName), ['laptops', 'software']);
            $subCatName = ! $isMainWithoutSub && $p->categories->count() > 1 ? $p->categories->last()?->name : null;

            $rawImg = ! empty($p->images) && is_array($p->images) ? $p->images[0] : null;
            $image = $rawImg ? ((str_starts_with($rawImg, 'http') || str_starts_with($rawImg, '/')) ? $rawImg : asset('storage/'.$rawImg)) : '/images/placeholder.png';

            return [
                'id' => $p->id,
                'name' => $p->name,
                'image' => $image,
                'category' => $catName,
                'subCategory' => $subCatName,
                'originalPrice' => (float) $p->price,
                'latestPrice' => $p->sale_price !== null ? (float) $p->sale_price : (float) $p->price,
                'discountPercent' => ($p->sale_price && $p->price > 0 && $p->sale_price < $p->price) ? max(0, round((($p->price - $p->sale_price) / $p->price) * 100)) : 0,
                'inStock' => ($p->quantity ?? 0) > 0,
                'brand' => $p->brand?->name ?? 'ComLink',
                'rating' => 4.8,
                'reviews' => 24,
            ];
        })->all();
    }
}
