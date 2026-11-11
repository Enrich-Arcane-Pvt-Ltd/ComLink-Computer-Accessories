<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class CustomPagesController extends Controller
{
    /**
     * Terms and Conditions Page
     */
    public function terms(): Response
    {
        $terms = [
            'title' => 'TERMS & CONDITIONS',
            'last_updated' => 'Last updated: November 2026',
            'content' => '
                <p>Welcome to ComLink Computer Accessories. These Terms and Conditions govern your use of our website, computer hardware, surveillance systems, networking products, and accessories purchased from us. By accessing or using our platform, you agree to be bound by these terms.</p>

                <h2>1. General Conditions</h2>
                <p>We provide genuine IT equipment, computer accessories, networking solutions, and security cameras across Sri Lanka. We reserve the right to refuse service or cancel orders in cases of suspicious transactions or fraudulent activity.</p>

                <h2>2. Products, Pricing & Availability</h2>
                <p>All prices are listed in Sri Lankan Rupees (LKR) and are subject to change without prior notice based on currency fluctuations or manufacturer updates. We strive to maintain accurate inventory levels; however, if an ordered item is out of stock, our customer care team will notify you promptly.</p>

                <h2>3. Warranty & Product Authenticity</h2>
                <p>All products sold on ComLink are 100% brand new and covered by official distributor warranties unless specifically indicated otherwise. Warranty claims require the original invoice and serial number intact on the equipment.</p>

                <h2>4. Orders & Deliveries</h2>
                <p>Once an order is placed and confirmed, tracking information will be provided. Deliveries are carried out via trusted courier partners across Sri Lanka.</p>

                <h2>5. Contact Information</h2>
                <p>For questions regarding our terms and policies, please reach out to us at support@comlink.lk or call our hotline.</p>
            ',
        ];

        return Inertia::render('Policies/TermsOfService', [
            'terms' => $terms,
        ]);
    }

    /**
     * Privacy Policy Page
     */
    public function privacy(): Response
    {
        $privacy = [
            'title' => 'PRIVACY POLICY',
            'last_updated' => 'Last updated: November 2026',
            'content' => '
                <p>At ComLink, protecting your privacy and securing your personal information is our priority. This Privacy Policy outlines how we collect, store, and utilize your personal details when browsing or shopping on our website.</p>

                <h2>1. Information We Collect</h2>
                <p>When you register an account, place an order, or submit an inquiry, we collect necessary information including your full name, contact numbers, delivery address, and email address.</p>

                <h2>2. Use of Your Information</h2>
                <p>Your details are strictly used to process customer orders, arrange islandwide deliveries, communicate order status updates, and provide after-sales warranty support.</p>

                <h2>3. Data Protection & Security</h2>
                <p>We implement industry-standard encryption, secure session protocols, and authentication safeguards to protect your personal data from unauthorized access.</p>

                <h2>4. Third-Party Services</h2>
                <p>We do not sell or rent customer data to third parties. We only share essential delivery information (name, address, phone number) with verified courier partners to fulfill your orders.</p>

                <h2>5. Contact Us</h2>
                <p>If you have any questions or requests regarding your personal data, please contact us at privacy@comlink.lk.</p>
            ',
        ];

        return Inertia::render('Policies/PrivacyPolicy', [
            'privacy' => $privacy,
        ]);
    }

    /**
     * Shipping and Delivery Page
     */
    public function shipping(): Response
    {
        $shipping = [
            'title' => 'SHIPPING & DELIVERY',
            'last_updated' => 'Last updated: November 2026',
            'content' => '
                <p>ComLink offers safe and reliable island-wide delivery for all computer parts, CCTV equipment, accessories, and system builds.</p>

                <h2>1. Order Processing Time</h2>
                <p>Orders placed on business days are verified and dispatched within 24 to 48 hours. Custom PC builds may require an additional 24 hours for hardware testing and quality inspection.</p>

                <h2>2. Delivery Timelines & Charges</h2>
                <ul>
                    <li><strong>Colombo & Suburbs:</strong> 1 - 2 business days.</li>
                    <li><strong>Island-wide Delivery:</strong> 2 - 4 business days.</li>
                    <li>Delivery charges are calculated at checkout based on weight and destination.</li>
                </ul>

                <h2>3. Secure Packaging & Tracking</h2>
                <p>All sensitive electronic components (hard drives, motherboards, processors, monitors) are bubble-wrapped and packaged in shock-proof boxes. You will receive a tracking link via SMS/Email once dispatched.</p>
            ',
        ];

        return Inertia::render('Policies/ShippingDelivery', [
            'shipping' => $shipping,
        ]);
    }

    /**
     * Returns and Refunds Page
     */
    public function refunds(): Response
    {
        $refunds = [
            'title' => 'RETURNS, REFUNDS & WARRANTY',
            'last_updated' => 'Last updated: November 2026',
            'content' => '
                <p>We stand behind the quality and authenticity of every product we sell. Please review our return and warranty procedure below.</p>

                <h2>1. Return Eligibility</h2>
                <p>Items may be returned within 7 calendar days of receipt if unopened, unused, with all seals, serial numbers, manuals, and original packaging undamaged.</p>

                <h2>2. Defective or Damaged on Arrival</h2>
                <p>If an item arrives damaged or defective, please contact our support team within 48 hours of delivery with photos of the package and product. We will arrange a replacement or refund immediately.</p>

                <h2>3. Warranty Claims</h2>
                <p>For items experiencing issues after the 7-day initial return period, claims fall under the manufacturer\'s official warranty. Our service center assists in processing all manufacturer warranty claims.</p>

                <h2>4. Refund Processing</h2>
                <p>Approved refunds are processed to your original payment method or bank account within 3 - 5 business days.</p>
            ',
        ];

        return Inertia::render('Policies/ReturnRefund', [
            'refunds' => $refunds,
        ]);
    }
}
