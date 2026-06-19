<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Method index
     */
    public function index(): Response
    {
        return Inertia::render('Home/Index', [
            'title' => 'Vicky Store - Home',
        ]);
    }
}
