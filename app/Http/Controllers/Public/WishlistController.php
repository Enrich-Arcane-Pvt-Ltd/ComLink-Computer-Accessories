<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WishlistController extends Controller
{
    /**
     * Display the wishlist page.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Wishlist/Index', [
            'appName' => config('app.name', 'ComLink'),
        ]);
    }
}
