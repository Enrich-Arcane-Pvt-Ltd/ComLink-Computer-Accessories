<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'errors' => function () {
                return session()->get('errors')
                    ? session()->get('errors')->getBag('default')->getMessages()
                    : (object) [];
            },
            'flash' => function () use ($request) {
                return [
                    'success' => $request->session()->get('success'),
                    'error' => $request->session()->get('error'),
                ];
            },
            'navCategories' => function () {
                try {
                    return \App\Models\Category::main()
                        ->visible()
                        ->orderBy('sort_order', 'asc')
                        ->with(['children' => function ($q) {
                            $q->visible()->orderBy('sort_order', 'asc');
                        }])
                        ->get()
                        ->map(function ($cat) {
                            return [
                                'id' => $cat->id,
                                'name' => $cat->name,
                                'slug' => $cat->slug,
                                'path' => '/shop?category='.$cat->slug,
                                'image' => $cat->image_url,
                                'children' => $cat->children->map(function ($child) use ($cat) {
                                    return [
                                        'id' => $child->id,
                                        'name' => $child->name,
                                        'slug' => $child->slug,
                                        'path' => '/shop?category='.$cat->slug.'&subcategory='.$child->slug,
                                        'image' => $child->image_url,
                                    ];
                                })->values()->all(),
                            ];
                        })->values()->all();
                } catch (\Throwable $e) {
                    return [];
                }
            },
        ];
    }
}
