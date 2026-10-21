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
     * Method index
     */
    public function index(): Response
    {
        $faqs = [
            [
                'id' => '1',
                'category' => 'Orders',
                'question' => 'How can I track my order?',
                'answer' => 'Once your order has shipped, you will receive an email with a tracking number and a link to track your package. You can also track your order by logging into your account and visiting the "Orders" section, or by using our Track Order page with your Order ID and email.',
            ],
            [
                'id' => '2',
                'category' => 'Orders',
                'question' => 'Can I modify or cancel my order after placing it?',
                'answer' => 'We process orders quickly to ensure fast delivery. If you need to modify or cancel your order, please contact our customer support team within 1 hour of placing it. After that time, we cannot guarantee that changes can be made.',
            ],
            [
                'id' => '3',
                'category' => 'Shipping',
                'question' => 'Do you offer international shipping?',
                'answer' => 'Yes, we ship to most countries worldwide. International shipping rates and delivery times vary depending on the destination. You can see the exact shipping cost at checkout before completing your purchase.',
            ],
            [
                'id' => '4',
                'category' => 'Shipping',
                'question' => 'How long will it take to receive my order?',
                'answer' => 'Standard domestic shipping typically takes 3-5 business days. Expedited shipping options are available at checkout for 1-2 day delivery. International orders usually take 7-14 business days.',
            ],
            [
                'id' => '5',
                'category' => 'Returns',
                'question' => 'What is your return policy?',
                'answer' => 'We accept returns within 30 days of delivery for unworn, unwashed items with original tags attached. Final sale items cannot be returned. Please visit our Returns page to initiate a return.',
            ],
            [
                'id' => '6',
                'category' => 'Returns',
                'question' => 'Are returns free?',
                'answer' => 'We offer free returns for all domestic orders. For international orders, the customer is responsible for return shipping costs unless the item received was defective or incorrect.',
            ],
            [
                'id' => '7',
                'category' => 'Payments',
                'question' => 'What payment methods do you accept?',
                'answer' => 'We accept all major credit cards (Visa, Mastercard, American Express, Discover), PayPal, Apple Pay, and Google Pay.',
            ],
            [
                'id' => '8',
                'category' => 'Account',
                'question' => 'Do I need an account to place an order?',
                'answer' => 'No, you can checkout as a guest. However, creating an account allows you to track orders easily, save shipping addresses, and manage your wishlist.',
            ],
        ];
        $formattedFaqs = [];

        try {
            $categories = FaqCategory::where('is_active', true)
                ->with(['faqs' => function ($query) {
                    $query->where('is_active', true)->orderBy('sort_order', 'asc');
                }])
                ->orderBy('sort_order', 'asc')
                ->get();

            // Frontend එක බලාපොරොත්තු වන Flat Array ව්‍යුහයට දත්ත සකස් කිරීම
            foreach ($categories as $category) {
                foreach ($category->faqs as $faq) {
                    $formattedFaqs[] = [
                        'id' => (string) $faq->id,
                        'category' => $category->name,
                        'question' => $faq->question,
                        'answer' => $faq->answer, // Filament RichEditor එකෙන් එන HTML මෙතනට එනවා
                    ];
                }
            }
        } catch (Exception $e) {
            // සර්වර් එකේ දෝෂයක් ආවොත් crash නොවී log කර හිස් array එකක් යවයි
            config('app.debug') ? dd($e->getMessage()) : Log::error('FAQ Load Error: '.$e->getMessage());
            $formattedFaqs = [];
        }

        return Inertia::render('FAQ/Index', [
            'faqs' => $formattedFaqs,
            'title' => 'Frequently Asked Questions',
        ]);
    }
}
