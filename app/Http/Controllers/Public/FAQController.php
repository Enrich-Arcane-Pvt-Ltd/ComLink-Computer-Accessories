<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\FaqCategory;
use Exception;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class FAQController extends Controller
{
    /**
     * Display the FAQs page.
     */
    public function index(): Response
    {
        $formattedFaqs = [];

        try {
            $categories = FaqCategory::where('is_active', true)
                ->with(['faqs' => function ($query) {
                    $query->where('is_active', true)->orderBy('sort_order', 'asc');
                }])
                ->orderBy('sort_order', 'asc')
                ->get();

            foreach ($categories as $category) {
                foreach ($category->faqs as $faq) {
                    $formattedFaqs[] = [
                        'id' => (string) $faq->id,
                        'category' => $category->name,
                        'question' => $faq->question,
                        'answer' => $faq->answer,
                    ];
                }
            }
        } catch (Exception $e) {
            config('app.debug') ? dd($e->getMessage()) : Log::error('FAQ Load Error: '.$e->getMessage());
            $formattedFaqs = [];
        }

        return Inertia::render('FAQ/Index', [
            'faqs' => $formattedFaqs,
            'title' => 'Frequently Asked Questions',
        ]);
    }
}
