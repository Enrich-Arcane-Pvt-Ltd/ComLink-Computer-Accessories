<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class PublicPageController extends Controller
{
    public function home(): Response
    {
        return Inertia::render('Public/Home/Index');
    }

    public function about(): Response
    {
        return Inertia::render('Public/About/Index');
    }

    public function instructors(): Response
    {
        return Inertia::render('Public/Instructors/Index');
    }

    public function be_member(): Response
    {
        return Inertia::render('Public/BeMember/Index');
    }

    // public function faqs(): Response
    // {
    //     return Inertia::render('Public/Faqs/Index');
    // }

    public function classes(): Response
    {
        return Inertia::render('Public/Classes/Index');
    }

    // public function blog(): Response
    // {
    //     return Inertia::render('Public/Blog/Index');
    // }

    // public function contact(): Response
    // {
    //     return Inertia::render('Public/Contact/Index');
    // }
}
