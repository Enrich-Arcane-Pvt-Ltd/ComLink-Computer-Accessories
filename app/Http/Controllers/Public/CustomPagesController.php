<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PolicyPage;
use Inertia\Inertia;
use Inertia\Response;

class CustomPagesController extends Controller
{
    /**
     * Resolve a policy page from the database with a safe fallback.
     */
    protected function resolvePolicy(string $slug, array $fallback): array
    {
        $page = PolicyPage::where('slug', $slug)->where('is_active', true)->first();

        if (! $page) {
            return $fallback;
        }

        return [
            'title' => $page->title ?: $fallback['title'],
            'last_updated' => $page->last_updated ?: ('Last updated: ' . $page->updated_at?->format('F Y')),
            'content' => $page->content ?: $fallback['content'],
        ];
    }

    /**
     * Terms and Conditions Page
     */
    public function terms(): Response
    {
        $terms = $this->resolvePolicy('terms-of-service', [
            'title' => 'TERMS & CONDITIONS',
            'last_updated' => 'Last updated: November 2026',
            'content' => '<p>Welcome to ComLink Computer Accessories.</p>',
        ]);

        return Inertia::render('Policies/TermsOfService', [
            'terms' => $terms,
        ]);
    }

    /**
     * Privacy Policy Page
     */
    public function privacy(): Response
    {
        $privacy = $this->resolvePolicy('privacy-policy', [
            'title' => 'PRIVACY POLICY',
            'last_updated' => 'Last updated: November 2026',
            'content' => '<p>At ComLink, protecting your privacy is our priority.</p>',
        ]);

        return Inertia::render('Policies/PrivacyPolicy', [
            'privacy' => $privacy,
        ]);
    }

    /**
     * Shipping and Delivery Page
     */
    public function shipping(): Response
    {
        $shipping = $this->resolvePolicy('shipping-delivery', [
            'title' => 'SHIPPING & DELIVERY',
            'last_updated' => 'Last updated: November 2026',
            'content' => '<p>ComLink offers safe and reliable island-wide delivery.</p>',
        ]);

        return Inertia::render('Policies/ShippingDelivery', [
            'shipping' => $shipping,
        ]);
    }

    /**
     * Returns and Refunds Page
     */
    public function refunds(): Response
    {
        $refunds = $this->resolvePolicy('return-refund', [
            'title' => 'RETURNS, REFUNDS & WARRANTY',
            'last_updated' => 'Last updated: November 2026',
            'content' => '<p>We stand behind the quality and authenticity of every product we sell.</p>',
        ]);

        return Inertia::render('Policies/ReturnRefund', [
            'refunds' => $refunds,
        ]);
    }
}
