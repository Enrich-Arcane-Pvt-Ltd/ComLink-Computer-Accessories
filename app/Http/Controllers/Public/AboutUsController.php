<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Inertia\Inertia;

class AboutUsController extends Controller
{
    /**
     * index
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $brands = Brand::where('is_visible', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($brand) {
                return [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'slug' => $brand->slug,
                    'logo' => $brand->logo_url,
                    'url' => $brand->url,
                ];
            });

        return Inertia::render('About/Index', [
            'title' => 'About Us',
            'brands' => $brands,
        ]);
    }
}
