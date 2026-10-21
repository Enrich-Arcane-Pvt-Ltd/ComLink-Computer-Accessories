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
        $testimonials = [
            [
                'id' => '1',
                'name' => 'David Kim',
                'location' => 'Los Angeles, CA',
                'avatar' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&q=80&w=200',
                'rating' => 5,
                'quote' => "I've been a loyal customer of MILANA for years, and I can confidently say that their products have truly transformed my personal style. The attention to detail and quality of their denim is unmatched.",
            ],
            [
                'id' => '2',
                'name' => 'Michael Chang',
                'location' => 'New York, NY',
                'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&q=80&w=200',
                'rating' => 5,
                'quote' => "The fit of their t-shirts is perfect. Not too tight, not too loose. I've bought them in almost every color now. Customer service is also incredibly responsive.",
            ],
            [
                'id' => '3',
                'name' => 'James Wilson',
                'location' => 'Chicago, IL',
                'avatar' => 'https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?auto=format&fit=crop&q=80&w=200',
                'rating' => 4,
                'quote' => 'Great streetwear essentials. The heavyweight hoodies are my go-to for the winter. Shipping was fast and the packaging felt very premium.',
            ],
            [
                'id' => '4',
                'name' => 'Kevin Nguyen',
                'location' => 'Seattle, WA',
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=200',
                'rating' => 5,
                'quote' => "Finally found a brand that understands modern men's fashion without being overly complicated. Clean lines, great fabrics, and versatile pieces.",
            ],
            [
                'id' => '5',
                'name' => 'Alex Mercer',
                'location' => 'Austin, TX',
                'avatar' => 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&q=80&w=200',
                'rating' => 5,
                'quote' => 'The material quality is exceptional. Washed it multiple times and it still holds its shape perfectly.',
            ],
            [
                'id' => '6',
                'name' => 'Ryan Reynolds',
                'location' => 'Vancouver, BC',
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=200',
                'rating' => 5,
                'quote' => 'Fast shipping and unbelievable comfort. Exceeded all my expectations. Will definitely order again.',
            ],
            [
                'id' => '7',
                'name' => 'John Doe',
                'location' => 'Miami, FL',
                'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&q=80&w=200',
                'rating' => 4,
                'quote' => 'Very minimal and clean aesthetic. Perfect for daily casual wear.',
            ],
            [
                'id' => '8',
                'name' => 'Sarah Jenkins',
                'location' => 'Boston, MA',
                'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=200',
                'rating' => 5,
                'quote' => 'Gifted a jacket to my boyfriend and he absolutely loves it. The premium feel is real.',
            ],
            [
                'id' => '9',
                'name' => 'Tom Hardy',
                'location' => 'London, UK',
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=200',
                'rating' => 5,
                'quote' => 'Top notch customer service and precise fits. Highly recommended brand!',
            ],
            [
                'id' => '10',
                'name' => 'Chris Evans',
                'location' => 'Boston, MA',
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=200',
                'rating' => 5,
                'quote' => 'Amazing fabric thickness. The texture feels incredibly high-end.',
            ],
            [
                'id' => '1',
                'name' => 'David Kim',
                'location' => 'Los Angeles, CA',
                'avatar' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&q=80&w=200',
                'rating' => 5,
                'quote' => "I've been a loyal customer of MILANA for years, and I can confidently say that their products have truly transformed my personal style. The attention to detail and quality of their denim is unmatched.",
            ],
            [
                'id' => '2',
                'name' => 'Michael Chang',
                'location' => 'New York, NY',
                'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&q=80&w=200',
                'rating' => 5,
                'quote' => "The fit of their t-shirts is perfect. Not too tight, not too loose. I've bought them in almost every color now. Customer service is also incredibly responsive.",
            ],
            [
                'id' => '3',
                'name' => 'James Wilson',
                'location' => 'Chicago, IL',
                'avatar' => 'https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?auto=format&fit=crop&q=80&w=200',
                'rating' => 4,
                'quote' => 'Great streetwear essentials. The heavyweight hoodies are my go-to for the winter. Shipping was fast and the packaging felt very premium.',
            ],
            [
                'id' => '4',
                'name' => 'Kevin Nguyen',
                'location' => 'Seattle, WA',
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=200',
                'rating' => 5,
                'quote' => "Finally found a brand that understands modern men's fashion without being overly complicated. Clean lines, great fabrics, and versatile pieces.",
            ],
            [
                'id' => '5',
                'name' => 'Alex Mercer',
                'location' => 'Austin, TX',
                'avatar' => 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&q=80&w=200',
                'rating' => 5,
                'quote' => 'The material quality is exceptional. Washed it multiple times and it still holds its shape perfectly.',
            ],
            [
                'id' => '6',
                'name' => 'Ryan Reynolds',
                'location' => 'Vancouver, BC',
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=200',
                'rating' => 5,
                'quote' => 'Fast shipping and unbelievable comfort. Exceeded all my expectations. Will definitely order again.',
            ],
            [
                'id' => '7',
                'name' => 'John Doe',
                'location' => 'Miami, FL',
                'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&q=80&w=200',
                'rating' => 4,
                'quote' => 'Very minimal and clean aesthetic. Perfect for daily casual wear.',
            ],
            [
                'id' => '8',
                'name' => 'Sarah Jenkins',
                'location' => 'Boston, MA',
                'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=200',
                'rating' => 5,
                'quote' => 'Gifted a jacket to my boyfriend and he absolutely loves it. The premium feel is real.',
            ],
            [
                'id' => '9',
                'name' => 'Tom Hardy',
                'location' => 'London, UK',
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=200',
                'rating' => 5,
                'quote' => 'Top notch customer service and precise fits. Highly recommended brand!',
            ],
            [
                'id' => '10',
                'name' => 'Chris Evans',
                'location' => 'Boston, MA',
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=200',
                'rating' => 5,
                'quote' => 'Amazing fabric thickness. The texture feels incredibly high-end.',
            ],
        ];

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
