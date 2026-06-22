<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Inertia\Response;
use Inertia\Inertia;

class CustomPagesController extends Controller
{
    /**
     * Terms and Conditions Page
     */
    public function terms(): Response
    {
        $terms = [
            'title' => 'TERMS & CONDITIONS',
            'last_updated' => 'Last updated: October 1, 2026',
            'content' => '
                <p>Welcome to VICKY STORE. These Terms and Conditions govern your use of our website and the purchase of products from us. By accessing or using our website, you agree to be bound by these Terms.</p>

                <h2>1. General Conditions</h2>
                <p>We reserve the right to refuse service to anyone for any reason at any time. You understand that your content (not including credit card information), may be transferred unencrypted and involve transmissions over various networks.</p>

                <h2>2. Products and Pricing</h2>
                <p>Prices for our products are subject to change without notice. We reserve the right at any time to modify or discontinue the Service (or any part or content thereof) without notice at any time.</p>

                <h2>3. User Comments and Feedback</h2>
                <p>If, at our request, you send certain specific submissions or without a request from us you send creative ideas, suggestions, proposals, plans, or other materials, whether online, by email, by postal mail, or otherwise, you agree that we may, at any time, without restriction, edit, copy, publish, distribute, translate and otherwise use in any medium any comments that you forward to us.</p>

                <h2>4. Personal Information</h2>
                <p>Your submission of personal information through the store is governed by our Privacy Policy.</p>

                <h2>5. Errors, Inaccuracies and Omissions</h2>
                <p>Occasionally there may be information on our site or in the Service that contains typographical errors, inaccuracies or omissions that may relate to product descriptions, pricing, promotions, offers, product shipping charges, transit times and availability. We reserve the right to correct any errors, inaccuracies or omissions, and to change or update information or cancel orders if any information in the Service or on any related website is inaccurate at any time without prior notice.</p>

                <h2>6. Contact Information</h2>
                <p>Questions about the Terms of Service should be sent to us at legal@wikistore.com.</p>
            '
        ];

        return Inertia::render('Policies/TermsOfService', [
            'terms' => $terms
        ]);
    }

    /**
     * Privacy Policy Page
     */
    public function privacy(): Response
    {
        $privacy = [
            'title' => 'PRIVACY POLICY',
            'last_updated' => 'Last updated: October 1, 2026',
            'content' => '
                <p>At VICKY STORE, we take your privacy seriously. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website or make a purchase.</p>

                <h2>1. Information We Collect</h2>
                <p>We collect information that you provide directly to us when you create an account, make a purchase, sign up for our newsletter, or contact customer support. This information may include your name, email address, shipping address, billing address, phone number, and payment details.</p>

                <h2>2. How We Use Your Information</h2>
                <p>We use the information we collect to process your orders, manage your account, send you promotional emails (if you opted in), and improve our overall services.</p>

                <h2>3. Sharing Your Information</h2>
                <p>We may share your information with third-party service providers who help us operate our business, such as payment processors, shipping carriers, and email marketing platforms. These providers are obligated to protect your information.</p>

                <h2>4. Cookies and Tracking</h2>
                <p>We use cookies and similar tracking technologies to track activity on our website and hold certain information. You can instruct your browser to refuse all cookies or to indicate when a cookie is being sent.</p>

                <h2>5. Your Rights</h2>
                <p>Depending on your location, you may have the right to access, correct, or delete your personal information. If you wish to exercise these rights, please contact us at privacy@wikistore.com.</p>

                <h2>6. Changes to This Policy</h2>
                <p>We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "Last updated" date.</p>

                <h2>7. Contact Us</h2>
                <p>If you have any questions about this Privacy Policy, please contact us at:<br>Email: privacy@wikistore.com<br>Address: 368 Ocean Ave, Seaside, CA 12345</p>
            '
        ];

        return Inertia::render('Policies/PrivacyPolicy', [
            'privacy' => $privacy
        ]);
    }

    /**
     * Shipping and Delivery Page
     */
    public function shipping(): Response
    {
        $shipping = [
            'title' => 'SHIPPING & DELIVERY',
            'last_updated' => 'Last updated: October 1, 2026',
            'content' => '
                <p>Thank you for shopping at VICKY STORE. Here is the information regarding our shipping and delivery timelines, rates, and policies.</p>

                <h2>1. Processing Timelines</h2>
                <p>All orders are processed within 1-2 business days. Orders are not shipped or delivered on weekends or holidays.</p>

                <h2>2. Shipping Rates & Delivery Estimates</h2>
                <ul>
                    <li><strong>Standard Shipping:</strong> 3-5 business days ($5.99, or FREE on orders over $100).</li>
                    <li><strong>Express Shipping:</strong> 1-2 business days ($14.99).</li>
                </ul>

                <h2>3. Shipment Confirmation & Order Tracking</h2>
                <p>You will receive a Shipment Confirmation email and a WhatsApp alert containing your tracking number(s) once your order has shipped.</p>
            '
        ];

        return Inertia::render('Policies/ShippingDelivery', [
            'shipping' => $shipping
        ]);
    }

    /**
     * Returns and Refunds Page
     */
    public function refunds(): Response
    {
        $refunds = [
            'title' => 'RETURNS, REFUNDS & CANCELLATIONS',
            'last_updated' => 'Last updated: October 1, 2026',
            'content' => '
                <p>Our priority is your complete satisfaction. If you are not entirely satisfied with your purchase, we are here to help.</p>

                <h2>1. 30-Day Return Policy</h2>
                <p>You have 30 calendar days to return an item from the date you received it. To be eligible for a return, your item must be unused, unwashed, and in the same condition that you received it with all original tags attached.</p>

                <h2>2. Refund Process</h2>
                <p>Once we receive your item, we will inspect it and notify you that we have received your returned item. If your return is approved, we will initiate a refund to your original method of payment within 5-7 business days.</p>

                <h2>3. Cancellations</h2>
                <p>You can cancel an order within 1 hour of placing it. Please contact customer support immediately to request a cancellation.</p>
            '
        ];

        return Inertia::render('Policies/ReturnRefund', [
            'refunds' => $refunds
        ]);
    }
}
