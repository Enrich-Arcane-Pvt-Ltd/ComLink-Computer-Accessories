<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class ContactController extends Controller
{
    /**
     * Contact පිටුව පෙන්වීම
     */
    public function index(): Response
    {
        return Inertia::render('Contact/Index', [
            'title' => 'Contact Us'
        ]);
    }

    /**
     * Form එක Submit කරන විට Handle කිරීම
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['required', 'string', 'email', 'max:255'],
            'phone'   => ['required', 'phone'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10'],
        ], [
            'name.required'    => 'Please enter your name.',
            'email.required'   => 'Please enter your email address.',
            'email.email'      => 'Please enter a valid email address.',
            'phone.required'   => 'Please enter your phone or WhatsApp number.',
            'phone.phone'      => 'Please enter a valid phone number with your country code (e.g., +94771234567).',
            'subject.required' => 'Please enter a subject.',
            'message.required' => 'Please enter your message.',
            'message.min'      => 'The message must be at least 10 characters long.',
        ]);
        // Notification::route('mail', $inquiry->email)
        //     ->notify(new ContactUsReplyNotification($inquiry, $inquiry));

        return redirect()->back()->with('success', 'Thank you for contacting us! We will get back to you soon.');
    }
}
