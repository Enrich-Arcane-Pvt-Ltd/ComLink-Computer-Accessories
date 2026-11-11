<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Exception;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class TestimonialController extends Controller
{
    public function index(): Response
    {
        $formattedTestimonials = [];

        try {
            $testimonials = Testimonial::where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->get();

            $formattedTestimonials = $testimonials->map(function ($testimonial) {
                return [
                    'id' => (string) $testimonial->id,
                    'name' => $testimonial->name,
                    'location' => $testimonial->location ?? '',
                    'avatar' => $testimonial->avatar_url,
                    'rating' => (int) $testimonial->rating,
                    'quote' => $testimonial->quote,
                ];
            })->toArray();
        } catch (Exception $e) {
            config('app.debug') ? dd($e->getMessage()) : Log::error('Testimonials Page Load Error: '.$e->getMessage());
            $formattedTestimonials = [];
        }

        return Inertia::render('Testimonials/Index', [
            'testimonials' => $formattedTestimonials,
            'title' => 'What Our Customers Say',
        ]);
    }
}
