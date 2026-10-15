<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    /**
     * Display the shopping cart page.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Cart/Index', [
            'appName' => config('app.name', 'ComLink'),
        ]);
    }
}
