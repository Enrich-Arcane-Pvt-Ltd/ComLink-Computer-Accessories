<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialAndFaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info("Seeding ComLink Testimonials & FAQs...");

        // ---------------------------------------------------------
        // 1. SEED TESTIMONIALS
        // ---------------------------------------------------------
        $testimonialsData = [
            [
                'name' => 'Nuwan Perera',
                'location' => 'Colombo 04, Sri Lanka',
                'rating' => 5,
                'quote' => 'Built my dream gaming rig with ComLink! The staff helped me pick compatible DDR5 RAM and an RTX card that fit my budget. Cable management and packaging were top-tier. Highly recommended for PC enthusiasts in Colombo!',
                'is_active' => true,
            ],
            [
                'name' => 'Dinesh Jayawardena',
                'location' => 'Kandy, Central Province',
                'rating' => 5,
                'quote' => 'Ordered a set of Hikvision ColorVu security cameras, DVR box, and high-speed SSDs. Delivery reached Kandy in under 48 hours in shock-proof bubble wrap. Genuine distributor warranty stickers were verified.',
                'is_active' => true,
            ],
            [
                'name' => 'Kavindu Madushan',
                'location' => 'Gampaha, Western Province',
                'rating' => 5,
                'quote' => 'The online PC Builder tool on ComLink is unmatched. It automatically calculates total wattage and checks motherboard socket compatibility. Ordered all components and got them delivered quickly.',
                'is_active' => true,
            ],
            [
                'name' => 'Sachini Fernando',
                'location' => 'Nugegoda, Sri Lanka',
                'rating' => 5,
                'quote' => 'Purchased an HP Core i5 laptop and a 27-inch Samsung desktop monitor for graphic design work. Competitive pricing compared to local market and very courteous after-sales assistance.',
                'is_active' => true,
            ],
            [
                'name' => 'Hasitha Bandara',
                'location' => 'Kurunegala, North Western',
                'rating' => 5,
                'quote' => 'Exceptional customer service and true warranty honor. My older UPS unit developed a voltage drop issue and their technical support assisted in replacing it under official warranty within days.',
                'is_active' => true,
            ],
            [
                'name' => 'Malith Silva',
                'location' => 'Galle, Southern Province',
                'rating' => 5,
                'quote' => 'Best computer accessories store in Sri Lanka! From high speed NVMe drives to mechanical keyboards and gaming headsets, everything is authentic and factory-sealed.',
                'is_active' => true,
            ],
        ];

        foreach ($testimonialsData as $item) {
            Testimonial::updateOrCreate(
                ['name' => $item['name']],
                $item
            );
        }
        $this->command->line("  [OK] Seeded " . count($testimonialsData) . " ComLink Testimonials.");

        // ---------------------------------------------------------
        // 2. SEED FAQ CATEGORIES & FAQS
        // ---------------------------------------------------------
        $faqCategoriesData = [
            [
                'name' => 'Orders & Payments',
                'slug' => 'orders-payments',
                'sort_order' => 1,
                'is_active' => true,
                'faqs' => [
                    [
                        'question' => 'How can I place an order on ComLink?',
                        'answer' => '<p>Placing an order is simple: browse our catalog or use the search bar, select your desired products or custom components, add them to your cart, and proceed to checkout. You can checkout as a registered member or quickly as a guest.</p>',
                        'sort_order' => 1,
                    ],
                    [
                        'question' => 'What payment methods do you accept?',
                        'answer' => '<p>We accept secure online card payments (Visa, MasterCard via Stripe/Payment Gateway), Direct Bank Transfers / Cash Deposits, and Cash on Delivery (COD) for selected areas in the Western Province.</p>',
                        'sort_order' => 2,
                    ],
                    [
                        'question' => 'Can I modify or cancel my order after placing it?',
                        'answer' => '<p>If you need to make changes or cancel an order, please contact our support hotline or WhatsApp support within <strong>2 hours</strong> of placing the order before it is packed and dispatched with the courier.</p>',
                        'sort_order' => 3,
                    ],
                ],
            ],
            [
                'name' => 'Island-wide Delivery & Shipping',
                'slug' => 'delivery-shipping',
                'sort_order' => 2,
                'is_active' => true,
                'faqs' => [
                    [
                        'question' => 'How long does island-wide delivery take across Sri Lanka?',
                        'answer' => '<p>Deliveries within Colombo and suburbs typically arrive within <strong>1 to 2 business days</strong>. For all outstation locations across Sri Lanka, delivery takes <strong>2 to 4 business days</strong> via our trusted courier partners.</p>',
                        'sort_order' => 1,
                    ],
                    [
                        'question' => 'How do you ensure fragile electronic items remain safe during transit?',
                        'answer' => '<p>All sensitive computer hardware (motherboards, graphics cards, hard drives, monitors, and glass PC cases) are packaged with multi-layer bubble wrap, anti-static shielding, and heavy-duty corrugated cartons with fragile warning labels.</p>',
                        'sort_order' => 2,
                    ],
                    [
                        'question' => 'How do I track my delivery status?',
                        'answer' => '<p>Once your order has been dispatched, you will receive an SMS and email notification containing your courier tracking number and direct tracking link. You can also view real-time updates in your <em>Orders</em> dashboard.</p>',
                        'sort_order' => 3,
                    ],
                ],
            ],
            [
                'name' => 'Warranty & Returns',
                'slug' => 'warranty-returns',
                'sort_order' => 3,
                'is_active' => true,
                'faqs' => [
                    [
                        'question' => 'Are all products covered by official manufacturer warranties?',
                        'answer' => '<p><strong>Yes, absolutely.</strong> All items sold on ComLink are 100% brand new, factory sealed, and backed by official Sri Lankan distributor warranties (ranging from 1 to 3 years depending on the brand and component).</p>',
                        'sort_order' => 1,
                    ],
                    [
                        'question' => 'How do I claim warranty for a defective component?',
                        'answer' => '<p>To claim a warranty, ensure the product has its original invoice and warranty/serial sticker intact. You can drop off the component at our service desk or send it to us via courier. Our technical team will inspect and process replacement or repair with the authorized agent.</p>',
                        'sort_order' => 2,
                    ],
                    [
                        'question' => 'What is your return and refund policy?',
                        'answer' => '<p>Unopened items in original factory-sealed condition can be returned within <strong>7 calendar days</strong> of receipt. If an item arrives damaged or defective, notify us within 48 hours for immediate replacement.</p>',
                        'sort_order' => 3,
                    ],
                ],
            ],
            [
                'name' => 'Custom PC Builder & Tech Support',
                'slug' => 'pc-builder-support',
                'sort_order' => 4,
                'is_active' => true,
                'faqs' => [
                    [
                        'question' => 'How does the online PC Builder tool work?',
                        'answer' => '<p>Our interactive <strong>Build My PC</strong> tool guides you step-by-step through choosing a processor, motherboard, memory, graphics card, storage, power supply, and casing. It automatically verifies socket compatibility, memory generations (DDR4 vs DDR5), and calculates total system wattage.</p>',
                        'sort_order' => 1,
                    ],
                    [
                        'question' => 'Can ComLink assemble and test my custom PC before delivery?',
                        'answer' => '<p>Yes! Our expert technicians assemble custom rigs with professional cable management, BIOS updating, thermal paste application, and comprehensive 24-hour hardware stress testing prior to delivery.</p>',
                        'sort_order' => 2,
                    ],
                ],
            ],
            [
                'name' => 'CCTV & Security Systems',
                'slug' => 'cctv-security',
                'sort_order' => 5,
                'is_active' => true,
                'faqs' => [
                    [
                        'question' => 'Do you provide genuine Hikvision CCTV cameras and accessories?',
                        'answer' => '<p>Yes, we are authorized suppliers of authentic Hikvision surveillance equipment, including ColorVu, TVI, Dual Light Live Guard cameras, DVRs, NVRs, RG59 coaxial and CAT6 networking cables with full distributor warranty.</p>',
                        'sort_order' => 1,
                    ],
                    [
                        'question' => 'Do you provide technical assistance for DVR remote mobile viewing?',
                        'answer' => '<p>Yes, our CCTV technicians can guide you through configuring the Hik-Connect mobile application for live surveillance viewing on iOS and Android smartphones.</p>',
                        'sort_order' => 2,
                    ],
                ],
            ],
        ];

        // Clean out previous dummy FAQs
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        Faq::truncate();
        FaqCategory::truncate();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        foreach ($faqCategoriesData as $catData) {
            $faqs = $catData['faqs'];
            unset($catData['faqs']);

            $cat = FaqCategory::create($catData);

            foreach ($faqs as $f) {
                $f['faq_category_id'] = $cat->id;
                $f['is_active'] = true;
                Faq::create($f);
            }
        }

        $this->command->info("Seeded " . count($faqCategoriesData) . " FAQ Categories and " . Faq::count() . " FAQs successfully!");
    }
}

