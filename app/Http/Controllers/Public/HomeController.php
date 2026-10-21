<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\GalleryItem;
use App\Models\Slide;
use App\Models\Testimonial;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Method index
     */
    public function index(): Response
    {

        // 1. Dynamic Categories Data Fetching (Main visible categories ordered by sort_order)
        $categories = Category::main()
            ->visible()
            ->withCount('products')
            ->orderBy('sort_order', 'asc')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name ?? 'Unknown Category',
                    'slug' => $category->slug,
                    'count' => $category->products_count ?? 0,
                    'image' => $category->image_url ?? url('/images/placeholder.png'),
                ];
            });

        // 2. Dynamic Testimonials Data Fetching
        $testimonials = Testimonial::where('is_active', true)
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => (string) $item->id,
                    'name' => $item->name ?? 'Anonymous',
                    'location' => $item->location ?? 'Verified Buyer',
                    'avatar' => $item->avatar_url,
                    'rating' => (int) ($item->rating ?? 5),
                    'quote' => Str::limit($item->quote ?? '', 50, '...'),
                ];
            });

        $allSlides = Slide::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        $heroSlides = $allSlides->where('location', 'hero')->values()->map(function ($slide) {
            return [
                'id' => $slide->id,
                'image' => $slide->image_url,
                'title' => $slide->title ?? 'Default Title',
                'subtitle' => $slide->subtitle ?? '',
                'description' => $slide->description ?? '',
                'cta' => $slide->button_text ?? 'Shop Now',
                'link' => $slide->button_url ?? '/',
            ];
        });

        if ($heroSlides->isEmpty()) {
            $heroSlides = collect([[
                'id' => 1,
                'image' => 'https://images.unsplash.com/photo-1552374196-1ab2a1c593e8?auto=format&fit=crop&q=80&w=2000',
                'title' => 'MAKING A STATEMENT THROUGH FASHION',
                'subtitle' => 'New Collection',
                'description' => 'Elevate your everyday style with our premium collection of streetwear and essentials.',
                'cta' => 'Shop Collection',
                'link' => '/',
            ]]);
        }

        // 3. Dynamic Products Data Fetching (Real products added by admin)
        $realProducts = \App\Models\Product::where('is_visible', true)
            ->with(['brand', 'categories'])
            ->latest()
            ->take(12)
            ->get();

        $formatCard = function (\App\Models\Product $p) {
            $cat = $p->categories->firstWhere('parent_id', null) ?? $p->categories->first();
            $subCat = $p->categories->firstWhere('parent_id', '!=', null);
            $rawImg = ! empty($p->images) && is_array($p->images) ? $p->images[0] : null;
            $image = $rawImg ? ((str_starts_with($rawImg, 'http') || str_starts_with($rawImg, '/')) ? $rawImg : asset('storage/'.$rawImg)) : '/images/placeholder.png';

            return [
                'id' => $p->id,
                'name' => $p->name,
                'image' => $image,
                'category' => $cat?->name ?? 'Hardware',
                'categorySlug' => $cat?->slug ?? '',
                'subCategory' => $subCat?->name,
                'subCategorySlug' => $subCat?->slug ?? '',
                'originalPrice' => (float) $p->price,
                'latestPrice' => $p->sale_price !== null ? (float) $p->sale_price : (float) $p->price,
                'discountPercent' => ($p->sale_price && $p->price > 0) ? max(0, round((($p->price - $p->sale_price) / $p->price) * 100)) : 0,
                'inStock' => ($p->quantity ?? 0) > 0,
                'condition' => $p->condition ?? 'Brand New',
                'brand' => $p->brand?->name ?? 'ComLink',
                'rating' => 4.9,
                'reviews' => 24,
            ];
        };

        if ($realProducts->isNotEmpty()) {
            $newestProducts = $realProducts->map($formatCard)->values()->all();

            $featured = \App\Models\Product::where('is_visible', true)
                ->where('is_featured', true)
                ->with(['brand', 'categories'])
                ->latest()
                ->take(12)
                ->get();

            if ($featured->isEmpty()) {
                $featured = $realProducts;
            }

            $bestSellerProducts = $featured->map($formatCard)->values()->all();
        } else {
            $newestProducts = [];
            $bestSellerProducts = [];
        }

        $instagramImages = GalleryItem::where('is_visible', true)
            ->where('is_featured_home', true)
            ->orderBy('sort_order', 'asc')
            ->get()
            ->map(function ($item) {
                return $item->image_url;
            })
            ->toArray();

        if (empty($instagramImages)) {
            $instagramImages = [
                'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&q=80&w=400',
                'https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&q=80&w=400',
                'https://images.unsplash.com/photo-1539109136881-3be0616acf4b?auto=format&fit=crop&q=80&w=400',
                'https://images.unsplash.com/photo-1529139574466-a303027c1d8b?auto=format&fit=crop&q=80&w=400',
                'https://images.unsplash.com/photo-1509631179647-0177331693ae?auto=format&fit=crop&q=80&w=400',
                'https://images.unsplash.com/photo-1554412933-514a83d2f3c8?auto=format&fit=crop&q=80&w=400',
            ];
        }

        return Inertia::render('Home/Index', [
            'title' => 'Home - ComLink Computer Accessories',
            'categories' => $categories,
            'products' => $newestProducts,
            'newestProducts' => $newestProducts,
            'bestSellerProducts' => $bestSellerProducts,
            'testimonials' => $testimonials,
            'slides' => $heroSlides,
            'instagramImages' => $instagramImages,
        ]);
    }
}
