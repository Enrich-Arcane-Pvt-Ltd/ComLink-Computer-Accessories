<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Support\Facades\Storage;
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
        // Only include brands that have actual, verified uploaded logos
        $brands = Brand::where('is_visible', true)
            ->whereNotNull('logo')
            ->where('logo', '!=', '')
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get()
            ->filter(function ($brand) {
                if (empty($brand->logo)) {
                    return false;
                }
                if (str_starts_with($brand->logo, 'http://') || str_starts_with($brand->logo, 'https://')) {
                    return true;
                }
                if (Storage::disk('public')->exists($brand->logo)) {
                    return true;
                }
                if (file_exists(public_path($brand->logo)) || file_exists(public_path('storage/' . ltrim($brand->logo, '/')))) {
                    return true;
                }
                return false;
            })
            ->map(function ($brand) {
                $logoUrl = null;
                if (str_starts_with($brand->logo, 'http://') || str_starts_with($brand->logo, 'https://')) {
                    $logoUrl = $brand->logo;
                } elseif (Storage::disk('public')->exists($brand->logo)) {
                    $logoUrl = asset(Storage::url($brand->logo));
                } elseif (file_exists(public_path($brand->logo))) {
                    $logoUrl = asset($brand->logo);
                } elseif (file_exists(public_path('storage/' . ltrim($brand->logo, '/')))) {
                    $logoUrl = asset('storage/' . ltrim($brand->logo, '/'));
                }

                return [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'slug' => $brand->slug,
                    'logo' => $logoUrl,
                    'url' => $brand->url,
                ];
            })
            ->filter(fn ($brand) => !empty($brand['logo']))
            ->values();

        return Inertia::render('About/Index', [
            'title' => 'About Us',
            'brands' => $brands,
        ]);
    }
}
