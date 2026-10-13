<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\FaqCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. ඔයා දීපු සාම්පල FAQ දත්ත සමූහය
        $faqs = [
            [
                'category' => 'Orders',
                'question' => 'How can I track my order?',
                'answer' => 'Once your order has shipped, you will receive an email with a tracking number and a link to track your package. You can also track your order by logging into your account and visiting the "Orders" section, or by using our Track Order page with your Order ID and email.'
            ],
            [
                'category' => 'Orders',
                'question' => 'Can I modify or cancel my order after placing it?',
                'answer' => 'We process orders quickly to ensure fast delivery. If you need to modify or cancel your order, please contact our customer support team within 1 hour of placing it. After that time, we cannot guarantee that changes can be made.'
            ],
            [
                'category' => 'Shipping',
                'question' => 'Do you offer international shipping?',
                'answer' => 'Yes, we ship to most countries worldwide. International shipping rates and delivery times vary depending on the destination. You can see the exact shipping cost at checkout before completing your purchase.'
            ],
            [
                'category' => 'Shipping',
                'question' => 'How long will it take to receive my order?',
                'answer' => 'Standard domestic shipping typically takes 3-5 business days. Expedited shipping options are available at checkout for 1-2 day delivery. International orders usually take 7-14 business days.'
            ],
            [
                'category' => 'Returns',
                'question' => 'What is your return policy?',
                'answer' => 'We accept returns within 30 days of delivery for unworn, unwashed items with original tags attached. Final sale items cannot be returned. Please visit our Returns page to initiate a return.'
            ],
            [
                'category' => 'Returns',
                'question' => 'Are returns free?',
                'answer' => 'We offer free returns for all domestic orders. For international orders, the customer is responsible for return shipping costs unless the item received was defective or incorrect.'
            ],
            [
                'category' => 'Payments',
                'question' => 'What payment methods do you accept?',
                'answer' => 'We accept all major credit cards (Visa, Mastercard, American Express, Discover), PayPal, Apple Pay, and Google Pay.'
            ],
            [
                'category' => 'Account',
                'question' => 'Do I need an account to place an order?',
                'answer' => 'No, you can checkout as a guest. However, creating an account allows you to track orders easily, save shipping addresses, and manage your wishlist.'
            ]
        ];

        // 2. Loop එකක් මඟින් Category එක මුලින්ම සාදා/ගෙන පසුව FAQ එක ඇතුළත් කිරීම
        foreach ($faqs as $index => $item) {

            // Category එක දැනටමත් නැත්නම් සාදයි, තිබේ නම් පවතින එක ලබා ගනියි (Unique by Name)
            $category = FaqCategory::updateOrCreate(
                ['name' => $item['category']],
                [
                    'slug' => Str::slug($item['category']),
                    'sort_order' => 0,
                    'is_active' => true,
                ]
            );

            // සාදාගත් Category ID එක යොදා FAQ ප්‍රශ්නය ඇතුළත් කිරීම
            Faq::create([
                'faq_category_id' => $category->id,
                'question' => $item['question'],
                // Frontend එකේ format එක ලස්සනට තියාගන්න <p> tag එකක් ඇතුළට දමා සේව් කිරීම (Filament RichEditor එකට ගැළපෙන සේ)
                'answer' => '<p>' . $item['answer'] . '</p>',
                'sort_order' => $index, // ආපු පිළිවෙළටම sort order එක සෙට් වේ
                'is_active' => true,
            ]);
        }
    }
}
