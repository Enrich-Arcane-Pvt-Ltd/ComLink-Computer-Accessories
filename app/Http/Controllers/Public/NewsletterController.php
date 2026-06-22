<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Exception;

class NewsletterController extends Controller
{
    public function store(Request $request)
    {
        // 1. Honeypot Check - Bot කෙනෙක් නම් නිහඬවම සාර්ථක පණිවිඩයක් පෙන්වන්න
        if ($request->filled('username_hp')) {
            return back()->with('success', 'Thank you for subscribing to our newsletter!');
        }

        // Input එක clean කිරීම
        $request->merge([
            'email' => strip_tags(trim($request->email))
        ]);

        // 2. Standard Validation Rules
        $request->validate([
            'email' => [
                'required',
                'string',
                'max:55',
                'email:rfc,dns',
                'unique:newsletter_subscribers,email'
            ]
        ], [
            'email.required' => 'Please enter your email address.',
            'email.max' => 'The email address is too long.',
            'email.email' => 'Please enter a valid and active email address.',
            'email.unique' => 'This email is already subscribed to our newsletter!'
        ]);

        // Disposable email check
        $disposableDomains = ['mailinator.com', '10minutemail.com', 'yopmail.com', 'tempmail.com'];
        $domain = substr(strrchr($request->email, "@"), 1);

        if (in_array(strtolower($domain), $disposableDomains)) {
            throw ValidationException::withMessages([
                'email' => 'Disposable email addresses are not allowed.'
            ]);
        }

        try {
            NewsletterSubscriber::create([
                'email' => $request->email,
                'is_active' => true
            ]);

            return back()->with('success', 'Thank you for subscribing to our newsletter!');
        } catch (Exception $e) {
            throw ValidationException::withMessages([
                'email' => 'Something went wrong on our server. Please try again later.'
            ]);
        }
    }
}
